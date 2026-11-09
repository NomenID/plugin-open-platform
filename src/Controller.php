<?php
/*
 * Copyright (c) 2025-2026 Yunyun (Wang Yaoze)
 * Licensed under the GNU General Public License, version 3 only, with
 * additional attribution terms in the NOTICE file.
 * Source: https://github.com/NomenID/plugin-open-platform
 * Project: https://nomen.caellab.com/w/plugin-open-platform
 */

namespace Nomen\Plugin\OpenPlatform;

use InvalidArgumentException;
use Nomen\Src\{ActivityLog, Brand, Csrf, Database, IconUpload, OAuthClients, Plugins, RateLimiter, Response, Session};

class Controller
{
    private const MAX_APPS = 10;

    public static function base(): string
    {
        return Plugin::basePath();
    }

    public static function isDeveloper(int $userId): bool
    {
        return self::getDeveloper($userId) !== null;
    }

    public static function getDeveloper(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM developers WHERE user_id = :uid AND status = 1 LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);

        return $stmt->fetch() ?: null;
    }

    /** 不管启用还是停用，只要有开发者记录就返回；注册入口用它挡住重复插入。 */
    public static function getDeveloperAny(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM developers WHERE user_id = :uid LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);

        return $stmt->fetch() ?: null;
    }

    public function index(): void
    {
        $userId = (int)Session::get('user_id');
        $developer = $userId > 0 ? self::getDeveloper($userId) : null;
        $disabledDeveloper = ($developer === null && $userId > 0) ? self::getDeveloperAny($userId) : null;

        $this->view('landing', [
            'title'             => t('Open Platform'),
            'loggedIn'          => $userId > 0,
            'developer'         => $developer,
            'disabledDeveloper' => $disabledDeveloper,
            'apps'              => $developer ? OAuthClients::all((int)$developer['id']) : [],
            'totalUsers'        => $developer ? OAuthClients::authorizedUserCount((int)$developer['id']) : 0,
            'activeTokens'      => $developer ? OAuthClients::activeTokenCount((int)$developer['id']) : 0,
            'error'             => (string)(Session::getFlash('error') ?: ''),
            'success'           => (string)(Session::getFlash('success') ?: ''),
        ]);
    }

    public function registerDeveloper(): void
    {
        $userId = (int)Session::get('user_id');

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('Invalid request, try again'));
            Response::redirect(self::base());
        }

        if (self::getDeveloper($userId)) {
            Response::redirect(self::base() . '/dashboard');
        }

        if (self::getDeveloperAny($userId)) {
            Session::flash('error', t('This developer account is disabled. Contact an administrator.'));
            Response::redirect(self::base());
        }

        $rate = RateLimiter::check("open_platform_register:{$userId}", 1, 3600);
        if (!$rate['allowed']) {
            Session::flash('error', t('Too many actions, try again later'));
            Response::redirect(self::base());
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 64) {
            Session::flash('error', t('Developer name needs 2-64 characters'));
            Response::redirect(self::base());
        }

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO developers (user_id, name, status) VALUES (:uid, :name, 1)'
        );
        $stmt->execute([':uid' => $userId, ':name' => $name]);
        $developerId = (int)Database::getInstance()->lastInsertId();

        ActivityLog::record([
            'action'      => 'developer.register',
            'category'    => ActivityLog::CAT_DEV,
            'target_type' => 'developer',
            'target_id'   => $developerId,
            'target_name' => $name,
            'summary'     => '入驻开放平台，开发者名称：' . $name,
        ]);

        Session::flash('success', t('Developer registered! You can create apps now'));
        Response::redirect(self::base() . '/dashboard');
    }

    public function dashboard(): void
    {
        $developer = $this->requireDeveloper();

        $this->view('dashboard', [
            'title'       => t('My apps'),
            'developer'   => $developer,
            'apps'        => OAuthClients::all((int)$developer['id']),
            'totalUsers'  => OAuthClients::authorizedUserCount((int)$developer['id']),
            'activeTokens'=> OAuthClients::activeTokenCount((int)$developer['id']),
            'maxApps'     => self::MAX_APPS,
            'error'       => (string)(Session::getFlash('error') ?: ''),
            'success'     => (string)(Session::getFlash('success') ?: ''),
        ]);
    }

    public function createApp(): void
    {
        $this->requireDeveloper();

        $this->view('app-form', [
            'title'           => t('Create app'),
            'app'             => null,
            'appScopes'       => ['openid'],
            'redirectUris'    => [],
            'availableScopes' => OAuthClients::scopes(),
            'action'          => self::base() . '/apps/create',
            'error'           => (string)(Session::getFlash('error') ?: ''),
        ]);
    }

    public function storeApp(): void
    {
        $developer = $this->requireDeveloper();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('Invalid request, try again'));
            Response::redirect(self::base() . '/apps/create');
        }

        $count = count(OAuthClients::all((int)$developer['id']));
        if ($count >= self::MAX_APPS) {
            Session::flash('error', t('Each developer can create up to 10 apps'));
            Response::redirect(self::base() . '/apps/create');
        }

        $rate = RateLimiter::check("open_platform_create:{$developer['id']}", 1, 60);
        if (!$rate['allowed']) {
            Session::flash('error', t('Too many actions, try again later'));
            Response::redirect(self::base() . '/apps/create');
        }

        $icon = null;
        if (!empty($_FILES['icon']['name'])) {
            $icon = IconUpload::store($_FILES['icon']);
            if ($icon === null) {
                Session::flash('error', t('Icon upload failed — check the file format and size'));
                Response::redirect(self::base() . '/apps/create');
            }
        }

        try {
            $created = OAuthClients::create(
                $_POST,
                (int)Session::get('user_id'),
                (int)$developer['id'],
                $icon
            );
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect(self::base() . '/apps/create');
        }

        ActivityLog::record([
            'action'      => 'developer.app_create',
            'category'    => ActivityLog::CAT_DEV,
            'target_type' => 'oauth_client',
            'target_id'   => $created['client_id'],
            'target_name' => $created['name'],
            'summary'     => '创建应用「' . $created['name'] . '」',
        ]);

        Session::flash('new_client_id', $created['client_id']);
        Session::flash('new_client_secret', $created['client_secret']);
        Session::flash('success', t('App created'));

        Response::redirect(self::base() . '/apps/' . $created['client_id']);
    }

    public function showApp(string $clientId): void
    {
        $developer = $this->requireDeveloper();
        $app = OAuthClients::findForDeveloper($clientId, (int)$developer['id']);

        if (!$app) {
            Response::error(404, t('App not found or no access'));
            return;
        }

        $redirectUris = json_decode((string)$app['redirect_uris'], true) ?: [];
        $scopes = preg_split('/[\s,]+/', trim((string)$app['scopes']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $isPublic = ($app['client_type'] ?? 'confidential') === 'public';
        $pkce = $isPublic || (int)($app['pkce_required'] ?? 0) === 1;

        $this->view('app-show', [
            'title'            => t('App details'),
            'developer'        => $developer,
            'app'              => $app,
            'redirectUris'     => array_values($redirectUris),
            'appScopes'        => array_values($scopes),
            'availableScopes'  => OAuthClients::scopes(),
            'stats'            => OAuthClients::stats($clientId),
            'isPublic'         => $isPublic,
            'pkceOn'           => $pkce,
            'authorizeSample'  => OAuthClients::authorizeSample($app, array_values($redirectUris), $pkce),
            'tokenSample'      => OAuthClients::tokenSample($app, array_values($redirectUris), $pkce),
            'baseUrl'          => rtrim((string)Brand::get('base_url'), '/'),
            'newClientId'      => (string)(Session::getFlash('new_client_id') ?: ''),
            'newClientSecret'  => (string)(Session::getFlash('new_client_secret') ?: ''),
            'error'            => (string)(Session::getFlash('error') ?: ''),
            'success'          => (string)(Session::getFlash('success') ?: ''),
        ]);
    }

    public function updateApp(string $clientId): void
    {
        $developer = $this->requireDeveloper();
        $app = OAuthClients::findForDeveloper($clientId, (int)$developer['id']);

        if (!$app) {
            Response::error(404, t('App not found or no access'));
            return;
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('Invalid request, try again'));
            Response::redirect(self::base() . '/apps/' . $clientId);
        }

        $icon = null;
        if (!empty($_FILES['icon']['name'])) {
            $icon = IconUpload::store($_FILES['icon']);
            if ($icon === null) {
                Session::flash('error', t('Icon upload failed — check the file format and size'));
                Response::redirect(self::base() . '/apps/' . $clientId);
            }
        }

        try {
            $data = OAuthClients::update($clientId, $_POST, $icon);
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect(self::base() . '/apps/' . $clientId);
        }

        $after = array_merge($app, $data);
        if ($icon !== null) {
            $after['icon'] = $icon;
        }
        $beforeFields = OAuthClients::logFields($app);
        $afterFields  = OAuthClients::logFields($after);

        ActivityLog::record([
            'action'      => 'developer.app_update',
            'category'    => ActivityLog::CAT_DEV,
            'target_type' => 'oauth_client',
            'target_id'   => $clientId,
            'target_name' => (string)$app['name'],
            'summary'     => '更新应用「' . $app['name'] . '」',
            'changes'     => ActivityLog::diff($beforeFields, $afterFields, array_keys($beforeFields)),
        ]);

        Session::flash('success', t('App updated'));
        Response::redirect(self::base() . '/apps/' . $clientId);
    }

    public function resetSecret(string $clientId): void
    {
        $developer = $this->requireDeveloper();
        $app = OAuthClients::findForDeveloper($clientId, (int)$developer['id']);

        if (!$app) {
            Response::error(404, t('App not found or no access'));
            return;
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('Invalid request, try again'));
            Response::redirect(self::base() . '/apps/' . $clientId);
        }
        if (($app['client_type'] ?? 'confidential') === 'public') {
            Session::flash('error', t('Public clients don\'t use a Client Secret, nothing to reset'));
            Response::redirect(self::base() . '/apps/' . $clientId);
        }

        $rate = RateLimiter::check("open_platform_reset:{$developer['id']}", 3, 3600);
        if (!$rate['allowed']) {
            Session::flash('error', t('Too many resets, try again later'));
            Response::redirect(self::base() . '/apps/' . $clientId);
        }

        $secret = OAuthClients::resetSecret($clientId);

        ActivityLog::record([
            'action'      => 'developer.app_reset_secret',
            'category'    => ActivityLog::CAT_DEV,
            'target_type' => 'oauth_client',
            'target_id'   => $clientId,
            'target_name' => (string)$app['name'],
            'summary'     => '重置应用「' . $app['name'] . '」的密钥',
        ]);

        Session::flash('new_client_id', $clientId);
        Session::flash('new_client_secret', $secret);
        Session::flash('success', t('Secret reset — all active Tokens invalidated'));
        Response::redirect(self::base() . '/apps/' . $clientId);
    }

    public function deleteApp(string $clientId): void
    {
        $developer = $this->requireDeveloper();
        $app = OAuthClients::findForDeveloper($clientId, (int)$developer['id']);

        if (!$app) {
            Response::error(404, t('App not found or no access'));
            return;
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('Invalid request, try again'));
            Response::redirect(self::base() . '/apps/' . $clientId);
        }

        OAuthClients::delete($clientId);

        ActivityLog::record([
            'action'      => 'developer.app_delete',
            'category'    => ActivityLog::CAT_DEV,
            'target_type' => 'oauth_client',
            'target_id'   => $clientId,
            'target_name' => (string)$app['name'],
            'summary'     => '删除应用「' . $app['name'] . '」',
        ]);

        Session::flash('success', t('App deleted'));
        Response::redirect(self::base() . '/dashboard');
    }

    public function settings(): void
    {
        $developer = $this->requireDeveloper();

        $this->view('settings', [
            'title'     => t('Developer settings'),
            'developer' => $developer,
            'error'     => (string)(Session::getFlash('error') ?: ''),
            'success'   => (string)(Session::getFlash('success') ?: ''),
        ]);
    }

    public function updateProfile(): void
    {
        $developer = $this->requireDeveloper();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('Invalid request, try again'));
            Response::redirect(self::base() . '/settings');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 64) {
            Session::flash('error', t('Developer name needs 2-64 characters'));
            Response::redirect(self::base() . '/settings');
        }

        $icon = $developer['icon'];
        if (!empty($_FILES['icon']['name'])) {
            $newIcon = IconUpload::store($_FILES['icon']);
            if ($newIcon === null) {
                Session::flash('error', t('Icon upload failed — check the file format and size'));
                Response::redirect(self::base() . '/settings');
            }
            $icon = $newIcon;
        }

        $stmt = Database::getInstance()->prepare('UPDATE developers SET name = :name, icon = :icon WHERE id = :id');
        $stmt->execute([':name' => $name, ':icon' => $icon, ':id' => $developer['id']]);

        ActivityLog::record([
            'action'      => 'developer.profile_update',
            'category'    => ActivityLog::CAT_DEV,
            'target_type' => 'developer',
            'target_id'   => (int)$developer['id'],
            'target_name' => $name,
            'summary'     => '更新开发者资料',
        ]);

        Session::flash('success', t('Developer profile updated'));
        Response::redirect(self::base() . '/settings');
    }

    private function requireDeveloper(): array
    {
        $developer = self::getDeveloper((int)Session::get('user_id'));

        if ($developer === null) {
            Session::flash('error', t('Become a developer first'));
            Response::redirect(self::base());
        }

        return $developer;
    }

    private function view(string $template, array $data): void
    {
        Response::view('@open-platform/' . $template, $data + [
            'portalBase' => self::base(),
            'developer'  => $data['developer'] ?? null,
        ], 'default');
    }
}
