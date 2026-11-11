<?php
/*
 * Copyright (c) 2025-2026 Yunyun (Wang Yaoze)
 * Licensed under the Apache License, Version 2.0.
 * See the LICENSE file in this repository for the full text.
 * Source: https://github.com/NomenID/plugin-open-platform
 * Project: https://nomenid.org/w/plugin-open-platform
 */

/** @var string $portalBase */
/** @var array $app */
/** @var array $developer */
/** @var array $redirectUris */
/** @var array $appScopes */
/** @var array $availableScopes */
/** @var array $stats */
/** @var bool $isPublic */
/** @var bool $pkceOn */
/** @var string $authorizeSample */
/** @var string $tokenSample */
/** @var string $baseUrl */
/** @var string $newClientId */
/** @var string $newClientSecret */
/** @var string $error */
/** @var string $success */
$statusOn = (int)$app['status'] === 1;
?>
<div class="portal">
    <header class="portal-head">
        <div class="portal-app-title">
            <h1><?= htmlspecialchars((string)$app['name']) ?></h1>
            <?php if ($statusOn): ?>
                <span class="portal-badge portal-badge--on"><?= t('Enabled') ?></span>
            <?php else: ?>
                <span class="portal-badge portal-badge--off"><?= t('Disabled') ?></span>
            <?php endif; ?>
        </div>
        <p class="portal-lede"><code><?= htmlspecialchars((string)$app['client_id']) ?></code></p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="portal-note portal-note--ok" style="margin-top:20px"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($newClientSecret !== ''): ?>
    <section class="portal-section">
        <div class="portal-note portal-note--warn">
            <strong><?= t('Credentials are shown once') ?></strong><br>
            <?= t('It will not be shown again after you leave this page. To get a new one, reset the secret.') ?>
        </div>
        <div class="portal-secret" style="margin-top:10px">
            <span>Client ID</span>
            <span id="new-client-id"><?= htmlspecialchars($newClientId) ?></span>
            <button type="button" data-copy="#new-client-id" data-copied-label="<?= t('Copied') ?>"><?= t('Copy') ?></button>
        </div>
        <div class="portal-secret" style="margin-top:8px">
            <span>Client Secret</span>
            <span id="new-client-secret"><?= htmlspecialchars($newClientSecret) ?></span>
            <button type="button" data-copy="#new-client-secret" data-copied-label="<?= t('Copied') ?>"><?= t('Copy') ?></button>
        </div>
    </section>
    <?php endif; ?>

    <section class="portal-section">
        <div class="portal-grid">
            <div class="portal-card">
                <h3><?= t('Authorized users') ?></h3>
                <p><?= (int)$stats['authorized_users'] ?></p>
            </div>
            <div class="portal-card">
                <h3><?= t('Active Tokens') ?></h3>
                <p><?= (int)$stats['active_tokens'] ?></p>
            </div>
            <div class="portal-card">
                <h3><?= t('Client type') ?></h3>
                <p><?= $isPublic ? t('Public client') : t('Confidential client') ?></p>
            </div>
            <div class="portal-card">
                <h3>PKCE</h3>
                <p><?= $pkceOn ? t('Require PKCE') : t('Optional') ?></p>
            </div>
        </div>
    </section>

    <section class="portal-section">
        <h2><?= t('Integration details') ?></h2>
        <table class="portal-table">
            <tbody>
                <tr><td><?= t('Authorize') ?></td><td><code id="ep-authorize"><?= htmlspecialchars($baseUrl . '/oauth/authorize') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-authorize" data-copied-label="<?= t('Copied') ?>"><?= t('Copy') ?></button></td></tr>
                <tr><td><?= t('Token') ?></td><td><code id="ep-token"><?= htmlspecialchars($baseUrl . '/oauth/token') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-token" data-copied-label="<?= t('Copied') ?>"><?= t('Copy') ?></button></td></tr>
                <tr><td><?= t('User info') ?></td><td><code id="ep-userinfo"><?= htmlspecialchars($baseUrl . '/api/userinfo') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-userinfo" data-copied-label="<?= t('Copied') ?>"><?= t('Copy') ?></button></td></tr>
                <tr><td><?= t('Revoke') ?></td><td><code id="ep-revoke"><?= htmlspecialchars($baseUrl . '/oauth/revoke') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-revoke" data-copied-label="<?= t('Copied') ?>"><?= t('Copy') ?></button></td></tr>
                <tr><td><?= t('Redirect URI') ?></td><td colspan="2">
                    <?php foreach ($redirectUris as $uri): ?>
                        <div><code><?= htmlspecialchars((string)$uri) ?></code></div>
                    <?php endforeach; ?>
                </td></tr>
                <tr><td><?= t('Scopes') ?></td><td colspan="2"><code><?= htmlspecialchars(implode(' ', $appScopes)) ?></code></td></tr>
            </tbody>
        </table>
    </section>

    <section class="portal-section">
        <details>
            <summary><?= t('Examples') ?></summary>
            <h3><?= t('Start authorization') ?></h3>
            <pre class="portal-code"><?= htmlspecialchars($authorizeSample) ?></pre>
            <h3><?= t('Exchange for a token') ?></h3>
            <pre class="portal-code"><?= htmlspecialchars($tokenSample) ?></pre>
            <h3><?= t('Read user info') ?></h3>
            <pre class="portal-code">GET <?= htmlspecialchars($baseUrl . '/api/userinfo') ?>

Authorization: Bearer ACCESS_TOKEN</pre>
        </details>
    </section>

    <section class="portal-section">
        <h2><?= t('Basic information') ?></h2>
        <form method="POST" action="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id'] . '/update') ?>"
              enctype="multipart/form-data" class="portal-form">
            <?= \Nomen\Src\Csrf::field() ?>
            <?php require __DIR__ . '/_fields.php'; ?>
            <div class="portal-actions">
                <button type="submit" class="btn btn-primary"><?= t('Save') ?></button>
                <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('Back') ?></a>
            </div>
        </form>
    </section>

    <section class="portal-danger">
        <h2><?= t('Danger zone') ?></h2>
        <div class="portal-actions">
            <?php if (!$isPublic): ?>
            <form method="POST" action="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id'] . '/reset-secret') ?>"
                  onsubmit="return confirm('<?= t('The old secret stops working immediately and all active tokens are revoked. Continue?') ?>')">
                <?= \Nomen\Src\Csrf::field() ?>
                <button type="submit" class="btn btn-ghost"><?= t('Reset secret') ?></button>
            </form>
            <?php endif; ?>
            <form method="POST" action="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id'] . '/delete') ?>"
                  onsubmit="return confirm('<?= t('Deleting the app revokes all authorizations and tokens. This cannot be undone. Continue?') ?>')">
                <?= \Nomen\Src\Csrf::field() ?>
                <button type="submit" class="btn btn-ghost" style="color:var(--color-danger)"><?= t('Delete app') ?></button>
            </form>
        </div>
    </section>
</div>
