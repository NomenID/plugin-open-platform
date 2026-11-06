# Open Platform

开发者门户。用户在 `/open` 注册开发者，然后自助创建、修改和删除自己的 OAuth 应用。

Open Platform 是独立项目，不属于 Nomen 本体，按 GPLv3 单独发布：
<https://github.com/NomenID/plugin-open-platform>。

管理员不需要这个插件也能建应用：管理后台的「应用」页直接调 `OAuthClients`，和插件用的是同一套逻辑。

## 启用

默认在 `config/plugins.php` 的 `enabled` 列表里。删掉 `open-platform` 就关掉前台入口，`/open` 会 404；`developers` 和 `oauth_clients` 数据留在库里，重新启用后回来。

## 配置

`config/plugins/open-platform.json`：

```json
{
    "base_path": "/open"
}
```

后台「接入点」页也能改。改完下一次请求生效；路由和导航都用 `Plugin::basePath()` 重新算。

## 路由

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `/open` | 公开着陆页 |
| POST | `/open/register` | 注册开发者（登录后） |
| GET | `/open/dashboard` | 我的应用 |
| GET | `/open/settings` | 开发者设置 |
| GET/POST | `/open/apps/create` | 创建应用 |
| GET | `/open/apps/{clientId}` | 应用详情 |
| POST | `/open/apps/{clientId}/update` | 保存基本信息 |
| POST | `/open/apps/{clientId}/reset-secret` | 重置密钥 |
| POST | `/open/apps/{clientId}/delete` | 删除应用 |
| POST | `/open/profile` | 保存开发者资料 |

后台：

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `/admin/developers` | 开发者列表（`adminPages` 注册） |
| POST | `/admin/developers/{id}/toggle` | 启用/停用开发者 |

## 数据

用的是核心表：

- `developers`：开发者身份，`user_id` 唯一，`status = 0` 表示停用。
- `oauth_clients`：应用，`developer_id` 指向开发者；管理员建的应用 `developer_id` 为空、`owner_id` 是管理员。
- `user_authorizations`、`oauth_access_tokens`、`oauth_refresh_tokens`：授权和令牌，重置密钥时作废。

插件自己不需要建表，所以没有 `database/migrations`。

## 规则

- 一个开发者最多 10 个应用；注册开发者每小时 1 次，建应用每分钟 1 次，重置密钥每小时 3 次。
- 公共客户端不显示重置密钥，强制 PKCE。
- Client Secret 只显示一次，存哈希；重置后旧 Token 全部作废。
- 应用图标用 `IconUpload::store()`，裁成 135×135，存 `public/assets/develops/`。
- 所有写操作都记 `ActivityLog`，动作名 `developer.*`。

## 许可

GPL-3.0-only，见同目录 `LICENSE`。
