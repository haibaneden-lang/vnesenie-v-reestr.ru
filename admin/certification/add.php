<?php
/**
 * Добавление новой страницы сертификации
 * Файл: /admin/certification/add.php
 * ИСПРАВЛЕННАЯ ВЕРСИЯ с уникальными классами
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию
requireAuth();

$certModel = new CertificationPages();

$errors = [];
$success = false;

// Список категорий для селекта
$categories_list = [
    'industrial' => 'ИСО',
    'medical' => 'Экологическая сертификация',
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

// Обработка формы
if ($_POST) {
    // Корректная обработка контента
    $raw_content = $_POST['content'] ?? '';
    
    // Логирование для отладки
    error_log("=== FORM SUBMISSION DEBUG ===");
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

    // Проверка контента
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
        '<p>Начните вводить текст...</p>',
        'Начните вводить текст...'
    ];
    
    $is_empty_content = empty($content_to_check) || 
                       in_array(trim($content_to_check), $empty_content_patterns) ||
                       empty($text_only) ||
                       $text_only === 'Начните вводить текст...';
    
    if ($is_empty_content) {
        $errors[] = 'Содержимое страницы обязательно для заполнения. Введите описание сертификата.';
    }

    if ($data['price'] < 0) {
        $errors[] = 'Цена не может быть отрицательной';
    }

    // Генерируем slug если не указан
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = generateSlug($data['title']);
    }

    // Проверяем уникальность slug
    if (!empty($data['slug'])) {
        if (!$certModel->isSlugUnique($data['slug'])) {
            $data['slug'] = $data['slug'] . '-' . time();
        }
    } else {
        $errors[] = 'URL (slug) обязателен для заполнения';
    }

    // Если нет ошибок - создаем страницу
    if (empty($errors)) {
        try {
            $page_id = $certModel->createPage($data);
            $success = true;
            
            // Редирект с сообщением об успехе
            header('Location: /admin/certification/edit.php?id=' . $page_id . '&success=created');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Ошибка при создании страницы: ' . $e->getMessage();
        }
    }
}

// Функция генерации slug
function generateSlug($text) {
    $text = mb_strtolower($text, 'UTF-8');
    
    // Транслитерация
    $translitMap = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    $text = strtr($text, $translitMap);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

$current_admin = getCurrentAdmin();

// Заполняем данные формы
$form_data = $_POST ? $_POST : [
    'title' => '',
    'h1' => '',
    'slug' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'document_type' => '',
    'certificate_name' => '',
    'price' => 0,
    'price_old' => '',
    'currency' => 'RUB',
    'featured_image' => '',
    'certificate_image' => '',
    'short_description' => '',
    'content' => '',
    'requirements' => '',
    'documents_needed' => '',
    'duration' => '',
    'validity_period' => '',
    'guarantee' => '',
    'category' => '',
    'subcategory' => '',
    'tags' => '',
    'is_active' => true,
    'is_featured' => false,
    'show_price' => true,
    'show_order_button' => true,
    'order_button_text' => 'Заказать сертификат',
    'order_email' => '',
    'order_phone' => '',
    'consultation_available' => true,
    'sort_order' => 0
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($form_data['title']) ? htmlspecialchars($form_data['title']) . ' | Создание страницы' : 'Создать страницу сертификации | Админ-панель'; ?></title>
<meta name="description" content="<?php echo !empty($form_data['meta_description']) ? htmlspecialchars($form_data['meta_description']) : 'Создание новой страницы сертификации в админ-панели сайта Реестр Гарант'; ?>">
    <link rel="stylesheet" href="/admin/admin-styles.css">
    <style>
        /* УНИКАЛЬНЫЕ СТИЛИ ДЛЯ СЕРТИФИКАЦИИ - ПРЕФИКС cert- */
        .cert-editor-container {
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            background: white;
            overflow: hidden;
            margin-top: 8px;
        }

        .cert-editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #e1e8ed;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cert-toolbar-btn {
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

        .cert-toolbar-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .cert-toolbar-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .cert-toolbar-select {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            cursor: pointer;
        }

        .cert-editor-content {
            position: relative;
            min-height: 400px;
        }

        .cert-visual-editor {
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

        .cert-visual-editor:focus {
            outline: 2px solid #007bff;
            outline-offset: -2px;
        }

        .cert-html-editor {
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

        .cert-editor-status {
            background: #e9ecef;
            padding: 8px 15px;
            font-size: 12px;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* УНИКАЛЬНЫЕ СТИЛИ ДЛЯ ВКЛАДОК */
        .cert-form-tabs {
            display: flex;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            border: 2px solid #e1e8ed;
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .cert-form-tab {
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
        
        .cert-form-tab:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .cert-form-tab.cert-active {
            background: white;
            color: #2c3e50;
            border-bottom: 2px solid white;
        }
        
        .cert-tab-content {
            display: none;
            background: white;
            border: 2px solid #e1e8ed;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 30px;
        }
        
        .cert-tab-content.cert-active {
            display: block;
        }

        /* Стили для изображений */
        .cert-image-upload-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .cert-image-upload {
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

        .cert-image-upload:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }

        .cert-image-upload.cert-has-image {
            border-color: #28a745;
            background: #f8fff8;
        }

        .cert-upload-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #6c757d;
        }

        .cert-upload-hint {
            color: #6c757d;
            font-size: 14px;
        }

        .cert-image-preview {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .cert-remove-image-btn {
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
        .cert-price-group {
            display: grid;
            grid-template-columns: 1fr 1fr 100px;
            gap: 15px;
            align-items: end;
        }

        /* Уведомления */
        .cert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: certSlideIn 0.3s ease;
        }

        .cert-notification-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .cert-notification-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes certSlideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .cert-image-upload-group {
                grid-template-columns: 1fr;
            }
            
            .cert-price-group {
                grid-template-columns: 1fr;
            }
            
            .cert-form-tabs {
                flex-wrap: wrap;
            }
            
            .cert-form-tab {
                flex: 1 1 50%;
                min-width: 120px;
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
                <h1>Создать страницу сертификации</h1>
                <div class="admin-actions">
                    <a href="/admin/certification/" class="btn btn-secondary">← Назад к списку</a>
                </div>
            </header>

            <div class="admin-content">
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

                <form method="POST" class="form-row" id="certForm">
                    <!-- Основная форма -->
                    <div class="news-form" style="flex: 2;">
                        <!-- Вкладки с уникальными классами -->
                        <div class="cert-form-tabs">
                            <button type="button" class="cert-form-tab cert-active" data-cert-tab="basic">📝 Основное</button>
                            <button type="button" class="cert-form-tab" data-cert-tab="content">📄 Контент</button>
                            <button type="button" class="cert-form-tab" data-cert-tab="images">🖼️ Изображения</button>
                            <button type="button" class="cert-form-tab" data-cert-tab="details">⚙️ Детали</button>
                            <button type="button" class="cert-form-tab" data-cert-tab="seo">🔍 SEO</button>
                        </div>

                        <!-- Основная информация -->
                        <div class="cert-tab-content cert-active" id="cert-basic">
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

                            <div class="cert-price-group">
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
                        <div class="cert-tab-content" id="cert-content">
                            <div class="form-group">
                                <label for="short_description">Краткое описание</label>
                                <textarea id="short_description" name="short_description" rows="3"
                                          placeholder="Краткое описание услуги для превью"><?php echo htmlspecialchars($form_data['short_description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="content">Полное описание *</label>
                                <div class="cert-editor-container">
                                    <!-- Панель инструментов -->
                                    <div class="cert-editor-toolbar">
                                        <select class="cert-toolbar-select" id="certFormatSelect" onchange="certFormatBlock()">
                                            <option value="">Формат</option>
                                            <option value="p">Обычный текст</option>
                                            <option value="h2">Заголовок 2</option>
                                            <option value="h3">Заголовок 3</option>
                                            <option value="h4">Заголовок 4</option>
                                        </select>

                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('bold')" title="Жирный">
                                            <strong>B</strong>
                                        </button>
                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('italic')" title="Курсив">
                                            <em>I</em>
                                        </button>
                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('underline')" title="Подчеркнутый">
                                            <u>U</u>
                                        </button>

                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('justifyLeft')" title="По левому краю">◧</button>
                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('justifyCenter')" title="По центру">▣</button>
                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('justifyRight')" title="По правому краю">◨</button>

                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('insertUnorderedList')" title="Маркированный список">• List</button>
                                        <button type="button" class="cert-toolbar-btn" onclick="certFormatText('insertOrderedList')" title="Нумерованный список">1. List</button>

                                        <button type="button" class="cert-toolbar-btn" onclick="certInsertLink()" title="Вставить ссылку">🔗</button>
                                        <button type="button" class="cert-toolbar-btn" onclick="certRemoveFormat()" title="Очистить форматирование">✗</button>

                                        <button type="button" class="cert-toolbar-btn" id="certHtmlModeBtn" onclick="certToggleHTMLMode()" title="HTML режим">
                                            &lt;/&gt;
                                        </button>
                                    </div>

                                    <!-- Область редактирования -->
                                    <div class="cert-editor-content">
                                        <div id="certVisualEditor" class="cert-visual-editor" contenteditable="true">
                                            <?php echo $form_data['content'] ?: '<p>Начните вводить описание сертификата...</p>'; ?>
                                        </div>
                                        <textarea id="content" name="content" class="cert-html-editor" style="display: none;"><?php echo htmlspecialchars($form_data['content']); ?></textarea>
                                    </div>

                                    <!-- Статус редактора -->
                                    <div class="cert-editor-status">
                                        <span id="certEditorMode">Визуальный режим</span>
                                        <span id="certWordCount">Слов: 0</span>
                                    </div>
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
                        <div class="cert-tab-content" id="cert-images">
                            <div class="cert-image-upload-group">
                                <div class="form-group">
                                    <label>Главное изображение</label>
                                    <div class="cert-image-upload <?php echo $form_data['featured_image'] ? 'cert-has-image' : ''; ?>" onclick="document.getElementById('cert_featured_image_input').click()">
                                        <div id="cert_featured_image_preview">
                                            <?php if ($form_data['featured_image']): ?>
                                                <img src="<?php echo htmlspecialchars($form_data['featured_image']); ?>" class="cert-image-preview" alt="Главное изображение">
                                                <button type="button" class="cert-remove-image-btn" onclick="certRemoveImage(event, 'featured')">🗑️</button>
                                            <?php else: ?>
                                                <div class="cert-upload-icon">🖼️</div>
                                                <div class="cert-upload-hint">Нажмите для выбора</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="cert_featured_image_input" accept="image/*" style="display: none;">
                                    </div>
                                    <input type="text" name="featured_image" id="featured_image" 
                                           placeholder="Или введите URL изображения"
                                           value="<?php echo htmlspecialchars($form_data['featured_image']); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Образец сертификата</label>
                                    <div class="cert-image-upload <?php echo $form_data['certificate_image'] ? 'cert-has-image' : ''; ?>" onclick="document.getElementById('cert_certificate_image_input').click()">
                                        <div id="cert_certificate_image_preview">
                                            <?php if ($form_data['certificate_image']): ?>
                                                <img src="<?php echo htmlspecialchars($form_data['certificate_image']); ?>" class="cert-image-preview" alt="Образец сертификата">
                                                <button type="button" class="cert-remove-image-btn" onclick="certRemoveImage(event, 'certificate')">🗑️</button>
                                            <?php else: ?>
                                                <div class="cert-upload-icon">📜</div>
                                                <div class="cert-upload-hint">Образец документа</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="cert_certificate_image_input" accept="image/*" style="display: none;">
                                    </div>
                                    <input type="text" name="certificate_image" id="certificate_image" 
                                           placeholder="Или введите URL изображения сертификата"
                                           value="<?php echo htmlspecialchars($form_data['certificate_image']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Детали -->
                        <div class="cert-tab-content" id="cert-details">
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
                        <div class="cert-tab-content" id="cert-seo">
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

                        <!-- Действия -->
                        <div class="form-actions">
                            <button type="submit" name="action" value="save" class="btn-save" id="certSaveBtn">
                                💾 Создать страницу
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        console.log('🚀 Загрузка исправленного скрипта с уникальными классами...');

        // Глобальные переменные с префиксом cert
        let certIsHTMLMode = false;
        let certVisualEditor = null;
        let certHtmlEditor = null;

        // ИСПРАВЛЕНО: Показ уведомлений с уникальными классами
        function certShowNotification(message, type) {
            // Удаляем существующие уведомления
            const existing = document.querySelectorAll('.cert-notification');
            existing.forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = 'cert-notification cert-notification-' + (type || 'success');
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        }

        // ИСПРАВЛЕНО: Переключение вкладок с уникальными классами
        function certSwitchTab(tabId) {
            console.log('🔄 Переключение на вкладку:', tabId);
            
            // Убираем активность со всех вкладок и контента
            const allTabs = document.querySelectorAll('.cert-form-tab');
            const allContent = document.querySelectorAll('.cert-tab-content');
            
            allTabs.forEach(tab => {
                tab.classList.remove('cert-active');
            });
            
            allContent.forEach(content => {
                content.classList.remove('cert-active');
            });
            
            // Активируем выбранную вкладку
            const activeTab = document.querySelector(`[data-cert-tab="${tabId}"]`);
            const activeContent = document.getElementById(`cert-${tabId}`);
            
            if (activeTab) {
                activeTab.classList.add('cert-active');
                console.log('✅ Активирована вкладка:', tabId);
            }
            
            if (activeContent) {
                activeContent.classList.add('cert-active');
                console.log('✅ Показан контент:', tabId);
            }
        }

        // ИСПРАВЛЕНО: Инициализация редактора с уникальными ID
        function certInitEditor() {
            certVisualEditor = document.getElementById('certVisualEditor');
            certHtmlEditor = document.getElementById('content');

            if (!certVisualEditor || !certHtmlEditor) {
                console.error('❌ Элементы редактора не найдены');
                return;
            }

            console.log('✅ Элементы редактора найдены');

            // Инициализация контента
            const existingContent = certHtmlEditor.value.trim();
            
            if (existingContent && existingContent !== '') {
                certVisualEditor.innerHTML = existingContent;
                console.log('📝 Загружен существующий контент');
            } else {
                certVisualEditor.innerHTML = '<p>Начните вводить описание сертификата...</p>';
                certHtmlEditor.value = '';
                console.log('📝 Инициализирован пустой редактор');
            }

            // Синхронизация контента
            function certSyncToTextarea() {
                if (!certIsHTMLMode && certVisualEditor && certHtmlEditor) {
                    const content = certVisualEditor.innerHTML;
                    certHtmlEditor.value = content;
                    certUpdateWordCount();
                    console.log('🔄 Синхронизация visual -> textarea');
                }
            }

            // Обработчики событий
            ['input', 'keyup', 'blur', 'paste'].forEach(event => {
                certVisualEditor.addEventListener(event, certSyncToTextarea);
            });

            // Обработка фокуса
            certVisualEditor.addEventListener('focus', function() {
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
                certSyncToTextarea();
            });

            // Синхронизация при изменении в HTML режиме
            certHtmlEditor.addEventListener('input', function() {
                if (certIsHTMLMode) {
                    certUpdateWordCount();
                }
            });

            // Принудительная синхронизация каждые 3 секунды
            setInterval(certSyncToTextarea, 3000);

            console.log('✅ Редактор инициализирован');
        }

        // ИСПРАВЛЕНО: Переключение режимов редактора
        function certToggleHTMLMode() {
            const htmlModeBtn = document.getElementById('certHtmlModeBtn');
            const editorMode = document.getElementById('certEditorMode');

            if (!certIsHTMLMode) {
                // Переключение в HTML режим
                certHtmlEditor.value = certVisualEditor.innerHTML;
                certVisualEditor.style.display = 'none';
                certHtmlEditor.style.display = 'block';
                
                if (htmlModeBtn) htmlModeBtn.innerHTML = 'Visual';
                if (editorMode) editorMode.textContent = 'HTML режим';
                
                certIsHTMLMode = true;
                certHtmlEditor.focus();
            } else {
                // Переключение в визуальный режим
                certVisualEditor.innerHTML = certHtmlEditor.value;
                certHtmlEditor.style.display = 'none';
                certVisualEditor.style.display = 'block';
                
                if (htmlModeBtn) htmlModeBtn.innerHTML = '&lt;/&gt;';
                if (editorMode) editorMode.textContent = 'Визуальный режим';
                
                certIsHTMLMode = false;
                certVisualEditor.focus();
            }
            
            certUpdateWordCount();
        }

        // Форматирование блока
        function certFormatBlock() {
            if (certIsHTMLMode) return;
            
            const select = document.getElementById('certFormatSelect');
            if (select && select.value) {
                document.execCommand('formatBlock', false, select.value);
                certVisualEditor.focus();
                certHtmlEditor.value = certVisualEditor.innerHTML;
                select.value = '';
            }
        }

        // Форматирование текста
        function certFormatText(command) {
            if (certIsHTMLMode) return;
            
            document.execCommand(command, false, null);
            certVisualEditor.focus();
            certHtmlEditor.value = certVisualEditor.innerHTML;
        }

        // Вставка ссылки
        function certInsertLink() {
            if (certIsHTMLMode) return;
            
            const url = prompt('Введите URL ссылки:');
            if (url) {
                const text = window.getSelection().toString() || prompt('Введите текст ссылки:') || url;
                document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${text}</a>`);
                certVisualEditor.focus();
                certHtmlEditor.value = certVisualEditor.innerHTML;
            }
        }

        // Очистка форматирования
        function certRemoveFormat() {
            if (certIsHTMLMode) return;
            
            document.execCommand('removeFormat', false, null);
            certVisualEditor.focus();
            certHtmlEditor.value = certVisualEditor.innerHTML;
        }

        // Обновление счетчика слов
        function certUpdateWordCount() {
            let text = '';
            
            if (certIsHTMLMode && certHtmlEditor) {
                text = certHtmlEditor.value.replace(/<[^>]*>/g, '');
            } else if (certVisualEditor) {
                text = certVisualEditor.textContent || '';
            }
            
            const words = text.trim().split(/\s+/).filter(word => word.length > 0);
            const wordCountEl = document.getElementById('certWordCount');
            if (wordCountEl) {
                wordCountEl.textContent = 'Слов: ' + words.length;
            }
        }

        // Генерация slug
        function certGenerateSlug(text) {
            const translitMap = {
                'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
                'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
                'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
                'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
                'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
            };
            
            return text
                .toLowerCase()
                .replace(/[а-я]/g, char => translitMap[char] || char)
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        // ИСПРАВЛЕНО: Работа с изображениями с уникальными ID
        function certRemoveImage(event, type) {
            event.stopPropagation();
            event.preventDefault();
            
            const fieldId = type === 'featured' ? 'featured_image' : 'certificate_image';
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                certUpdateImagePreview(type);
            }
        }

        function certUpdateImagePreview(type) {
            const fieldId = type === 'featured' ? 'featured_image' : 'certificate_image';
            const previewId = type === 'featured' ? 'cert_featured_image_preview' : 'cert_certificate_image_preview';
            const field = document.getElementById(fieldId);
            const preview = document.getElementById(previewId);
            
            if (!field || !preview) return;
            
            const imageUrl = field.value;
            const uploadDiv = preview.closest('.cert-image-upload');
            
            if (imageUrl && imageUrl.trim()) {
                const icon = type === 'featured' ? '🖼️' : '📜';
                preview.innerHTML = `
                    <img src="${imageUrl}" class="cert-image-preview" alt="Изображение">
                    <button type="button" class="cert-remove-image-btn" onclick="certRemoveImage(event, '${type}')">🗑️</button>
                `;
                if (uploadDiv) uploadDiv.classList.add('cert-has-image');
            } else {
                const icon = type === 'featured' ? '🖼️' : '📜';
                const hint = type === 'featured' ? 'Нажмите для выбора' : 'Образец документа';
                preview.innerHTML = `
                    <div class="cert-upload-icon">${icon}</div>
                    <div class="cert-upload-hint">${hint}</div>
                `;
                if (uploadDiv) uploadDiv.classList.remove('cert-has-image');
            }
        }

        // ИСПРАВЛЕНО: Обработка загрузки файлов
        function certHandleFileUpload(inputId, type) {
            const input = document.getElementById(inputId);
            if (!input || !input.files || !input.files[0]) return;
            
            const file = input.files[0];

            if (!file.type.startsWith('image/')) {
                certShowNotification('Выберите изображение', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                certShowNotification('Размер файла не должен превышать 5MB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            certShowNotification('Загружаем изображение...');

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
                        certUpdateImagePreview(type);
                        certShowNotification('Изображение загружено успешно!');
                    }
                } else {
                    certShowNotification('Ошибка загрузки: ' + (data.error || 'Неизвестная ошибка'), 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки:', error);
                certShowNotification('Ошибка загрузки изображения. Проверьте подключение.', 'error');
            });
        }

        // ИСПРАВЛЕНО: Строгая валидация формы
        function certValidateForm() {
            console.log('🔍 === НАЧАЛО ВАЛИДАЦИИ ===');
            
            // Принудительная синхронизация
            if (!certIsHTMLMode && certVisualEditor && certHtmlEditor) {
                const visualContent = certVisualEditor.innerHTML;
                certHtmlEditor.value = visualContent;
                console.log('🔄 Принудительная синхронизация перед валидацией');
            }
            
            const title = document.getElementById('title');
            const certificateName = document.getElementById('certificate_name');
            const price = document.getElementById('price');
            
            if (!title || !title.value.trim()) {
                certShowNotification('Заголовок обязателен для заполнения', 'error');
                certSwitchTab('basic');
                if (title) title.focus();
                return false;
            }

            if (!certificateName || !certificateName.value.trim()) {
                certShowNotification('Название сертификата обязательно', 'error');
                certSwitchTab('basic');
                if (certificateName) certificateName.focus();
                return false;
            }

            if (!price || price.value <= 0) {
                certShowNotification('Укажите корректную цену', 'error');
                certSwitchTab('basic');
                if (price) price.focus();
                return false;
            }

            // Строгая проверка контента
            const contentValue = certHtmlEditor ? certHtmlEditor.value : '';
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
                certShowNotification('Содержимое страницы обязательно! Введите описание сертификата.', 'error');
                certSwitchTab('content');
                
                if (certIsHTMLMode && certHtmlEditor) {
                    certHtmlEditor.focus();
                } else if (certVisualEditor) {
                    certVisualEditor.focus();
                }
                
                return false;
            }

            console.log('✅ Валидация прошла успешно!');
            return true;
        }

        // ИСПРАВЛЕНО: Инициализация при загрузке с уникальными обработчиками
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📋 Инициализация страницы сертификации...');
            
            // Инициализация редактора
            certInitEditor();
            
            // ИСПРАВЛЕНО: Настройка вкладок с уникальными атрибутами
            document.querySelectorAll('.cert-form-tab').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-cert-tab');
                    if (tabId) {
                        certSwitchTab(tabId);
                    }
                });
            });
            
            // Показываем первую вкладку
            certSwitchTab('basic');
            
            // ИСПРАВЛЕНО: Обработка отправки формы
            const form = document.getElementById('certForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('📝 Попытка отправки формы...');
                    
                    if (!certValidateForm()) {
                        console.log('❌ Валидация не прошла');
                        return false;
                    }
                    
                    console.log('✅ Валидация прошла - отправляем форму');
                    
                    const saveBtn = document.getElementById('certSaveBtn');
                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.textContent = '⏳ Создание...';
                    }
                    
                    this.submit();
                });
            }

            // Автогенерация slug
            const titleInput = document.getElementById('title');
            if (titleInput) {
                titleInput.addEventListener('input', function() {
                    const slugField = document.getElementById('slug');
                    if (slugField && !slugField.value.trim()) {
                        slugField.value = certGenerateSlug(this.value);
                    }
                });
            }

            // ИСПРАВЛЕНО: Обработка загрузки файлов с уникальными ID
            const featuredInput = document.getElementById('cert_featured_image_input');
            if (featuredInput) {
                featuredInput.addEventListener('change', () => certHandleFileUpload('cert_featured_image_input', 'featured'));
            }

            const certificateInput = document.getElementById('cert_certificate_image_input');
            if (certificateInput) {
                certificateInput.addEventListener('change', () => certHandleFileUpload('cert_certificate_image_input', 'certificate'));
            }

            // Обновление превью при изменении URL
            const featuredImageInput = document.getElementById('featured_image');
            if (featuredImageInput) {
                featuredImageInput.addEventListener('input', () => certUpdateImagePreview('featured'));
            }

            const certificateImageInput = document.getElementById('certificate_image');
            if (certificateImageInput) {
                certificateImageInput.addEventListener('input', () => certUpdateImagePreview('certificate'));
            }

            // Инициализация превью изображений
            certUpdateImagePreview('featured');
            certUpdateImagePreview('certificate');
            certUpdateWordCount();
            
            // Показываем уведомление о готовности
            setTimeout(() => {
                certShowNotification('Редактор готов к работе! ✨', 'success');
            }, 1500);
            
            console.log('✅ Страница сертификации загружена успешно');
        });

        console.log('✅ Скрипт сертификации загружен');
    </script>
</body>
</html>