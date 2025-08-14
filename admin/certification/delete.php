<?php
/**
 * ФАЙЛ 1: /admin/certification/delete.php
 * Удаление страницы сертификации - ИСПРАВЛЕНО
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию и права доступа
requireAuth();

$certModel = new CertificationPages();
$current_admin = getCurrentAdmin();

// Только админы могут удалять страницы
if ($current_admin['role'] !== 'admin') {
    header('Location: /admin/certification/?error=no_permissions');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /admin/certification/?error=invalid_id');
    exit;
}

try {
    // Получаем страницу для проверки и логирования
    $page = $certModel->getPageById($id);
    
    if (!$page) {
        header('Location: /admin/certification/?error=page_not_found');
        exit;
    }
    
    // Сохраняем название для сообщения
    $page_title = $page['title'];
    
    // Удаляем страницу
    $result = $certModel->deletePage($id);
    
    if ($result) {
        // Логируем действие
        error_log("Certificate page deleted: ID {$id}, Title: {$page_title}, Admin: {$current_admin['name']} ({$current_admin['email']})");
        
        // Перенаправляем с сообщением об успехе
        header('Location: /admin/certification/?success=page_deleted&title=' . urlencode($page_title));
    } else {
        header('Location: /admin/certification/?error=delete_failed');
    }
    
} catch (Exception $e) {
    error_log('Ошибка при удалении страницы сертификации: ' . $e->getMessage());
    header('Location: /admin/certification/?error=delete_failed');
}

exit;

?>
<?php
/**
 * ФАЙЛ 2: /admin/certification/toggle.php
 * Переключение статуса активности страницы - ИСПРАВЛЕНО
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию
requireAuth();

$certModel = new CertificationPages();
$current_admin = getCurrentAdmin();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /admin/certification/?error=invalid_id');
    exit;
}

try {
    // Получаем страницу для проверки
    $page = $certModel->getPageById($id);
    
    if (!$page) {
        header('Location: /admin/certification/?error=page_not_found');
        exit;
    }
    
    // Переключаем статус
    $result = $certModel->toggleActiveStatus($id);
    
    if ($result) {
        // Логируем действие
        $new_status = $page['is_active'] ? 'деактивирована' : 'активирована';
        error_log("Certificate page status changed: ID {$id}, Title: {$page['title']}, Status: {$new_status}, Admin: {$current_admin['name']} ({$current_admin['email']})");
        
        // Перенаправляем с сообщением об успехе
        header('Location: /admin/certification/?success=status_changed');
    } else {
        header('Location: /admin/certification/?error=toggle_failed');
    }
    
} catch (Exception $e) {
    error_log('Ошибка при изменении статуса страницы сертификации: ' . $e->getMessage());
    header('Location: /admin/certification/?error=toggle_failed');
}

exit;

?>
<?php
/**
 * ФАЙЛ 3: /admin/certification/actions.php - ДОПОЛНИТЕЛЬНЫЙ ФАЙЛ
 * Универсальный обработчик действий (опционально)
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию
requireAuth();

$certModel = new CertificationPages();
$current_admin = getCurrentAdmin();

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$id || !$action) {
    header('Location: /admin/certification/?error=invalid_request');
    exit;
}

try {
    switch ($action) {
        case 'delete':
            // Проверяем права (только админы могут удалять)
            if ($current_admin['role'] !== 'admin') {
                header('Location: /admin/certification/?error=no_permissions');
                exit;
            }
            
            $page = $certModel->getPageById($id);
            if (!$page) {
                header('Location: /admin/certification/?error=page_not_found');
                exit;
            }
            
            $certModel->deletePage($id);
            error_log("Certificate page deleted via actions: ID {$id}, Title: {$page['title']}, Admin: {$current_admin['name']}");
            header('Location: /admin/certification/?success=page_deleted&title=' . urlencode($page['title']));
            break;
            
        case 'toggle':
            $certModel->toggleActiveStatus($id);
            error_log("Certificate page status toggled via actions: ID {$id}, Admin: {$current_admin['name']}");
            header('Location: /admin/certification/?success=status_changed');
            break;
            
        case 'duplicate':
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
            $data['is_active'] = 0; // Деактивируем копию
            
            $new_id = $certModel->createPage($data);
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