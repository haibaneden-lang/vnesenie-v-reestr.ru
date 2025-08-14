<?php
/**
 * Редактирование страницы сертификации
 * Файл: /admin/certification/edit.php
 * ПОЛНОСТЬЮ ИСПРАВЛЕННАЯ ВЕРСИЯ с уникальными классами
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию
requireAuth();

$certModel = new CertificationPages();

$errors = [];
$success = false;
$page_id = intval($_GET['id'] ?? 0);

// Проверяем ID страницы
if (!$page_id) {
    header('Location: /admin/certification/?error=invalid_id');
    exit;
}

// Получаем страницу
$page = $certModel->getPageById($page_id);
if (!$page) {
    header('Location: /admin/certification/?error=not_found');
    exit;
}

// Список категорий для селекта
$categories_list = [
    'industrial' => 'ИСО',
    'medical' => 'ЭКО', 
    'radioelectronic' => 'Лицензирование',
    'software' => 'Программное обеспечение',
    'telecom' => 'Телекоммуникационное оборудование',
    'oil_gas' => 'Нефтегазовое оборудование',
    'other' => 'Другое'
];

$document_types = [
    'Сертификат соответствия',
    'Декларация соответствия', 
    'Заключение о подтверждении производства',
    'Разрешение на применение',
    'Техническое свидетельство',
    'Экспертное заключение',
    'Другой документ'
];

// Обработка автосохранения (AJAX)
if (!empty($_POST['auto_save'])) {
    header('Content-Type: application/json');
    
    // Очищаем контент для автосохранения
    $raw_content = $_POST['content'] ?? '';
    $clean_content = trim($raw_content);
    
    // Убираем BOM если есть
    if (substr($clean_content, 0, 3) === "\xEF\xBB\xBF") {
        $clean_content = substr($clean_content, 3);
    }
    
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'h1' => trim($_POST['h1'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
        'document_type' => trim($_POST['document_type'] ?? ''),
        'certificate_name' => trim($_POST['certificate_name'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'price_old' => !empty($_POST['price_old']) ? floatval($_POST['price_old']) : null,
        'currency' => $_POST['currency'] ?? 'RUB',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'certificate_image' => trim($_POST['certificate_image'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'content' => $clean_content,
        'requirements' => trim($_POST['requirements'] ?? ''),
        'documents_needed' => trim($_POST['documents_needed'] ?? ''),
        'duration' => trim($_POST['duration'] ?? ''),
        'validity_period' => trim($_POST['validity_period'] ?? ''),
        'guarantee' => trim($_POST['guarantee'] ?? ''),
        'category' => $_POST['category'] ?? '',
        'subcategory' => trim($_POST['subcategory'] ?? ''),
        'tags' => trim($_POST['tags'] ?? ''),
        'is_active' => !empty($_POST['is_active']),
        'is_featured' => !empty($_POST['is_featured']),
        'show_price' => !empty($_POST['show_price']),
        'show_order_button' => !empty($_POST['show_order_button']),
        'order_button_text' => trim($_POST['order_button_text'] ?? ''),
        'order_email' => trim($_POST['order_email'] ?? ''),
        'order_phone' => trim($_POST['order_phone'] ?? ''),
        'consultation_available' => !empty($_POST['consultation_available']),
        'sort_order' => intval($_POST['sort_order'] ?? 0)
    ];
    
    if (empty($data['h1'])) {
        $data['h1'] = $data['title'];
    }
    
    try {
        $certModel->updatePage($page_id, $data);
        echo json_encode(['success' => true, 'message' => 'Автосохранение выполнено']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Обработка основной формы
if ($_POST && empty($_POST['auto_save'])) {
    // Корректная обработка контента
    $raw_content = $_POST['content'] ?? '';
    
    // Логирование для отладки
    error_log("=== EDIT FORM SUBMISSION DEBUG ===");
    error_log("Raw POST content: " . $raw_content);
    error_log("Content length: " . strlen($raw_content));
    
    // Очищаем контент
    $clean_content = trim($raw_content);
    
    // Убираем BOM если есть
    if (substr($clean_content, 0, 3) === "\xEF\xBB\xBF") {
        $clean_content = substr($clean_content, 3);
    }
    
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'h1' => trim($_POST['h1'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
        'document_type' => trim($_POST['document_type'] ?? ''),
        'certificate_name' => trim($_POST['certificate_name'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'price_old' => !empty($_POST['price_old']) ? floatval($_POST['price_old']) : null,
        'currency' => $_POST['currency'] ?? 'RUB',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'certificate_image' => trim($_POST['certificate_image'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'content' => $clean_content,
        'requirements' => trim($_POST['requirements'] ?? ''),
        'documents_needed' => trim($_POST['documents_needed'] ?? ''),
        'duration' => trim($_POST['duration'] ?? ''),
        'validity_period' => trim($_POST['validity_period'] ?? ''),
        'guarantee' => trim($_POST['guarantee'] ?? ''),
        'category' => $_POST['category'] ?? '',
        'subcategory' => trim($_POST['subcategory'] ?? ''),
        'tags' => trim($_POST['tags'] ?? ''),
        'is_active' => !empty($_POST['is_active']),
        'is_featured' => !empty($_POST['is_featured']),
        'show_price' => !empty($_POST['show_price']),
        'show_order_button' => !empty($_POST['show_order_button']),
        'order_button_text' => trim($_POST['order_button_text'] ?? ''),
        'order_email' => trim($_POST['order_email'] ?? ''),
        'order_phone' => trim($_POST['order_phone'] ?? ''),
        'consultation_available' => !empty($_POST['consultation_available']),
        'sort_order' => intval($_POST['sort_order'] ?? 0)
    ];

    // Валидация
    if (empty($data['title'])) {
        $errors[] = 'Заголовок обязателен для заполнения';
    }

    if (empty($data['certificate_name'])) {
        $errors[] = 'Название сертификата обязательно для заполнения';
    }

    // Проверка контента (аналогично add.php)
    $content_to_check = $data['content'];
    $text_only = strip_tags($content_to_check);
    $text_only = trim($text_only);
    
    $empty_content_patterns = [
        '',
        '<p></p>',
        '<p><br></p>',
        '<p>&nbsp;</p>',
        '<br>',
        '<div></div>',
        '<p>Начните вводить описание сертификата...</p>',
        'Начните вводить описание сертификата...'
    ];
    
    $is_empty_content = empty($content_to_check) || 
                       in_array(trim($content_to_check), $empty_content_patterns) ||
                       empty($text_only) ||
                       $text_only === 'Начните вводить описание сертификата...';
    
    if ($is_empty_content) {
        $errors[] = 'Содержимое страницы обязательно для заполнения. Введите описание сертификата.';
    }

    if ($data['price'] < 0) {
        $errors[] = 'Цена не может быть отрицательной';
    }

    if (empty($data['h1'])) {
        $data['h1'] = $data['title'];
    }

    // Проверяем уникальность slug
    if (!empty($data['slug'])) {
        if (!$certModel->isSlugUnique($data['slug'], $page_id)) {
            $errors[] = 'URL (slug) уже используется другой страницей';
        }
    } else {
        $errors[] = 'URL (slug) обязателен для заполнения';
    }

    // Если нет ошибок - обновляем страницу
    if (empty($errors)) {
        try {
            $certModel->updatePage($page_id, $data);
            $success = true;
            
            // Обновляем данные для отображения
            $page = $certModel->getPageById($page_id);
            
            // Редирект с сообщением об успехе
            header('Location: /admin/certification/edit.php?id=' . $page_id . '&success=updated');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Ошибка при обновлении страницы: ' . $e->getMessage();
        }
    }
}

$current_admin = getCurrentAdmin();

// Проверяем сообщения
$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'updated':
            $success_message = 'Страница успешно обновлена!';
            break;
        case 'created':
            $success_message = 'Страница успешно создана!';
            break;
    }
}

// Заполняем данные формы
$form_data = $_POST ? $_POST : [
    'title' => $page['title'],
    'h1' => $page['h1'],
    'slug' => $page['slug'],
    'meta_description' => $page['meta_description'],
    'meta_keywords' => $page['meta_keywords'],
    'document_type' => $page['document_type'],
    'certificate_name' => $page['certificate_name'],
    'price' => $page['price'],
    'price_old' => $page['price_old'],
    'currency' => $page['currency'],
    'featured_image' => $page['featured_image'],
    'certificate_image' => $page['certificate_image'],
    'short_description' => $page['short_description'],
    'content' => $page['content'],
    'requirements' => $page['requirements'],
    'documents_needed' => $page['documents_needed'],
    'duration' => $page['duration'],
    'validity_period' => $page['validity_period'],
    'guarantee' => $page['guarantee'],
    'category' => $page['category'],
    'subcategory' => $page['subcategory'],
    'tags' => $page['tags'],
    'is_active' => $page['is_active'],
    'is_featured' => $page['is_featured'],
    'show_price' => $page['show_price'],
    'show_order_button' => $page['show_order_button'],
    'order_button_text' => $page['order_button_text'],
    'order_email' => $page['order_email'],
    'order_phone' => $page['order_phone'],
    'consultation_available' => $page['consultation_available'],
    'sort_order' => $page['sort_order']
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать страницу сертификации | Админ-панель</title>
    <link rel="stylesheet" href="/admin/admin-styles.css">
    <style>
        /* УНИКАЛЬНЫЕ СТИЛИ ДЛЯ РЕДАКТИРОВАНИЯ СЕРТИФИКАЦИИ - ПРЕФИКС edit-cert- */
        .edit-cert-editor-container {
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            background: white;
            overflow: hidden;
            margin-top: 8px;
        }

        .edit-cert-editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #e1e8ed;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .edit-cert-toolbar-btn {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #495057;
            transition: all 0.2s ease;
        }

        .edit-cert-toolbar-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .edit-cert-toolbar-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .edit-cert-toolbar-select {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            cursor: pointer;
        }

        .edit-cert-editor-content {
            position: relative;
            min-height: 400px;
        }

        .edit-cert-visual-editor {
            min-height: 400px;
            padding: 20px;
            border: none;
            outline: none;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: white;
        }

        .edit-cert-visual-editor:focus {
            outline: 2px solid #007bff;
            outline-offset: -2px;
        }

        .edit-cert-html-editor {
            width: 100%;
            min-height: 400px;
            padding: 20px;
            border: none;
            outline: none;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
            background: #f8f9fa;
            color: #495057;
            resize: vertical;
            box-sizing: border-box;
        }

        .edit-cert-editor-status {
            background: #e9ecef;
            padding: 8px 15px;
            font-size: 12px;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* УНИКАЛЬНЫЕ СТИЛИ ДЛЯ ВКЛАДОК РЕДАКТИРОВАНИЯ */
        .edit-cert-form-tabs {
            display: flex;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            border: 2px solid #e1e8ed;
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .edit-cert-form-tab {
            padding: 12px 20px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
            outline: none;
            flex: 1;
            text-align: center;
        }
        
        .edit-cert-form-tab:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .edit-cert-form-tab.edit-cert-active {
            background: white;
            color: #2c3e50;
            border-bottom: 2px solid white;
        }
        
        .edit-cert-tab-content {
            display: none;
            background: white;
            border: 2px solid #e1e8ed;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 30px;
        }
        
        .edit-cert-tab-content.edit-cert-active {
            display: block;
        }

        /* Стили для изображений */
        .edit-cert-image-upload-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .edit-cert-image-upload {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .edit-cert-image-upload:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }

        .edit-cert-image-upload.edit-cert-has-image {
            border-color: #28a745;
            background: #f8fff8;
        }

        .edit-cert-upload-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #6c757d;
        }

        .edit-cert-upload-hint {
            color: #6c757d;
            font-size: 14px;
        }

        .edit-cert-image-preview {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .edit-cert-remove-image-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* Сетка цен */
        .edit-cert-price-group {
            display: grid;
            grid-template-columns: 1fr 1fr 100px;
            gap: 15px;
            align-items: end;
        }

        /* Уведомления */
        .edit-cert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: editCertSlideIn 0.3s ease;
        }

        .edit-cert-notification-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .edit-cert-notification-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .edit-cert-notification-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @keyframes editCertSlideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Информация о странице */
        .edit-cert-page-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .edit-cert-page-info h4 {
            margin: 0 0 15px 0;
            color: #1976d2;
            font-size: 1.2rem;
        }
        
        .edit-cert-page-meta-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .edit-cert-page-meta-info > div {
            background: white;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #e3f2fd;
        }

        /* Автосохранение */
        .edit-cert-auto-save-status {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
            text-align: center;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .edit-cert-auto-save-status.saving {
            background: #fff3cd;
            color: #856404;
        }

        .edit-cert-auto-save-status.saved {
            background: #d4edda;
            color: #155724;
        }

        .edit-cert-auto-save-status.error {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .edit-cert-image-upload-group {
                grid-template-columns: 1fr;
            }
            
            .edit-cert-price-group {
                grid-template-columns: 1fr;
            }
            
            .edit-cert-form-tabs {
                flex-wrap: wrap;
            }
            
            .edit-cert-form-tab {
                flex: 1 1 50%;
                min-width: 120px;
            }

            .edit-cert-page-meta-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Боковая панель -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <div class="logo-icon">📋</div>
                <span>Админ-панель</span>
            </div>

            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/">📊 Dashboard</a></li>
                    <li><a href="/admin/news/">📰 Новости</a></li>
                    <li><a href="/admin/categories/">📁 Категории</a></li>
                    <li><a href="/admin/certification/" class="active">🏆 Сертификация</a></li>
                    <?php if ($current_admin['role'] === 'admin'): ?>
                        <li><a href="/admin/admins/">👥 Администраторы</a></li>
                    <?php endif; ?>
                    <li><a href="/admin/profile/">👤 Профиль</a></li>
                    <li><a href="/admin/logout.php">🚪 Выход</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Основной контент -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Редактировать страницу #<?php echo $page['id']; ?></h1>
                <div class="admin-actions">
                    <a href="/admin/certification/" class="btn btn-secondary">← Назад к списку</a>
                    <a href="/certification/<?php echo htmlspecialchars($page['slug']); ?>" target="_blank" class="btn btn-secondary">👁️ Просмотр</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($success_message): ?>
                    <div class="success">
                        <strong>Успех!</strong> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <strong>Ошибки:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Информация о странице -->
                <div class="edit-cert-page-info">
                    <h4>📊 Информация о странице</h4>
                    <div class="edit-cert-page-meta-info">
                        <div><strong>ID:</strong> <?php echo $page['id']; ?></div>
                        <div><strong>Создана:</strong> <?php echo date('d.m.Y H:i', strtotime($page['created_at'])); ?></div>
                        <div><strong>Обновлена:</strong> <?php echo date('d.m.Y H:i', strtotime($page['updated_at'])); ?></div>
                        <div><strong>Просмотры:</strong> <?php echo number_format($page['views_count']); ?></div>
                        <div><strong>Заказы:</strong> <?php echo number_format($page['orders_count']); ?></div>
                        <div><strong>Конверсия:</strong> <?php 
                            $conversion = $page['views_count'] > 0 ? round(($page['orders_count'] / $page['views_count']) * 100, 1) : 0;
                            echo $conversion; 
                        ?>%</div>
                        <div><strong>Автор:</strong> <?php echo htmlspecialchars($page['created_by_name'] ?? 'Неизвестно'); ?></div>
                        <div><strong>Статус:</strong> 
                            <span style="color: <?php echo $page['is_active'] ? '#28a745' : '#dc3545'; ?>">
                                <?php echo $page['is_active'] ? '✅ Активна' : '❌ Неактивна'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <form method="POST" class="form-row" id="editCertForm">
                    <!-- Основная форма -->
                    <div class="news-form" style="flex: 2;">
                        <!-- Вкладки с уникальными классами -->
                        <div class="edit-cert-form-tabs">
                            <button type="button" class="edit-cert-form-tab edit-cert-active" data-edit-cert-tab="basic">📝 Основное</button>
                            <button type="button" class="edit-cert-form-tab" data-edit-cert-tab="content">📄 Контент</button>
                            <button type="button" class="edit-cert-form-tab" data-edit-cert-tab="images">🖼️ Изображения</button>
                            <button type="button" class="edit-cert-form-tab" data-edit-cert-tab="details">⚙️ Детали</button>
                            <button type="button" class="edit-cert-form-tab" data-edit-cert-tab="seo">🔍 SEO</button>
                        </div>

                        <!-- Основная информация -->
                        <div class="edit-cert-tab-content edit-cert-active" id="edit-cert-basic">
                            <div class="form-group">
                                <label for="title">Заголовок страницы *</label>
                                <input type="text" id="title" name="title" required 
                                       value="<?php echo htmlspecialchars($form_data['title']); ?>"
                                       placeholder="Сертификат соответствия на промышленное оборудование">
                                <div class="form-help">SEO заголовок для страницы</div>
                            </div>

                            <div class="form-group">
                                <label for="h1">H1 заголовок</label>
                                <input type="text" id="h1" name="h1" 
                                       value="<?php echo htmlspecialchars($form_data['h1']); ?>"
                                       placeholder="H1 на странице (если отличается от заголовка)">
                            </div>

                            <div class="form-group">
                                <label for="certificate_name">Название сертификата *</label>
                                <input type="text" id="certificate_name" name="certificate_name" required
                                       value="<?php echo htmlspecialchars($form_data['certificate_name']); ?>"
                                       placeholder="Сертификат соответствия ГОСТ Р на промышленное оборудование">
                            </div>

                            <div class="form-group">
                                <label for="document_type">Тип документа</label>
                                <select id="document_type" name="document_type">
                                    <option value="">Выберите тип документа</option>
                                    <?php foreach ($document_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"
                                                <?php echo $form_data['document_type'] === $type ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="slug">URL (slug) *</label>
                                <input type="text" id="slug" name="slug" required
                                       value="<?php echo htmlspecialchars($form_data['slug']); ?>"
                                       placeholder="sertifikat-sootvetstviya-promyshlennoe-oborudovanie">
                            </div>

                            <div class="form-group">
                                <label for="category">Категория</label>
                                <select id="category" name="category">
                                    <option value="">Выберите категорию</option>
                                    <?php foreach ($categories_list as $key => $name): ?>
                                        <option value="<?php echo $key; ?>"
                                                <?php echo $form_data['category'] === $key ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="edit-cert-price-group">
                                <div class="form-group">
                                    <label for="price">Цена *</label>
                                    <input type="number" id="price" name="price" min="0" step="0.01" required
                                           value="<?php echo htmlspecialchars($form_data['price']); ?>"
                                           placeholder="35000">
                                </div>
                                <div class="form-group">
                                    <label for="price_old">Старая цена</label>
                                    <input type="number" id="price_old" name="price_old" min="0" step="0.01"
                                           value="<?php echo htmlspecialchars($form_data['price_old']); ?>"
                                           placeholder="45000">
                                </div>
                                <div class="form-group">
                                    <label for="currency">Валюта</label>
                                    <select id="currency" name="currency">
                                        <option value="RUB" <?php echo $form_data['currency'] === 'RUB' ? 'selected' : ''; ?>>₽ RUB</option>
                                        <option value="USD" <?php echo $form_data['currency'] === 'USD' ? 'selected' : ''; ?>>$ USD</option>
                                        <option value="EUR" <?php echo $form_data['currency'] === 'EUR' ? 'selected' : ''; ?>>€ EUR</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Контент -->
                        <div class="edit-cert-tab-content" id="edit-cert-content">
                            <div class="form-group">
                                <label for="short_description">Краткое описание</label>
                                <textarea id="short_description" name="short_description" rows="3"
                                          placeholder="Краткое описание услуги для превью"><?php echo htmlspecialchars($form_data['short_description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="content">Полное описание *</label>
                                <div class="edit-cert-editor-container">
                                    <!-- Панель инструментов -->
                                    <div class="edit-cert-editor-toolbar">
                                        <select class="edit-cert-toolbar-select" id="editCertFormatSelect" onchange="editCertFormatBlock()">
                                            <option value="">Формат</option>
                                            <option value="p">Обычный текст</option>
                                            <option value="h2">Заголовок 2</option>
                                            <option value="h3">Заголовок 3</option>
                                            <option value="h4">Заголовок 4</option>
                                        </select>

                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('bold')" title="Жирный">
                                            <strong>B</strong>
                                        </button>
                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('italic')" title="Курсив">
                                            <em>I</em>
                                        </button>
                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('underline')" title="Подчеркнутый">
                                            <u>U</u>
                                        </button>

                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('justifyLeft')" title="По левому краю">◧</button>
                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('justifyCenter')" title="По центру">▣</button>
                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('justifyRight')" title="По правому краю">◨</button>

                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('insertUnorderedList')" title="Маркированный список">• List</button>
                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertFormatText('insertOrderedList')" title="Нумерованный список">1. List</button>

                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertInsertLink()" title="Вставить ссылку">🔗</button>
                                        <button type="button" class="edit-cert-toolbar-btn" onclick="editCertRemoveFormat()" title="Очистить форматирование">✗</button>

                                        <button type="button" class="edit-cert-toolbar-btn" id="editCertHtmlModeBtn" onclick="editCertToggleHTMLMode()" title="HTML режим">
                                            &lt;/&gt;
                                        </button>
                                    </div>

                                    <!-- Область редактирования -->
                                    <div class="edit-cert-editor-content">
                                        <div id="editCertVisualEditor" class="edit-cert-visual-editor" contenteditable="true">
                                            <?php echo $form_data['content'] ?: '<p>Начните вводить описание сертификата...</p>'; ?>
                                        </div>
                                        <textarea id="content" name="content" class="edit-cert-html-editor" style="display: none;"><?php echo htmlspecialchars($form_data['content']); ?></textarea>
                                    </div>

                                    <!-- Статус редактора -->
                                    <div class="edit-cert-editor-status">
                                        <span id="editCertEditorMode">Визуальный режим</span>
                                        <span id="editCertWordCount">Слов: 0</span>
                                    </div>
                                </div>
                                
                                <!-- Автосохранение -->
                                <div class="edit-cert-auto-save-status" id="editCertAutoSaveStatus">
                                    Автосохранение отключено
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="requirements">Требования и условия</label>
                                <textarea id="requirements" name="requirements" rows="4"
                                          placeholder="Требования к продукции, условия получения сертификата"><?php echo htmlspecialchars($form_data['requirements']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="documents_needed">Необходимые документы</label>
                                <textarea id="documents_needed" name="documents_needed" rows="4"
                                          placeholder="Список документов, необходимых для оформления"><?php echo htmlspecialchars($form_data['documents_needed']); ?></textarea>
                            </div>
                        </div>

                        <!-- Изображения -->
                        <div class="edit-cert-tab-content" id="edit-cert-images">
                            <div class="edit-cert-image-upload-group">
                                <div class="form-group">
                                    <label>Главное изображение</label>
                                    <div class="edit-cert-image-upload <?php echo $form_data['featured_image'] ? 'edit-cert-has-image' : ''; ?>" onclick="document.getElementById('edit_cert_featured_image_input').click()">
                                        <div id="edit_cert_featured_image_preview">
                                            <?php if ($form_data['featured_image']): ?>
                                                <img src="<?php echo htmlspecialchars($form_data['featured_image']); ?>" class="edit-cert-image-preview" alt="Главное изображение">
                                                <button type="button" class="edit-cert-remove-image-btn" onclick="editCertRemoveImage(event, 'featured')">🗑️</button>
                                            <?php else: ?>
                                                <div class="edit-cert-upload-icon">🖼️</div>
                                                <div class="edit-cert-upload-hint">Нажмите для выбора</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="edit_cert_featured_image_input" accept="image/*" style="display: none;">
                                    </div>
                                    <input type="text" name="featured_image" id="featured_image" 
                                           placeholder="Или введите URL изображения"
                                           value="<?php echo htmlspecialchars($form_data['featured_image']); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Образец сертификата</label>
                                    <div class="edit-cert-image-upload <?php echo $form_data['certificate_image'] ? 'edit-cert-has-image' : ''; ?>" onclick="document.getElementById('edit_cert_certificate_image_input').click()">
                                        <div id="edit_cert_certificate_image_preview">
                                            <?php if ($form_data['certificate_image']): ?>
                                                <img src="<?php echo htmlspecialchars($form_data['certificate_image']); ?>" class="edit-cert-image-preview" alt="Образец сертификата">
                                                <button type="button" class="edit-cert-remove-image-btn" onclick="editCertRemoveImage(event, 'certificate')">🗑️</button>
                                            <?php else: ?>
                                                <div class="edit-cert-upload-icon">📜</div>
                                                <div class="edit-cert-upload-hint">Образец документа</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="edit_cert_certificate_image_input" accept="image/*" style="display: none;">
                                    </div>
                                    <input type="text" name="certificate_image" id="certificate_image" 
                                           placeholder="Или введите URL изображения сертификата"
                                           value="<?php echo htmlspecialchars($form_data['certificate_image']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Детали -->
                        <div class="edit-cert-tab-content" id="edit-cert-details">
                            <div class="form-group">
                                <label for="duration">Срок получения</label>
                                <input type="text" id="duration" name="duration"
                                       value="<?php echo htmlspecialchars($form_data['duration']); ?>"
                                       placeholder="5-10 рабочих дней">
                            </div>

                            <div class="form-group">
                                <label for="validity_period">Срок действия</label>
                                <input type="text" id="validity_period" name="validity_period"
                                       value="<?php echo htmlspecialchars($form_data['validity_period']); ?>"
                                       placeholder="3 года">
                            </div>

                            <div class="form-group">
                                <label for="guarantee">Гарантии</label>
                                <input type="text" id="guarantee" name="guarantee"
                                       value="<?php echo htmlspecialchars($form_data['guarantee']); ?>"
                                       placeholder="100% получение документа">
                            </div>

                            <div class="form-group">
                                <label for="subcategory">Подкатегория</label>
                                <input type="text" id="subcategory" name="subcategory"
                                       value="<?php echo htmlspecialchars($form_data['subcategory']); ?>"
                                       placeholder="Конкретная подкатегория">
                            </div>

                            <div class="form-group">
                                <label for="tags">Теги</label>
                                <input type="text" id="tags" name="tags"
                                       value="<?php echo htmlspecialchars($form_data['tags']); ?>"
                                       placeholder="сертификат, соответствие, ГОСТ">
                                <div class="form-help">Теги через запятую для поиска</div>
                            </div>

                            <div class="form-group">
                                <label for="order_button_text">Текст кнопки заказа</label>
                                <input type="text" id="order_button_text" name="order_button_text"
                                       value="<?php echo htmlspecialchars($form_data['order_button_text']); ?>"
                                       placeholder="Заказать сертификат">
                            </div>

                            <div class="form-group">
                                <label for="order_email">Email для заказов</label>
                                <input type="email" id="order_email" name="order_email"
                                       value="<?php echo htmlspecialchars($form_data['order_email']); ?>"
                                       placeholder="orders@example.com">
                            </div>

                            <div class="form-group">
                                <label for="order_phone">Телефон для заказов</label>
                                <input type="tel" id="order_phone" name="order_phone"
                                       value="<?php echo htmlspecialchars($form_data['order_phone']); ?>"
                                       placeholder="+7 (XXX) XXX-XX-XX">
                            </div>

                            <div class="form-group">
                                <label for="sort_order">Порядок сортировки</label>
                                <input type="number" id="sort_order" name="sort_order" min="0"
                                       value="<?php echo htmlspecialchars($form_data['sort_order']); ?>"
                                       placeholder="0">
                                <div class="form-help">Чем меньше число, тем выше в списке</div>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="edit-cert-tab-content" id="edit-cert-seo">
                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" rows="3" maxlength="160"
                                          placeholder="Описание для поисковых систем (до 160 символов)"><?php echo htmlspecialchars($form_data['meta_description']); ?></textarea>
                                <div class="form-help">SEO описание для поисковых систем</div>
                            </div>

                            <div class="form-group">
                                <label for="meta_keywords">Ключевые слова</label>
                                <textarea id="meta_keywords" name="meta_keywords" rows="2"
                                          placeholder="сертификат соответствия, промышленное оборудование"><?php echo htmlspecialchars($form_data['meta_keywords']); ?></textarea>
                                <div class="form-help">Ключевые слова через запятую</div>
                            </div>
                        </div>
                    </div>

                    <!-- Боковая панель -->
                    <div class="form-sidebar">
                        <!-- Публикация -->
                        <div class="sidebar-section">
                            <h3>Публикация</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                       <?php echo $form_data['is_active'] ? 'checked' : ''; ?>>
                                <label for="is_active">Активна</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                       <?php echo $form_data['is_featured'] ? 'checked' : ''; ?>>
                                <label for="is_featured">Рекомендуемая</label>
                            </div>
                        </div>

                        <!-- Отображение -->
                        <div class="sidebar-section">
                            <h3>Отображение</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="show_price" name="show_price" value="1"
                                       <?php echo $form_data['show_price'] ? 'checked' : ''; ?>>
                                <label for="show_price">Показывать цену</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="show_order_button" name="show_order_button" value="1"
                                       <?php echo $form_data['show_order_button'] ? 'checked' : ''; ?>>
                                <label for="show_order_button">Показывать кнопку заказа</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="consultation_available" name="consultation_available" value="1"
                                       <?php echo $form_data['consultation_available'] ? 'checked' : ''; ?>>
                                <label for="consultation_available">Доступна консультация</label>
                            </div>
                        </div>

                        <!-- Автосохранение -->
                        <div class="sidebar-section">
                            <h3>Автосохранение</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="enable_auto_save" checked>
                                <label for="enable_auto_save">Включить автосохранение</label>
                            </div>
                            
                            <div class="form-help">
                                Черновик сохраняется каждые 30 секунд
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="form-actions">
                            <button type="submit" name="action" value="save" class="btn-save" id="editCertSaveBtn">
                                💾 Сохранить изменения
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="editCertSaveDraft()">
                                📄 Сохранить черновик
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        console.log('🚀 Загрузка исправленного скрипта редактирования...');

        // Глобальные переменные с префиксом editCert
        let editCertIsHTMLMode = false;
        let editCertVisualEditor = null;
        let editCertHtmlEditor = null;
        let editCertAutoSaveInterval = null;
        let editCertAutoSaveEnabled = true;

        // ИСПРАВЛЕНО: Показ уведомлений с уникальными классами
        function editCertShowNotification(message, type) {
            // Удаляем существующие уведомления
            const existing = document.querySelectorAll('.edit-cert-notification');
            existing.forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = 'edit-cert-notification edit-cert-notification-' + (type || 'success');
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        }

        // ИСПРАВЛЕНО: Переключение вкладок с уникальными классами
        function editCertSwitchTab(tabId) {
            console.log('🔄 Переключение на вкладку:', tabId);
            
            // Убираем активность со всех вкладок и контента
            const allTabs = document.querySelectorAll('.edit-cert-form-tab');
            const allContent = document.querySelectorAll('.edit-cert-tab-content');
            
            allTabs.forEach(tab => {
                tab.classList.remove('edit-cert-active');
            });
            
            allContent.forEach(content => {
                content.classList.remove('edit-cert-active');
            });
            
            // Активируем выбранную вкладку
            const activeTab = document.querySelector(`[data-edit-cert-tab="${tabId}"]`);
            const activeContent = document.getElementById(`edit-cert-${tabId}`);
            
            if (activeTab) {
                activeTab.classList.add('edit-cert-active');
                console.log('✅ Активирована вкладка:', tabId);
            }
            
            if (activeContent) {
                activeContent.classList.add('edit-cert-active');
                console.log('✅ Показан контент:', tabId);
            }
        }

        // ИСПРАВЛЕНО: Инициализация редактора с уникальными ID
        function editCertInitEditor() {
            editCertVisualEditor = document.getElementById('editCertVisualEditor');
            editCertHtmlEditor = document.getElementById('content');

            if (!editCertVisualEditor || !editCertHtmlEditor) {
                console.error('❌ Элементы редактора не найдены');
                return;
            }

            console.log('✅ Элементы редактора найдены');

            // Инициализация контента
            const existingContent = editCertHtmlEditor.value.trim();
            
            if (existingContent && existingContent !== '') {
                editCertVisualEditor.innerHTML = existingContent;
                console.log('📝 Загружен существующий контент');
            } else {
                editCertVisualEditor.innerHTML = '<p>Начните вводить описание сертификата...</p>';
                editCertHtmlEditor.value = '';
                console.log('📝 Инициализирован пустой редактор');
            }

            // Синхронизация контента
            function editCertSyncToTextarea() {
                if (!editCertIsHTMLMode && editCertVisualEditor && editCertHtmlEditor) {
                    const content = editCertVisualEditor.innerHTML;
                    editCertHtmlEditor.value = content;
                    editCertUpdateWordCount();
                    console.log('🔄 Синхронизация visual -> textarea');
                }
            }

            // Обработчики событий
            ['input', 'keyup', 'blur', 'paste'].forEach(event => {
                editCertVisualEditor.addEventListener(event, editCertSyncToTextarea);
            });

            // Обработка фокуса
            editCertVisualEditor.addEventListener('focus', function() {
                const content = this.innerHTML.trim();
                if (content === '<p>Начните вводить описание сертификата...</p>' || content === '<p></p>') {
                    this.innerHTML = '<p></p>';
                    // Устанавливаем курсор
                    const range = document.createRange();
                    const sel = window.getSelection();
                    if (this.firstChild) {
                        range.setStart(this.firstChild, 0);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                }
                editCertSyncToTextarea();
            });

            // Синхронизация при изменении в HTML режиме
            editCertHtmlEditor.addEventListener('input', function() {
                if (editCertIsHTMLMode) {
                    editCertUpdateWordCount();
                }
            });

            // Принудительная синхронизация каждые 3 секунды
            setInterval(editCertSyncToTextarea, 3000);

            console.log('✅ Редактор инициализирован');
        }

        // ИСПРАВЛЕНО: Переключение режимов редактора
        function editCertToggleHTMLMode() {
            const htmlModeBtn = document.getElementById('editCertHtmlModeBtn');
            const editorMode = document.getElementById('editCertEditorMode');

            if (!editCertIsHTMLMode) {
                // Переключение в HTML режим
                editCertHtmlEditor.value = editCertVisualEditor.innerHTML;
                editCertVisualEditor.style.display = 'none';
                editCertHtmlEditor.style.display = 'block';
                
                if (htmlModeBtn) htmlModeBtn.innerHTML = 'Visual';
                if (editorMode) editorMode.textContent = 'HTML режим';
                
                editCertIsHTMLMode = true;
                editCertHtmlEditor.focus();
            } else {
                // Переключение в визуальный режим
                editCertVisualEditor.innerHTML = editCertHtmlEditor.value;
                editCertHtmlEditor.style.display = 'none';
                editCertVisualEditor.style.display = 'block';
                
                if (htmlModeBtn) htmlModeBtn.innerHTML = '&lt;/&gt;';
                if (editorMode) editorMode.textContent = 'Визуальный режим';
                
                editCertIsHTMLMode = false;
                editCertVisualEditor.focus();
            }
            
            editCertUpdateWordCount();
        }

        // Форматирование блока
        function editCertFormatBlock() {
            if (editCertIsHTMLMode) return;
            
            const select = document.getElementById('editCertFormatSelect');
            if (select && select.value) {
                document.execCommand('formatBlock', false, select.value);
                editCertVisualEditor.focus();
                editCertHtmlEditor.value = editCertVisualEditor.innerHTML;
                select.value = '';
            }
        }

        // Форматирование текста
        function editCertFormatText(command) {
            if (editCertIsHTMLMode) return;
            
            document.execCommand(command, false, null);
            editCertVisualEditor.focus();
            editCertHtmlEditor.value = editCertVisualEditor.innerHTML;
        }

        // Вставка ссылки
        function editCertInsertLink() {
            if (editCertIsHTMLMode) return;
            
            const url = prompt('Введите URL ссылки:');
            if (url) {
                const text = window.getSelection().toString() || prompt('Введите текст ссылки:') || url;
                document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${text}</a>`);
                editCertVisualEditor.focus();
                editCertHtmlEditor.value = editCertVisualEditor.innerHTML;
            }
        }

        // Очистка форматирования
        function editCertRemoveFormat() {
            if (editCertIsHTMLMode) return;
            
            document.execCommand('removeFormat', false, null);
            editCertVisualEditor.focus();
            editCertHtmlEditor.value = editCertVisualEditor.innerHTML;
        }

        // Обновление счетчика слов
        function editCertUpdateWordCount() {
            let text = '';
            
            if (editCertIsHTMLMode && editCertHtmlEditor) {
                text = editCertHtmlEditor.value.replace(/<[^>]*>/g, '');
            } else if (editCertVisualEditor) {
                text = editCertVisualEditor.textContent || '';
            }
            
            const words = text.trim().split(/\s+/).filter(word => word.length > 0);
            const wordCountEl = document.getElementById('editCertWordCount');
            if (wordCountEl) {
                wordCountEl.textContent = 'Слов: ' + words.length;
            }
        }

        // ИСПРАВЛЕНО: Работа с изображениями с уникальными ID
        function editCertRemoveImage(event, type) {
            event.stopPropagation();
            event.preventDefault();
            
            const fieldId = type === 'featured' ? 'featured_image' : 'certificate_image';
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                editCertUpdateImagePreview(type);
            }
        }

        function editCertUpdateImagePreview(type) {
            const fieldId = type === 'featured' ? 'featured_image' : 'certificate_image';
            const previewId = type === 'featured' ? 'edit_cert_featured_image_preview' : 'edit_cert_certificate_image_preview';
            const field = document.getElementById(fieldId);
            const preview = document.getElementById(previewId);
            
            if (!field || !preview) return;
            
            const imageUrl = field.value;
            const uploadDiv = preview.closest('.edit-cert-image-upload');
            
            if (imageUrl && imageUrl.trim()) {
                const icon = type === 'featured' ? '🖼️' : '📜';
                preview.innerHTML = `
                    <img src="${imageUrl}" class="edit-cert-image-preview" alt="Изображение">
                    <button type="button" class="edit-cert-remove-image-btn" onclick="editCertRemoveImage(event, '${type}')">🗑️</button>
                `;
                if (uploadDiv) uploadDiv.classList.add('edit-cert-has-image');
            } else {
                const icon = type === 'featured' ? '🖼️' : '📜';
                const hint = type === 'featured' ? 'Нажмите для выбора' : 'Образец документа';
                preview.innerHTML = `
                    <div class="edit-cert-upload-icon">${icon}</div>
                    <div class="edit-cert-upload-hint">${hint}</div>
                `;
                if (uploadDiv) uploadDiv.classList.remove('edit-cert-has-image');
            }
        }

        // ИСПРАВЛЕНО: Обработка загрузки файлов
        function editCertHandleFileUpload(inputId, type) {
            const input = document.getElementById(inputId);
            if (!input || !input.files || !input.files[0]) return;
            
            const file = input.files[0];

            if (!file.type.startsWith('image/')) {
                editCertShowNotification('Выберите изображение', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                editCertShowNotification('Размер файла не должен превышать 5MB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            editCertShowNotification('Загружаем изображение...');

            fetch('/admin/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fieldId = type === 'featured' ? 'featured_image' : 'certificate_image';
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.value = data.file_url;
                        editCertUpdateImagePreview(type);
                        editCertShowNotification('Изображение загружено успешно!');
                    }
                } else {
                    editCertShowNotification('Ошибка загрузки: ' + (data.error || 'Неизвестная ошибка'), 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки:', error);
                editCertShowNotification('Ошибка загрузки изображения. Проверьте подключение.', 'error');
            });
        }

        // ИСПРАВЛЕНО: Автосохранение
        function editCertAutoSave() {
            if (!editCertAutoSaveEnabled) return;

            const statusEl = document.getElementById('editCertAutoSaveStatus');
            
            // Принудительная синхронизация перед автосохранением
            if (!editCertIsHTMLMode && editCertVisualEditor && editCertHtmlEditor) {
                editCertHtmlEditor.value = editCertVisualEditor.innerHTML;
            }

            const formData = new FormData(document.getElementById('editCertForm'));
            formData.append('auto_save', '1');

            if (statusEl) {
                statusEl.textContent = 'Сохранение...';
                statusEl.className = 'edit-cert-auto-save-status saving';
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (statusEl) {
                    if (data.success) {
                        statusEl.textContent = 'Автосохранено в ' + new Date().toLocaleTimeString();
                        statusEl.className = 'edit-cert-auto-save-status saved';
                    } else {
                        statusEl.textContent = 'Ошибка автосохранения';
                        statusEl.className = 'edit-cert-auto-save-status error';
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка автосохранения:', error);
                if (statusEl) {
                    statusEl.textContent = 'Ошибка автосохранения';
                    statusEl.className = 'edit-cert-auto-save-status error';
                }
            });
        }

        // Сохранение черновика
        function editCertSaveDraft() {
            editCertAutoSave();
            editCertShowNotification('Черновик сохранен!', 'info');
        }

        // ИСПРАВЛЕНО: Строгая валидация формы
        function editCertValidateForm() {
            console.log('🔍 === НАЧАЛО ВАЛИДАЦИИ РЕДАКТИРОВАНИЯ ===');
            
            // Принудительная синхронизация
            if (!editCertIsHTMLMode && editCertVisualEditor && editCertHtmlEditor) {
                const visualContent = editCertVisualEditor.innerHTML;
                editCertHtmlEditor.value = visualContent;
                console.log('🔄 Принудительная синхронизация перед валидацией');
            }
            
            const title = document.getElementById('title');
            const certificateName = document.getElementById('certificate_name');
            const price = document.getElementById('price');
            
            if (!title || !title.value.trim()) {
                editCertShowNotification('Заголовок обязателен для заполнения', 'error');
                editCertSwitchTab('basic');
                if (title) title.focus();
                return false;
            }

            if (!certificateName || !certificateName.value.trim()) {
                editCertShowNotification('Название сертификата обязательно', 'error');
                editCertSwitchTab('basic');
                if (certificateName) certificateName.focus();
                return false;
            }

            if (!price || price.value <= 0) {
                editCertShowNotification('Укажите корректную цену', 'error');
                editCertSwitchTab('basic');
                if (price) price.focus();
                return false;
            }

            // Строгая проверка контента
            const contentValue = editCertHtmlEditor ? editCertHtmlEditor.value : '';
            console.log('📝 Проверяем контент:', contentValue);
            
            const textOnly = contentValue.replace(/<[^>]*>/g, '').trim();
            console.log('📝 Только текст:', textOnly);
            
            const emptyValues = [
                '',
                '<p></p>',
                '<p><br></p>',
                '<p>&nbsp;</p>',
                '<br>',
                '<div></div>',
                '<p>Начните вводить описание сертификата...</p>',
                'Начните вводить описание сертификата...'
            ];
            
            const isEmpty = emptyValues.includes(contentValue.trim()) || 
                           textOnly.length === 0 ||
                           textOnly === 'Начните вводить описание сертификата...';
            
            console.log('📝 Контент пустой?', isEmpty);
            
            if (isEmpty) {
                editCertShowNotification('Содержимое страницы обязательно! Введите описание сертификата.', 'error');
                editCertSwitchTab('content');
                
                if (editCertIsHTMLMode && editCertHtmlEditor) {
                    editCertHtmlEditor.focus();
                } else if (editCertVisualEditor) {
                    editCertVisualEditor.focus();
                }
                
                return false;
            }

            console.log('✅ Валидация прошла успешно!');
            return true;
        }

        // ИСПРАВЛЕНО: Инициализация при загрузке с уникальными обработчиками
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📋 Инициализация страницы редактирования сертификации...');
            
            // Инициализация редактора
            editCertInitEditor();
            
            // ИСПРАВЛЕНО: Настройка вкладок с уникальными атрибутами
            document.querySelectorAll('.edit-cert-form-tab').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-edit-cert-tab');
                    if (tabId) {
                        editCertSwitchTab(tabId);
                    }
                });
            });
            
            // Показываем первую вкладку
            editCertSwitchTab('basic');
            
            // ИСПРАВЛЕНО: Обработка отправки формы
            const form = document.getElementById('editCertForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('📝 Попытка отправки формы редактирования...');
                    
                    if (!editCertValidateForm()) {
                        console.log('❌ Валидация не прошла');
                        return false;
                    }
                    
                    console.log('✅ Валидация прошла - отправляем форму');
                    
                    const saveBtn = document.getElementById('editCertSaveBtn');
                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.textContent = '⏳ Сохранение...';
                    }
                    
                    this.submit();
                });
            }

            // ИСПРАВЛЕНО: Обработка загрузки файлов с уникальными ID
            const featuredInput = document.getElementById('edit_cert_featured_image_input');
            if (featuredInput) {
                featuredInput.addEventListener('change', () => editCertHandleFileUpload('edit_cert_featured_image_input', 'featured'));
            }

            const certificateInput = document.getElementById('edit_cert_certificate_image_input');
            if (certificateInput) {
                certificateInput.addEventListener('change', () => editCertHandleFileUpload('edit_cert_certificate_image_input', 'certificate'));
            }

            // Обновление превью при изменении URL
            const featuredImageInput = document.getElementById('featured_image');
            if (featuredImageInput) {
                featuredImageInput.addEventListener('input', () => editCertUpdateImagePreview('featured'));
            }

            const certificateImageInput = document.getElementById('certificate_image');
            if (certificateImageInput) {
                certificateImageInput.addEventListener('input', () => editCertUpdateImagePreview('certificate'));
            }

            // Настройка автосохранения
            const autoSaveCheckbox = document.getElementById('enable_auto_save');
            if (autoSaveCheckbox) {
                autoSaveCheckbox.addEventListener('change', function() {
                    editCertAutoSaveEnabled = this.checked;
                    const statusEl = document.getElementById('editCertAutoSaveStatus');
                    
                    if (editCertAutoSaveEnabled) {
                        editCertAutoSaveInterval = setInterval(editCertAutoSave, 30000); // каждые 30 секунд
                        if (statusEl) {
                            statusEl.textContent = 'Автосохранение включено';
                            statusEl.className = 'edit-cert-auto-save-status';
                        }
                    } else {
                        if (editCertAutoSaveInterval) {
                            clearInterval(editCertAutoSaveInterval);
                        }
                        if (statusEl) {
                            statusEl.textContent = 'Автосохранение отключено';
                            statusEl.className = 'edit-cert-auto-save-status';
                        }
                    }
                });
                
                // Запускаем автосохранение если включено
                if (autoSaveCheckbox.checked) {
                    editCertAutoSaveEnabled = true;
                    editCertAutoSaveInterval = setInterval(editCertAutoSave, 30000);
                }
            }

            // Инициализация превью изображений
            editCertUpdateImagePreview('featured');
            editCertUpdateImagePreview('certificate');
            editCertUpdateWordCount();
            
            // Показываем уведомление о готовности
            setTimeout(() => {
                editCertShowNotification('Редактор готов к работе! ✨ Автосохранение включено.', 'success');
            }, 1500);
            
            console.log('✅ Страница редактирования сертификации загружена успешно');
        });

        console.log('✅ Скрипт редактирования сертификации загружен');
    </script>
</body>
</html>