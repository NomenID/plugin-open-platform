<?php
namespace Nomen\Plugin\OpenPlatform;

use Nomen\Src\{ActivityLog, Csrf, Database, OAuthClients, Response, Session};

class AdminPage
{
    public static function render(): string
    {
        $db = Database::getInstance();
        $developers = $db->query(
            'SELECT d.*, u.username, u.email, u.status AS user_status,
                    (SELECT COUNT(*) FROM oauth_clients oc WHERE oc.developer_id = d.id) AS app_count
             FROM developers d
             JOIN users u ON u.id = d.user_id
             ORDER BY d.created_at DESC'
        )->fetchAll();

        $html = '<div class="admin-card">';
        $html .= '<div class="admin-card__head"><h2>开发者</h2><span class="admin-cell-sub">共 ' . count($developers) . ' 个</span></div>';

        if (!$developers) {
            $html .= '<p class="admin-empty">还没有人注册开发者。启用开放平台插件后，用户可以在前台申请。</p>';
        } else {
            $html .= '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>'
                . '<th>开发者</th><th>账号</th><th>应用</th><th>状态</th><th>注册时间</th><th class="admin-th-actions">操作</th>'
                . '</tr></thead><tbody>';

            foreach ($developers as $developer) {
                $on = (int)$developer['status'] === 1;
                $apps = OAuthClients::all((int)$developer['id']);

                $html .= '<tr>'
                    . '<td>' . htmlspecialchars((string)$developer['name'])
                    . '<div class="admin-cell-sub">#' . (int)$developer['id'] . '</div></td>'
                    . '<td>' . htmlspecialchars((string)$developer['username'])
                    . '<div class="admin-cell-sub">' . htmlspecialchars((string)($developer['email'] ?? '')) . '</div></td>'
                    . '<td>' . count($apps) . '</td>'
                    . '<td>' . ($on
                        ? '<span class="admin-badge admin-badge--success">正常</span>'
                        : '<span class="admin-badge admin-badge--danger">已停用</span>') . '</td>'
                    . '<td class="admin-nowrap">' . htmlspecialchars((string)$developer['created_at']) . '</td>'
                    . '<td class="admin-td-actions">'
                    . '<form method="POST" action="/admin/developers/' . (int)$developer['id'] . '/toggle">'
                    . Csrf::field()
                    . '<button type="submit" class="admin-btn admin-btn--sm' . ($on ? ' admin-btn--danger' : '') . '">'
                    . ($on ? '停用' : '启用') . '</button></form></td>'
                    . '</tr>';
            }

            $html .= '</tbody></table></div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function toggleStatus(string $id): void
    {
        $db = Database::getInstance();
        $developerId = (int)$id;

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', '请求无效，请重试');
            Response::redirect('/admin/developers');
        }

        $stmt = $db->prepare('SELECT id, name, status FROM developers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $developerId]);
        $developer = $stmt->fetch();

        if (!$developer) {
            Session::flash('error', '开发者不存在');
            Response::redirect('/admin/developers');
        }

        $status = (int)$developer['status'] === 1 ? 0 : 1;
        $db->prepare('UPDATE developers SET status = :status WHERE id = :id')
            ->execute([':status' => $status, ':id' => $developerId]);

        ActivityLog::record([
            'action'      => 'admin.developer_toggle',
            'category'    => ActivityLog::CAT_ADMIN,
            'target_type' => 'developer',
            'target_id'   => $developerId,
            'target_name' => (string)$developer['name'],
            'summary'     => ($status === 1 ? '启用' : '停用') . '开发者 ' . $developer['name'],
        ]);

        Session::flash('success', $status === 1 ? '开发者已启用' : '开发者已停用');
        Response::redirect('/admin/developers');
    }
}
