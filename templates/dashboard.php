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
/** @var array $apps */
/** @var int $totalUsers */
/** @var int $activeTokens */
/** @var int $maxApps */
/** @var string $error */
/** @var string $success */
?>
<div class="portal">
    <header class="portal-head">
        <h1><?= t('My apps') ?></h1>
        <p class="portal-lede">
            <?= htmlspecialchars((string)$developer['name']) ?>
            · <?= (int)$totalUsers ?> <?= t('Authorized users') ?>
            · <?= (int)$activeTokens ?> <?= t('Active Tokens') ?>
        </p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="portal-note portal-note--ok" style="margin-top:20px"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="portal-actions" style="margin-top:24px">
        <a class="btn btn-primary" href="<?= htmlspecialchars($portalBase . '/apps/create') ?>"><?= t('Create app') ?></a>
        <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/settings') ?>"><?= t('Developer settings') ?></a>
        <a class="btn btn-ghost" href="/dashboard"><?= t('Back to account') ?></a>
    </div>

    <section class="portal-section">
        <h2><?= t('apps') ?></h2>
        <?php if (!$apps): ?>
            <p class="portal-muted"><?= t('No apps yet.') ?></p>
        <?php else: ?>
        <table class="portal-table">
            <thead>
                <tr>
                    <th><?= t('Name') ?></th>
                    <th>Client ID</th>
                    <th><?= t('Status') ?></th>
                    <th><?= t('Created') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apps as $app): ?>
                <tr>
                    <td>
                        <a href="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id']) ?>">
                            <?= htmlspecialchars((string)$app['name']) ?>
                        </a>
                    </td>
                    <td><code><?= htmlspecialchars((string)$app['client_id']) ?></code></td>
                    <td>
                        <?php if ((int)$app['status'] === 1): ?>
                            <span class="portal-badge portal-badge--on"><?= t('Enabled') ?></span>
                        <?php else: ?>
                            <span class="portal-badge portal-badge--off"><?= t('Disabled') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(substr((string)$app['created_at'], 0, 10)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <p class="portal-hint"><?= t('Each developer can create up to 10 apps') ?>（<?= count($apps) ?> / <?= (int)$maxApps ?>）</p>
    </section>
</div>
