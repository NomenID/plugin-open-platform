<?php
namespace Nomen\Plugin\OpenPlatform;

use Nomen\Src\{Nav, Plugins};

class Plugin
{
    public static function boot(): void
    {
        $base = self::basePath();

        Plugins::route('GET', $base, [Controller::class, 'index']);
        Plugins::route('POST', $base . '/register', [Controller::class, 'registerDeveloper'], ['auth']);
        Plugins::route('GET', $base . '/dashboard', [Controller::class, 'dashboard'], ['auth']);
        Plugins::route('GET', $base . '/settings', [Controller::class, 'settings'], ['auth']);
        Plugins::route('GET', $base . '/apps/create', [Controller::class, 'createApp'], ['auth']);
        Plugins::route('POST', $base . '/apps/create', [Controller::class, 'storeApp'], ['auth']);
        Plugins::route('GET', $base . '/apps/{clientId}', [Controller::class, 'showApp'], ['auth']);
        Plugins::route('POST', $base . '/apps/{clientId}/update', [Controller::class, 'updateApp'], ['auth']);
        Plugins::route('POST', $base . '/apps/{clientId}/reset-secret', [Controller::class, 'resetSecret'], ['auth']);
        Plugins::route('POST', $base . '/apps/{clientId}/delete', [Controller::class, 'deleteApp'], ['auth']);
        Plugins::route('POST', $base . '/profile', [Controller::class, 'updateProfile'], ['auth']);

        Plugins::route('POST', '/admin/developers/{id}/toggle', [AdminPage::class, 'toggleStatus'], ['admin']);

        Nav::add('open-platform', [
            'label'   => '开放平台',
            'href'    => $base . '/dashboard',
            'icon'    => 'layers',
            'order'   => 10,
            'visible' => static fn(?int $uid): bool => $uid !== null && $uid > 0 && Controller::isDeveloper($uid),
        ]);

        $path = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
        if ($path === $base || str_starts_with($path, $base . '/')) {
            Plugins::style('open-platform', 'portal.css');
            Plugins::script('open-platform', 'portal.js');
        }
    }

    public static function basePath(): string
    {
        $config = Plugins::config('open-platform', ['base_path' => '/open']);
        $base = trim((string)($config['base_path'] ?? '/open'));

        if ($base === '' || $base === '/') {
            $base = '/open';
        }

        return '/' . trim($base, '/');
    }
}
