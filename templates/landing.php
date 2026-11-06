<?php
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
        <h1><?= t('开放平台') ?></h1>
        <p class="portal-lede"><?= t('用 Nomen 账号登录你的应用。') ?> <?= t('OAuth 2.0 授权码流程，支持 PKCE。') ?></p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="portal-note portal-note--error" style="margin-top:20px"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="portal-note portal-note--ok" style="margin-top:20px"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($developer): ?>
        <section class="portal-section">
            <h2><?= t('开发者') ?></h2>
            <p>
                <?= htmlspecialchars((string)$developer['name']) ?>
                · <?= count($apps) ?> <?= t('个应用') ?>
                · <?= (int)$totalUsers ?> <?= t('授权用户') ?>
                · <?= (int)$activeTokens ?> <?= t('活跃 Token') ?>
            </p>
            <div class="portal-actions">
                <a class="btn btn-primary" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('进入控制台') ?></a>
                <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/settings') ?>"><?= t('开发者设置') ?></a>
            </div>
        </section>
    <?php elseif ($disabledDeveloper): ?>
        <section class="portal-section">
            <p class="portal-note portal-note--warn"><?= t('开发者账号已被停用，请联系管理员') ?></p>
        </section>
    <?php elseif ($loggedIn): ?>
        <section class="portal-section" id="become-developer">
            <h2><?= t('成为开发者') ?></h2>
            <p class="portal-muted"><?= t('填写名称后就能创建应用。') ?></p>
            <form method="POST" action="<?= htmlspecialchars($portalBase . '/register') ?>" class="portal-form">
                <?= \Nomen\Src\Csrf::field() ?>
                <div class="portal-field">
                    <label for="developer-name"><?= t('开发者 / 团队名称') ?></label>
                    <input id="developer-name" name="name" required minlength="2" maxlength="64"
                           placeholder="<?= t('2-64 个字符') ?>" autocomplete="organization">
                    <span class="portal-hint"><?= t('会显示在应用的授权页面上，注册后可以在开发者设置里修改') ?></span>
                </div>
                <div class="portal-actions">
                    <button type="submit" class="btn btn-primary"><?= t('注册成为开发者') ?></button>
                </div>
            </form>
        </section>
    <?php else: ?>
        <section class="portal-section">
            <p><?= t('先登录或注册 Nomen 账号，再申请开发者。') ?></p>
            <div class="portal-actions">
                <a class="btn btn-primary" href="/login?redirect_to=<?= rawurlencode($portalBase) ?>"><?= t('登录') ?></a>
                <a class="btn btn-ghost" href="/register"><?= t('注册') ?></a>
            </div>
        </section>
    <?php endif; ?>

    <section class="portal-section">
        <h2><?= t('应用接入') ?></h2>
        <table class="portal-table">
            <thead>
                <tr><th><?= t('名称') ?></th><th><?= t('地址') ?></th></tr>
            </thead>
            <tbody>
                <tr><td><?= t('授权') ?></td><td><code><?= htmlspecialchars($baseUrl . '/oauth/authorize') ?></code></td></tr>
                <tr><td><?= t('令牌') ?></td><td><code><?= htmlspecialchars($baseUrl . '/oauth/token') ?></code></td></tr>
                <tr><td><?= t('用户信息') ?></td><td><code><?= htmlspecialchars($baseUrl . '/api/userinfo') ?></code></td></tr>
                <tr><td><?= t('撤销') ?></td><td><code><?= htmlspecialchars($baseUrl . '/oauth/revoke') ?></code></td></tr>
            </tbody>
        </table>
    </section>

    <section class="portal-section">
        <h2><?= t('权限') ?></h2>
        <ul class="portal-list">
            <li><code>openid</code> · <?= t('基本用户信息') ?></li>
            <li><code>profile</code> · <?= t('用户名、昵称、头像') ?></li>
            <li><code>email</code> · <?= t('已验证邮箱') ?></li>
        </ul>
    </section>

    <?php if ($docsBase): ?>
    <section class="portal-section">
        <h2><?= t('文档') ?></h2>
        <p><a href="<?= htmlspecialchars($docsBase . '/oauth') ?>"><?= t('OAuth 2.0 接入') ?></a></p>
    </section>
    <?php endif; ?>
</div>
