<?php
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
        <h1><?= t('创建应用') ?></h1>
        <p class="portal-lede"><?= t('回调地址和权限先配好，创建后就能接入。') ?></p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($action) ?>" enctype="multipart/form-data" class="portal-form" style="margin-top:28px">
        <?= \Nomen\Src\Csrf::field() ?>
        <?php require __DIR__ . '/_fields.php'; ?>
        <div class="portal-actions">
            <button type="submit" class="btn btn-primary"><?= t('创建应用') ?></button>
            <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('取消') ?></a>
        </div>
    </form>
</div>
