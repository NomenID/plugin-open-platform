<?php
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
                <span class="portal-badge portal-badge--on"><?= t('启用') ?></span>
            <?php else: ?>
                <span class="portal-badge portal-badge--off"><?= t('停用') ?></span>
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
            <strong><?= t('凭证只显示这一次') ?></strong><br>
            <?= t('关掉页面就看不到了。需要再拿一次只能重置密钥。') ?>
        </div>
        <div class="portal-secret" style="margin-top:10px">
            <span>Client ID</span>
            <span id="new-client-id"><?= htmlspecialchars($newClientId) ?></span>
            <button type="button" data-copy="#new-client-id" data-copied-label="<?= t('已复制') ?>"><?= t('复制') ?></button>
        </div>
        <div class="portal-secret" style="margin-top:8px">
            <span>Client Secret</span>
            <span id="new-client-secret"><?= htmlspecialchars($newClientSecret) ?></span>
            <button type="button" data-copy="#new-client-secret" data-copied-label="<?= t('已复制') ?>"><?= t('复制') ?></button>
        </div>
    </section>
    <?php endif; ?>

    <section class="portal-section">
        <div class="portal-grid">
            <div class="portal-card">
                <h3><?= t('授权用户') ?></h3>
                <p><?= (int)$stats['authorized_users'] ?></p>
            </div>
            <div class="portal-card">
                <h3><?= t('活跃 Token') ?></h3>
                <p><?= (int)$stats['active_tokens'] ?></p>
            </div>
            <div class="portal-card">
                <h3><?= t('客户端类型') ?></h3>
                <p><?= $isPublic ? t('公共客户端') : t('机密客户端') ?></p>
            </div>
            <div class="portal-card">
                <h3>PKCE</h3>
                <p><?= $pkceOn ? t('强制 PKCE') : t('可选') ?></p>
            </div>
        </div>
    </section>

    <section class="portal-section">
        <h2><?= t('接入信息') ?></h2>
        <table class="portal-table">
            <tbody>
                <tr><td><?= t('授权') ?></td><td><code id="ep-authorize"><?= htmlspecialchars($baseUrl . '/oauth/authorize') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-authorize" data-copied-label="<?= t('已复制') ?>"><?= t('复制') ?></button></td></tr>
                <tr><td><?= t('令牌') ?></td><td><code id="ep-token"><?= htmlspecialchars($baseUrl . '/oauth/token') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-token" data-copied-label="<?= t('已复制') ?>"><?= t('复制') ?></button></td></tr>
                <tr><td><?= t('用户信息') ?></td><td><code id="ep-userinfo"><?= htmlspecialchars($baseUrl . '/api/userinfo') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-userinfo" data-copied-label="<?= t('已复制') ?>"><?= t('复制') ?></button></td></tr>
                <tr><td><?= t('撤销') ?></td><td><code id="ep-revoke"><?= htmlspecialchars($baseUrl . '/oauth/revoke') ?></code></td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-copy="#ep-revoke" data-copied-label="<?= t('已复制') ?>"><?= t('复制') ?></button></td></tr>
                <tr><td><?= t('回调地址') ?></td><td colspan="2">
                    <?php foreach ($redirectUris as $uri): ?>
                        <div><code><?= htmlspecialchars((string)$uri) ?></code></div>
                    <?php endforeach; ?>
                </td></tr>
                <tr><td><?= t('权限') ?></td><td colspan="2"><code><?= htmlspecialchars(implode(' ', $appScopes)) ?></code></td></tr>
            </tbody>
        </table>
    </section>

    <section class="portal-section">
        <details>
            <summary><?= t('接入示例') ?></summary>
            <h3><?= t('发起授权') ?></h3>
            <pre class="portal-code"><?= htmlspecialchars($authorizeSample) ?></pre>
            <h3><?= t('换取令牌') ?></h3>
            <pre class="portal-code"><?= htmlspecialchars($tokenSample) ?></pre>
            <h3><?= t('读取用户信息') ?></h3>
            <pre class="portal-code">GET <?= htmlspecialchars($baseUrl . '/api/userinfo') ?>

Authorization: Bearer ACCESS_TOKEN</pre>
        </details>
    </section>

    <section class="portal-section">
        <h2><?= t('基本信息') ?></h2>
        <form method="POST" action="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id'] . '/update') ?>"
              enctype="multipart/form-data" class="portal-form">
            <?= \Nomen\Src\Csrf::field() ?>
            <?php require __DIR__ . '/_fields.php'; ?>
            <div class="portal-actions">
                <button type="submit" class="btn btn-primary"><?= t('保存') ?></button>
                <a class="btn btn-ghost" href="<?= htmlspecialchars($portalBase . '/dashboard') ?>"><?= t('返回') ?></a>
            </div>
        </form>
    </section>

    <section class="portal-danger">
        <h2><?= t('危险操作') ?></h2>
        <div class="portal-actions">
            <?php if (!$isPublic): ?>
            <form method="POST" action="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id'] . '/reset-secret') ?>"
                  onsubmit="return confirm('<?= t('重置后旧密钥立即失效，所有活跃 Token 一并作废。继续？') ?>')">
                <?= \Nomen\Src\Csrf::field() ?>
                <button type="submit" class="btn btn-ghost"><?= t('重置密钥') ?></button>
            </form>
            <?php endif; ?>
            <form method="POST" action="<?= htmlspecialchars($portalBase . '/apps/' . $app['client_id'] . '/delete') ?>"
                  onsubmit="return confirm('<?= t('删除后所有授权和 Token 都会失效，且不可恢复。继续？') ?>')">
                <?= \Nomen\Src\Csrf::field() ?>
                <button type="submit" class="btn btn-ghost" style="color:var(--color-danger)"><?= t('删除应用') ?></button>
            </form>
        </div>
    </section>
</div>
