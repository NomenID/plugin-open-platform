<?php
/*
 * Copyright (c) 2025-2026 Yunyun (Wang Yaoze)
 * Licensed under the GNU General Public License, version 3 only.
 * Attribution requests are in the NOTICE file.
 * Source: https://github.com/NomenID/plugin-open-platform
 * Project: https://nomenid.org/w/plugin-open-platform
 */

/** @var string $portalBase */
/** @var array|null $app */
/** @var array $appScopes */
/** @var array $redirectUris */
/** @var array $availableScopes */
/** @var string $action */
/** @var string $error */
?>
<div class="portal">
    <header class="portal-head">
        <h1><?= t('Create app') ?></h1>
        <p class="portal-lede"><?= t('Configure the redirect URI and scopes now; the app can be integrated as soon as it is created.') ?></p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($action) ?>" enctype="multipart/form-data" class="portal-form" style="margin-top:28px">
        <?= \Nomen\Src\Csrf::field() ?>
        <?php require __DIR__ . '/_fields.php'; ?>
        <div class="portal-actions">
            <button type="submit" class="btn btn-primary"><?= t('Create app') ?></button>
            <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('Cancel') ?></a>
        </div>
    </form>
</div>
