<?php
require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/News.php';

// Проверяем авторизацию
requireAuth();

$newsModel = new News();
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $newsModel->togglePublishStatus($id);
        header('Location: /admin/news/?success=status_updated');
    } catch (Exception $e) {
        header('Location: /admin/news/?error=toggle_failed');
    }
} else {
    header('Location: /admin/news/?error=invalid_id');
}
exit;
?>