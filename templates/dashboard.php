<?php
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
        <h1><?= t('我的应用') ?></h1>
        <p class="portal-lede">
            <?= htmlspecialchars((string)$developer['name']) ?>
            · <?= (int)$totalUsers ?> <?= t('授权用户') ?>
            · <?= (int)$activeTokens ?> <?= t('活跃 Token') ?>
        </p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="portal-note portal-note--ok" style="margin-top:20px"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="portal-actions" style="margin-top:24px">
        <a class="btn btn-primary" href="<?= htmlspecialchars($portalBase . '/apps/create') ?>"><?= t('创建应用') ?></a>
        <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/settings') ?>"><?= t('开发者设置') ?></a>
        <a class="btn btn-ghost" href="/dashboard"><?= t('返回个人') ?></a>
    </div>

    <section class="portal-section">
        <h2><?= t('应用') ?></h2>
        <?php if (!$apps): ?>
            <p class="portal-muted"><?= t('还没有应用。') ?></p>
        <?php else: ?>
        <table class="portal-table">
            <thead>
                <tr>
                    <th><?= t('名称') ?></th>
                    <th>Client ID</th>
                    <th><?= t('状态') ?></th>
                    <th><?= t('创建时间') ?></th>
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
                            <span class="portal-badge portal-badge--on"><?= t('启用') ?></span>
                        <?php else: ?>
                            <span class="portal-badge portal-badge--off"><?= t('停用') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(substr((string)$app['created_at'], 0, 10)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <p class="portal-hint"><?= t('每个开发者最多创建 10 个应用') ?>（<?= count($apps) ?> / <?= (int)$maxApps ?>）</p>
    </section>
</div>
