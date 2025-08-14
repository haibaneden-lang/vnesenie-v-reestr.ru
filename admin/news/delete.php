<?php
require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/News.php';

// Проверяем авторизацию и права
requireAuth();

$newsModel = new News();
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // Получаем новость для проверки
        $news = $newsModel->getNewsById($id);
        
        if ($news) {
            // Удаляем новость
            $newsModel->deleteNews($id);
            header('Location: /admin/news/?success=news_deleted');
        } else {
            header('Location: /admin/news/?error=news_not_found');
        }
    } catch (Exception $e) {
        header('Location: /admin/news/?error=delete_failed');
    }
} else {
    header('Location: /admin/news/?error=invalid_id');
}
exit;
?>