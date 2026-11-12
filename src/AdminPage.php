<?php
/*
 * Copyright (c) 2025-2026 Yunyun (Wang Yaoze)
 * Licensed under the Apache License, Version 2.0.
 * See the LICENSE file in this repository for the full text.
 * Source: https://github.com/NomenID/plugin-open-platform
 * Project: https://nomenid.org/w/plugin-open-platform
 */

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

        $html = '<div class="a-card">';
        $html .= '<div class="a-card__head"><h2>' . t('Developers') . '</h2><span class="a-count">'
            . t('{:count} in total', ['count' => count($developers)]) . '</span></div>';
        $html .= '<div class="a-card__body' . ($developers ? ' a-card__body--flush' : '') . '">';

        if (!$developers) {
            $html .= '<p class="a-empty">' . t('No developers yet. Once the open platform plugin is on, users can apply from the site.') . '</p>';
        } else {
            $html .= '<div class="a-table-wrap"><table class="a-table"><thead><tr>'
                . '<th>' . t('Developer') . '</th><th>' . t('Account') . '</th><th>' . t('Apps') . '</th><th>' . t('Status') . '</th><th>' . t('Registered') . '</th><th class="a-td-actions">' . t('Actions') . '</th>'
                . '</tr></thead><tbody>';

            foreach ($developers as $developer) {
                $on = (int)$developer['status'] === 1;
                $apps = OAuthClients::all((int)$developer['id']);

                $html .= '<tr>'
                    . '<td>' . htmlspecialchars((string)$developer['name'])
                    . '<div class="a-sub">#' . (int)$developer['id'] . '</div></td>'
                    . '<td>' . htmlspecialchars((string)$developer['username'])
                    . '<div class="a-sub">' . htmlspecialchars((string)($developer['email'] ?? '')) . '</div></td>'
                    . '<td>' . count($apps) . '</td>'
                    . '<td>' . ($on
                        ? '<span class="a-badge a-badge--success">' . t('Active') . '</span>'
                        : '<span class="a-badge a-badge--danger">' . t('Disabled') . '</span>') . '</td>'
                    . '<td class="a-nowrap">' . htmlspecialchars((string)$developer['created_at']) . '</td>'
                    . '<td class="a-td-actions">'
                    . '<form method="POST" action="/admin/developers/' . (int)$developer['id'] . '/toggle" data-admin-ajax>'
                    . Csrf::field()
                    . '<button type="submit" class="a-btn a-btn--sm' . ($on ? ' a-btn--danger' : ' a-btn--ghost') . '">'
                    . ($on ? t('Disable') : t('Enable')) . '</button></form></td>'
                    . '</tr>';
            }

            $html .= '</tbody></table></div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    public function toggleStatus(string $id): void
    {
        $db = Database::getInstance();
        $developerId = (int)$id;

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Session::flash('error', t('The request could not be verified, try again'));
            Response::redirect('/admin/developers');
        }

        $stmt = $db->prepare('SELECT id, name, status FROM developers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $developerId]);
        $developer = $stmt->fetch();

        if (!$developer) {
            Session::flash('error', t('Developer not found'));
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

        Session::flash('success', $status === 1 ? t('Developer enabled') : t('Developer disabled'));
        Response::redirect('/admin/developers');
    }
}
