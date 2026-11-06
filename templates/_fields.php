<?php
/** @var array|null $app */
/** @var array $appScopes */
/** @var array $redirectUris */
/** @var array $availableScopes */
$isPublic = ($app['client_type'] ?? 'confidential') === 'public';
$pkceOn = $isPublic || (int)($app['pkce_required'] ?? 0) === 1;
?>
<div class="portal-field">
    <label for="app-name"><?= t('名称') ?></label>
    <input id="app-name" name="name" required minlength="2" maxlength="128"
           value="<?= htmlspecialchars((string)($app['name'] ?? '')) ?>" autocomplete="off">
</div>

<div class="portal-field">
    <label for="app-description"><?= t('说明') ?></label>
    <textarea id="app-description" name="description" rows="2"><?= htmlspecialchars((string)($app['description'] ?? '')) ?></textarea>
    <span class="portal-hint"><?= t('显示在授权页上，一句话说清这个应用是做什么的。') ?></span>
</div>

<div class="portal-field">
    <label for="app-homepage"><?= t('主页') ?></label>
    <input id="app-homepage" name="homepage" type="url" value="<?= htmlspecialchars((string)($app['homepage_url'] ?? '')) ?>"
           placeholder="https://example.com">
</div>

<div class="portal-field">
    <label for="app-redirects"><?= t('回调地址') ?></label>
    <textarea id="app-redirects" name="redirect_uris" rows="3" required
              placeholder="https://example.com/callback"><?= htmlspecialchars(implode("\n", $redirectUris)) ?></textarea>
    <span class="portal-hint"><?= t('每行一个。授权码只会跳到白名单里的地址，必须和请求里的 redirect_uri 完全一致。') ?></span>
</div>

<div class="portal-field">
    <label><?= t('权限') ?></label>
    <div class="portal-check-list">
        <?php foreach ($availableScopes as $scope => $label): ?>
            <?php $checked = $scope === 'openid' || in_array($scope, $appScopes, true); ?>
            <label class="portal-check">
                <input type="checkbox" name="scopes[]" value="<?= htmlspecialchars((string)$scope) ?>"
                       <?= $checked ? 'checked' : '' ?> <?= $scope === 'openid' ? 'disabled' : '' ?>>
                <span><?= htmlspecialchars(t((string)$label)) ?></span>
                <code><?= htmlspecialchars((string)$scope) ?></code>
            </label>
        <?php endforeach; ?>
    </div>
    <span class="portal-hint"><?= t('openid 始终包含；授权页只会按这里勾选的权限请求。') ?></span>
</div>

<div class="portal-field">
    <label><?= t('客户端类型') ?></label>
    <div class="portal-check-list">
        <label class="portal-check">
            <input type="radio" name="client_type" value="confidential" <?= $isPublic ? '' : 'checked' ?>>
            <span><?= t('机密客户端') ?></span>
            <code><?= t('有 Secret，适合服务端应用') ?></code>
        </label>
        <label class="portal-check">
            <input type="radio" name="client_type" value="public" <?= $isPublic ? 'checked' : '' ?>>
            <span><?= t('公共客户端') ?></span>
            <code><?= t('没有 Secret，强制 PKCE') ?></code>
        </label>
    </div>
</div>

<div class="portal-field">
    <label class="portal-check">
        <input type="checkbox" name="pkce_required" value="1" <?= $pkceOn ? 'checked' : '' ?>>
        <span><?= t('强制 PKCE') ?></span>
    </label>
</div>

<div class="portal-field">
    <label for="app-icon"><?= t('图标') ?></label>
    <?php if (!empty($app['icon'])): ?>
        <div style="margin-bottom:8px">
            <img src="<?= htmlspecialchars(\Nomen\Src\IconUpload::url((string)$app['icon'])) ?>" alt="" width="40" height="40">
        </div>
    <?php endif; ?>
    <input id="app-icon" type="file" name="icon" accept="image/png,image/jpeg,image/gif,image/webp">
    <span class="portal-hint"><?= t('PNG / JPEG / GIF / WebP，最大 2MB，会裁成 135×135。') ?></span>
</div>
