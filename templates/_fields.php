<?php
/** @var array|null $app */
/** @var array $appScopes */
/** @var array $redirectUris */
/** @var array $availableScopes */
$isPublic = ($app['client_type'] ?? 'confidential') === 'public';
$pkceOn = $isPublic || (int)($app['pkce_required'] ?? 0) === 1;
?>
<div class="portal-field">
    <label for="app-name"><?= t('Name') ?></label>
    <input id="app-name" name="name" required minlength="2" maxlength="128"
           value="<?= htmlspecialchars((string)($app['name'] ?? '')) ?>" autocomplete="off">
</div>

<div class="portal-field">
    <label for="app-description"><?= t('Description') ?></label>
    <textarea id="app-description" name="description" rows="2"><?= htmlspecialchars((string)($app['description'] ?? '')) ?></textarea>
    <span class="portal-hint"><?= t('Shown on the consent screen. One sentence about what the app does.') ?></span>
</div>

<div class="portal-field">
    <label for="app-homepage"><?= t('Homepage') ?></label>
    <input id="app-homepage" name="homepage" type="url" value="<?= htmlspecialchars((string)($app['homepage_url'] ?? '')) ?>"
           placeholder="https://example.com">
</div>

<div class="portal-field">
    <label for="app-redirects"><?= t('Redirect URI') ?></label>
    <textarea id="app-redirects" name="redirect_uris" rows="3" required
              placeholder="https://example.com/callback"><?= htmlspecialchars(implode("\n", $redirectUris)) ?></textarea>
    <span class="portal-hint"><?= t('One per line. Authorization codes are only sent to these URIs; the request must match one exactly.') ?></span>
</div>

<div class="portal-field">
    <label><?= t('Scopes') ?></label>
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
    <span class="portal-hint"><?= t('openid is always included. The consent page only requests the scopes checked here.') ?></span>
</div>

<div class="portal-field">
    <label><?= t('Client type') ?></label>
    <div class="portal-check-list">
        <label class="portal-check">
            <input type="radio" name="client_type" value="confidential" <?= $isPublic ? '' : 'checked' ?>>
            <span><?= t('Confidential client') ?></span>
            <code><?= t('Has a secret; for server-side apps') ?></code>
        </label>
        <label class="portal-check">
            <input type="radio" name="client_type" value="public" <?= $isPublic ? 'checked' : '' ?>>
            <span><?= t('Public client') ?></span>
            <code><?= t('No secret; PKCE required') ?></code>
        </label>
    </div>
</div>

<div class="portal-field">
    <label class="portal-check">
        <input type="checkbox" name="pkce_required" value="1" <?= $pkceOn ? 'checked' : '' ?>>
        <span><?= t('Require PKCE') ?></span>
    </label>
</div>

<div class="portal-field">
    <label for="app-icon"><?= t('Icon') ?></label>
    <?php if (!empty($app['icon'])): ?>
        <div style="margin-bottom:8px">
            <img src="<?= htmlspecialchars(\Nomen\Src\IconUpload::url((string)$app['icon'])) ?>" alt="" width="40" height="40">
        </div>
    <?php endif; ?>
    <input id="app-icon" type="file" name="icon" accept="image/png,image/jpeg,image/gif,image/webp">
    <span class="portal-hint"><?= t('PNG, JPEG, GIF or WebP, up to 2 MB. Cropped to 135×135.') ?></span>
</div>
