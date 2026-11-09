<?php
/*
 * Copyright (c) 2025-2026 Yunyun (Wang Yaoze)
 * Licensed under the GNU General Public License, version 3 only, with
 * additional attribution terms in the NOTICE file.
 * Source: https://github.com/NomenID/plugin-open-platform
 * Project: https://nomenid.org/w/plugin-open-platform
 */

/** @var string $portalBase */
/** @var array|null $developer */
/** @var array|null $disabledDeveloper */
/** @var bool $loggedIn */
/** @var array $apps */
/** @var int $totalUsers */
/** @var int $activeTokens */
/** @var string $error */
/** @var string $success */
$baseUrl = rtrim((string)\Nomen\Src\Brand::get('base_url'), '/');
$docsBase = \Nomen\Src\Plugins::mountUrl('pages');
?>
<div class="portal">
    <header class="portal-head">
        <h1><?= t('Open Platform') ?></h1>
        <p class="portal-lede"><?= t('Let people sign in to your app with their {:sitename} account.') ?> <?= t('OAuth 2.0 authorization code flow with PKCE.') ?></p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="portal-note portal-note--ok" style="margin-top:20px"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($developer): ?>
        <section class="portal-section">
            <h2><?= t('Developer') ?></h2>
            <p>
                <?= htmlspecialchars((string)$developer['name']) ?>
                · <?= count($apps) ?> <?= t('apps') ?>
                · <?= (int)$totalUsers ?> <?= t('Authorized users') ?>
                · <?= (int)$activeTokens ?> <?= t('Active Tokens') ?>
            </p>
            <div class="portal-actions">
                <a class="btn btn-primary" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('Open console') ?></a>
                <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/settings') ?>"><?= t('Developer settings') ?></a>
            </div>
        </section>
    <?php elseif ($disabledDeveloper): ?>
        <section class="portal-section">
            <p class="portal-note portal-note--warn"><?= t('This developer account is disabled. Contact an administrator.') ?></p>
        </section>
    <?php elseif ($loggedIn): ?>
        <section class="portal-section" id="become-developer">
            <h2><?= t('Become a developer') ?></h2>
            <p class="portal-muted"><?= t('Enter a name to create apps.') ?></p>
            <form method="POST" action="<?= htmlspecialchars($portalBase . '/register') ?>" class="portal-form">
                <?= \Nomen\Src\Csrf::field() ?>
                <div class="portal-field">
                    <label for="developer-name"><?= t('Developer / team name') ?></label>
                    <input id="developer-name" name="name" required minlength="2" maxlength="64"
                           placeholder="<?= t('2-64 characters') ?>" autocomplete="organization">
                    <span class="portal-hint"><?= t('Shown on the app consent screen. You can change it later in developer settings.') ?></span>
                </div>
                <div class="portal-actions">
                    <button type="submit" class="btn btn-primary"><?= t('Become a developer') ?></button>
                </div>
            </form>
        </section>
    <?php else: ?>
        <section class="portal-section">
            <p><?= t('Sign in or create a {:sitename} account, then apply as a developer.') ?></p>
            <div class="portal-actions">
                <a class="btn btn-primary" href="/login?redirect_to=<?= rawurlencode($portalBase) ?>"><?= t('Sign in') ?></a>
                <a class="btn btn-ghost" href="/register"><?= t('Sign up') ?></a>
            </div>
        </section>
    <?php endif; ?>

    <section class="portal-section">
        <h2><?= t('Integrations') ?></h2>
        <table class="portal-table">
            <thead>
                <tr><th><?= t('Name') ?></th><th><?= t('URL') ?></th></tr>
            </thead>
            <tbody>
                <tr><td><?= t('Authorize') ?></td><td><code><?= htmlspecialchars($baseUrl . '/oauth/authorize') ?></code></td></tr>
                <tr><td><?= t('Token') ?></td><td><code><?= htmlspecialchars($baseUrl . '/oauth/token') ?></code></td></tr>
                <tr><td><?= t('User info') ?></td><td><code><?= htmlspecialchars($baseUrl . '/api/userinfo') ?></code></td></tr>
                <tr><td><?= t('Revoke') ?></td><td><code><?= htmlspecialchars($baseUrl . '/oauth/revoke') ?></code></td></tr>
            </tbody>
        </table>
    </section>

    <section class="portal-section">
        <h2><?= t('Scopes') ?></h2>
        <ul class="portal-list">
            <li><code>openid</code> · <?= t('Basic profile') ?></li>
            <li><code>profile</code> · <?= t('username, display name, avatar') ?></li>
            <li><code>email</code> · <?= t('Verified email') ?></li>
        </ul>
    </section>

    <?php if ($docsBase): ?>
    <section class="portal-section">
        <h2><?= t('Docs') ?></h2>
        <p><a href="<?= htmlspecialchars($docsBase . '/oauth') ?>"><?= t('OAuth 2.0 integration') ?></a></p>
    </section>
    <?php endif; ?>
</div>
