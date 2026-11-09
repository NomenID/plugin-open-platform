<?php
/*
 * Copyright (c) 2025-2026 Yunyun (Wang Yaoze)
 * Licensed under the GNU General Public License, version 3 only, with
 * additional attribution terms in the NOTICE file.
 * Source: https://github.com/NomenID/plugin-open-platform
 * Project: https://nomenid.org/w/plugin-open-platform
 */

/** @var string $portalBase */
/** @var array $developer */
/** @var string $error */
/** @var string $success */
?>
<div class="portal">
    <header class="portal-head">
        <h1><?= t('Developer settings') ?></h1>
        <p class="portal-lede"><?= t('The name and icon appear on the consent screen.') ?></p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="portal-note portal-note--ok" style="margin-top:20px"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($portalBase . '/profile') ?>" enctype="multipart/form-data"
          class="portal-form" style="margin-top:28px">
        <?= \Nomen\Src\Csrf::field() ?>
        <div class="portal-field">
            <label for="developer-name"><?= t('Name') ?></label>
            <input id="developer-name" name="name" required minlength="2" maxlength="64"
                   value="<?= htmlspecialchars((string)$developer['name']) ?>">
        </div>
        <div class="portal-field">
            <label for="developer-icon"><?= t('Icon') ?></label>
            <?php if (!empty($developer['icon'])): ?>
                <div style="margin-bottom:8px">
                    <img src="<?= htmlspecialchars(\Nomen\Src\IconUpload::url((string)$developer['icon'])) ?>" alt="" width="40" height="40">
                </div>
            <?php endif; ?>
            <input id="developer-icon" type="file" name="icon" accept="image/png,image/jpeg,image/gif,image/webp">
        </div>
        <div class="portal-actions">
            <button type="submit" class="btn btn-primary"><?= t('Save') ?></button>
            <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('Back') ?></a>
        </div>
    </form>

    <section class="portal-section">
        <h2><?= t('Account') ?></h2>
        <p class="portal-muted">
            <?= t('Joined') ?> <?= htmlspecialchars(substr((string)$developer['created_at'], 0, 10)) ?>
            · <a href="/dashboard"><?= t('Back to account') ?></a>
        </p>
    </section>
</div>
