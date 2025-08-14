<?php
/**
 * Обработчики действий для сертификации
 * Файл: /admin/certification/actions.php
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию и права
requireAuth();

$certModel = new CertificationPages();
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$id || !$action) {
    header('Location: /admin/certification/?error=invalid_request');
    exit;
}

try {
    switch ($action) {
        case 'delete':
            // Получаем страницу для логирования
            $page = $certModel->getPageById($id);
            
            if (!$page) {
                header('Location: /admin/certification/?error=page_not_found');
                exit;
            }
            
            // Проверяем права (только админы могут удалять)
            $current_admin = getCurrentAdmin();
            if ($current_admin['role'] !== 'admin') {
                header('Location: /admin/certification/?error=no_permissions');
                exit;
            }
            
            // Удаляем страницу
            $certModel->deletePage($id);
            
            // Логируем действие
            error_log("Certificate page deleted: ID {$id}, Title: {$page['title']}, Admin: {$current_admin['name']}");
            
            header('Location: /admin/certification/?success=page_deleted&title=' . urlencode($page['title']));
            break;
            
        case 'toggle':
            // Переключаем статус активности
            $certModel->toggleActiveStatus($id);
            
            // Логируем действие
            $current_admin = getCurrentAdmin();
            error_log("Certificate page status toggled: ID {$id}, Admin: {$current_admin['name']}");
            
            header('Location: /admin/certification/?success=status_changed');
            break;
            
        case 'duplicate':
            // Дублируем страницу
            $page = $certModel->getPageById($id);
            
            if (!$page) {
                header('Location: /admin/certification/?error=page_not_found');
                exit;
            }
            
            // Создаем копию
            $data = $page;
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['views_count'], $data['orders_count']);
            
            // Изменяем название и slug
            $data['title'] = $data['title'] . ' (копия)';
            $data['slug'] = $data['slug'] . '-copy-' . time();
            $data['is_active'] = false; // Деактивируем копию
            
            $new_id = $certModel->createPage($data);
            
            // Логируем действие
            $current_admin = getCurrentAdmin();
            error_log("Certificate page duplicated: Original ID {$id}, New ID {$new_id}, Admin: {$current_admin['name']}");
            
            header('Location: /admin/certification/edit.php?id=' . $new_id . '&success=duplicated');
            break;
            
        default:
            header('Location: /admin/certification/?error=unknown_action');
            break;
    }
    
} catch (Exception $e) {
    error_log("Certificate action error: " . $e->getMessage());
    header('Location: /admin/certification/?error=' . urlencode($e->getMessage()));
}
exit;
?>