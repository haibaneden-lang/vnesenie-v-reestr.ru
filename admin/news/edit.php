<?php
require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/News.php';

// Проверяем авторизацию
requireAuth();

$newsModel = new News();
$categoryModel = new NewsCategory();

$errors = [];
$success = false;
$news_id = intval($_GET['id'] ?? 0);

// Проверяем ID новости
if (!$news_id) {
    header('Location: /admin/news/?error=invalid_id');
    exit;
}

// Получаем новость
$news = $newsModel->getNewsById($news_id);
if (!$news) {
    header('Location: /admin/news/?error=not_found');
    exit;
}

// Обработка автосохранения (AJAX)
if (!empty($_POST['auto_save'])) {
    header('Content-Type: application/json');
    
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'h1' => trim($_POST['h1'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'category_id' => intval($_POST['category_id']) ?: null,
        'published_at' => $_POST['published_at'] ?? null,
        'is_published' => false,
        'is_featured' => false
    ];
    
    if (empty($data['h1'])) {
        $data['h1'] = $data['title'];
    }
    
    try {
        $newsModel->updateNews($news_id, $data);
        echo json_encode(['success' => true, 'message' => 'Автосохранение выполнено']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Обработка формы
if ($_POST && empty($_POST['auto_save'])) {
    $data = [
        'category_id' => intval($_POST['category_id']) ?: null,
        'title' => trim($_POST['title'] ?? ''),
        'h1' => trim($_POST['h1'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'is_published' => !empty($_POST['is_published']),
        'is_featured' => !empty($_POST['is_featured']),
        'published_at' => $_POST['published_at'] ?? null
    ];

    // Валидация
    if (empty($data['title'])) {
        $errors[] = 'Заголовок обязателен для заполнения';
    }
    
    if (empty($data['h1'])) {
        $data['h1'] = $data['title'];
    }
    
    if (empty($data['content'])) {
        $errors[] = 'Содержимое статьи обязательно для заполнения';
    }

    // Если нет ошибок - обновляем новость
    if (empty($errors)) {
        try {
            $newsModel->updateNews($news_id, $data);
            $success = true;
            
            // Обновляем данные для отображения
            $news = $newsModel->getNewsById($news_id);
            
            // Редирект с сообщением об успехе
            header('Location: /admin/news/edit.php?id=' . $news_id . '&success=updated');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Ошибка при обновлении новости: ' . $e->getMessage();
        }
    }
}

// Получаем категории
$categories = $categoryModel->getAllCategories();
$current_admin = getCurrentAdmin();

// Проверяем сообщения
$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'updated':
            $success_message = 'Новость успешно обновлена!';
            break;
        case 'created':
            $success_message = 'Новость успешно создана!';
            break;
    }
}

// Заполняем данные формы
$form_data = $_POST ? $_POST : [
    'category_id' => $news['category_id'],
    'title' => $news['title'],
    'h1' => $news['h1'],
    'slug' => $news['slug'],
    'meta_description' => $news['meta_description'],
    'excerpt' => $news['excerpt'],
    'content' => $news['content'],
    'featured_image' => $news['featured_image'],
    'is_published' => $news['is_published'],
    'is_featured' => $news['is_featured'],
    'published_at' => $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : ''
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать новость | Админ-панель</title>
    <link rel="stylesheet" href="/admin/admin-styles.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Боковая панель -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="3" width="12" height="16" rx="1" fill="white" opacity="0.9"/>
                        <line x1="6" y1="7" x2="14" y2="7" stroke="#667eea" stroke-width="0.8"/>
                        <line x1="6" y1="9" x2="14" y2="9" stroke="#667eea" stroke-width="0.8"/>
                        <line x1="6" y1="11" x2="14" y2="11" stroke="#667eea" stroke-width="0.8"/>
                        <line x1="6" y1="13" x2="14" y2="13" stroke="#667eea" stroke-width="0.8"/>
                        <circle cx="18" cy="6" r="3" fill="#27ae60"/>
                        <path d="M16.5 6l1 1 2-2" stroke="white" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <span>Админ-панель</span>
            </div>

            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/">📊 Dashboard</a></li>
                    <li><a href="/admin/news/" class="active">📰 Новости</a></li>
                    <li><a href="/admin/categories/">📁 Категории</a></li>
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
                <h1>Редактировать новость #<?php echo $news['id']; ?></h1>
                <div class="admin-actions">
                    <a href="/admin/news/" class="btn btn-secondary">← Назад к списку</a>
                    <a href="/news/<?php echo htmlspecialchars($news['slug']); ?>" target="_blank" class="btn btn-secondary">👁️ Просмотр</a>
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

                <!-- Информация о новости -->
                <div class="news-info">
                    <h4>Информация о новости</h4>
                    <div class="news-meta-info">
                        <div><strong>ID:</strong> <?php echo $news['id']; ?></div>
                        <div><strong>Создана:</strong> <?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?></div>
                        <div><strong>Обновлена:</strong> <?php echo date('d.m.Y H:i', strtotime($news['updated_at'])); ?></div>
                        <div><strong>Просмотры:</strong> <?php echo number_format($news['views_count']); ?></div>
                        <div><strong>Статус:</strong> 
                            <span style="color: <?php echo $news['is_published'] ? '#28a745' : '#dc3545'; ?>">
                                <?php echo $news['is_published'] ? '✅ Опубликована' : '❌ Черновик'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <form method="POST" class="form-row" id="newsForm">
                    <!-- Основная форма -->
                    <div class="news-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title">Заголовок *</label>
                                <input type="text" id="title" name="title" required 
                                       value="<?php echo htmlspecialchars($form_data['title']); ?>"
                                       placeholder="Введите заголовок новости">
                                <div class="form-help">Основной заголовок новости</div>
                            </div>

                            <div class="form-group">
                                <label for="h1">H1 заголовок</label>
                                <input type="text" id="h1" name="h1" 
                                       value="<?php echo htmlspecialchars($form_data['h1']); ?>"
                                       placeholder="H1 для страницы">
                                <div class="form-help">Заголовок H1 на странице статьи</div>
                            </div>

                            <div class="form-group">
                                <label for="slug">URL (slug)</label>
                                <input type="text" id="slug" name="slug" 
                                       value="<?php echo htmlspecialchars($form_data['slug']); ?>"
                                       placeholder="url-novosti">
                                <div class="form-help">URL адрес новости</div>
                            </div>

                            <div class="form-group">
                                <label for="excerpt">Краткое описание</label>
                                <textarea id="excerpt" name="excerpt" rows="3" 
                                          placeholder="Краткое описание новости"><?php echo htmlspecialchars($form_data['excerpt']); ?></textarea>
                                <div class="form-help">Отображается в списке новостей</div>
                            </div>

                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" rows="2" maxlength="160"
                                          placeholder="Описание для поисковых систем"><?php echo htmlspecialchars($form_data['meta_description']); ?></textarea>
                                <div class="form-help">SEO описание для поисковых систем</div>
                            </div>

                            <!-- НОВЫЙ РЕДАКТОР С HTML РЕЖИМОМ -->
                            <div class="form-group">
                                <label for="content">Содержимое *</label>
                                <div class="editor-container">
                                    <!-- Панель инструментов -->
                                    <div class="editor-toolbar">
                                        <div class="toolbar-section">
                                            <button type="button" onclick="toggleEditorMode()" class="html-toggle-btn" id="htmlToggleBtn">
                                                📝 HTML режим
                                            </button>
                                            <button type="button" onclick="showPreview()" class="preview-btn">
                                                👁️ Предпросмотр
                                            </button>
                                        </div>
                                        <div class="toolbar-section" id="visualToolbar">
                                            <button type="button" onclick="formatHeading('h1')" title="Заголовок H1" class="heading-btn">H1</button>
                                            <button type="button" onclick="formatHeading('h2')" title="Заголовок H2" class="heading-btn">H2</button>
                                            <button type="button" onclick="formatHeading('h3')" title="Заголовок H3" class="heading-btn">H3</button>
                                            <span class="toolbar-separator">|</span>
                                            <button type="button" onclick="formatText('bold')" title="Жирный"><b>B</b></button>
                                            <button type="button" onclick="formatText('italic')" title="Курсив"><i>I</i></button>
                                            <span class="toolbar-separator">|</span>
                                            <button type="button" onclick="formatText('insertUnorderedList')" title="Список">•</button>
                                            <button type="button" onclick="formatText('insertOrderedList')" title="Нумерованный список">1.</button>
                                            <span class="toolbar-separator">|</span>
                                            <button type="button" onclick="insertLink()" title="Ссылка">🔗</button>
                                            <button type="button" onclick="insertImage()" title="Изображение">🖼️</button>
                                            <span class="toolbar-separator">|</span>
                                            <button type="button" onclick="formatText('removeFormat')" title="Очистить форматирование">🧹</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Редактор -->
                                    <div class="editor-wrapper">
                                        <div id="visualEditor" class="visual-editor" contenteditable="true"><?php echo $form_data['content']; ?></div>
                                        <textarea id="content" name="content" class="html-editor" style="display: none;"><?php echo htmlspecialchars($form_data['content']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Боковая панель -->
                    <div class="form-sidebar">
                        <!-- Публикация -->
                        <div class="sidebar-section">
                            <h3>Публикация</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_published" name="is_published" value="1"
                                       <?php echo $form_data['is_published'] ? 'checked' : ''; ?>>
                                <label for="is_published">Опубликовать</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                       <?php echo $form_data['is_featured'] ? 'checked' : ''; ?>>
                                <label for="is_featured">Рекомендуемая</label>
                            </div>

                            <div class="form-group">
                                <label for="published_at">Дата публикации</label>
                                <input type="datetime-local" id="published_at" name="published_at" 
                                       value="<?php echo htmlspecialchars($form_data['published_at']); ?>">
                            </div>
                        </div>

                        <!-- Категория -->
                        <div class="sidebar-section">
                            <h3>Категория</h3>
                            <div class="form-group">
                                <select name="category_id">
                                    <option value="">Без категории</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                                <?php echo ($form_data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Изображение -->
                        <div class="sidebar-section">
                            <h3>Главное изображение</h3>
                            <div class="image-upload <?php echo $form_data['featured_image'] ? 'has-image' : ''; ?>" onclick="document.getElementById('image_input').click()">
                                <div id="image_preview">
                                    <?php if ($form_data['featured_image']): ?>
                                        <img src="<?php echo htmlspecialchars($form_data['featured_image']); ?>" class="image-preview" alt="Текущее изображение">
                                        <button type="button" class="remove-image-btn" onclick="removeImage(event)">🗑️ Удалить</button>
                                    <?php else: ?>
                                        <div class="upload-icon">📸</div>
                                        <div class="upload-hint">Нажмите для выбора изображения</div>
                                        <div class="upload-formats">JPG, PNG, GIF, WebP до 5MB</div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="image_input" accept="image/*" style="display: none;">
                            </div>
                            
                            <div class="form-group" style="margin-top: 10px;">
                                <input type="text" name="featured_image" id="featured_image" 
                                       placeholder="Или введите URL изображения"
                                       value="<?php echo htmlspecialchars($form_data['featured_image']); ?>">
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="form-actions">
                            <button type="submit" name="action" value="save" class="btn-save">
                                💾 Сохранить
                            </button>
                            <button type="submit" name="action" value="draft" class="btn-draft">
                                📝 Черновик
                            </button>
                            <button type="button" onclick="confirmDelete()" class="btn-danger">
                                🗑️ Удалить
                            </button>
                        </div>

                        <!-- Автосохранение статус -->
                        <div class="auto-save-status" id="autoSaveStatus">
                            <!-- Статус автосохранения -->
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <style>
    /* Стили для нового редактора */
    .editor-container {
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        overflow: hidden;
        background: white;
    }

    .editor-toolbar {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .toolbar-section {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .html-toggle-btn, .preview-btn {
        background: rgba(255,255,255,0.2) !important;
        color: white !important;
        border: 2px solid rgba(255,255,255,0.3) !important;
        padding: 8px 16px !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        transition: all 0.3s ease !important;
    }

    .html-toggle-btn:hover, .preview-btn:hover {
        background: rgba(255,255,255,0.3) !important;
        transform: translateY(-1px) !important;
    }

    .html-toggle-btn.active {
        background: rgba(40,167,69,0.8) !important;
        border-color: rgba(40,167,69,1) !important;
    }

    #visualToolbar button {
        background: rgba(255,255,255,0.1) !important;
        color: white !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        padding: 6px 10px !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        font-weight: bold !important;
        transition: all 0.2s ease !important;
        min-width: 32px !important;
        height: 32px !important;
        margin: 0 2px !important;
    }

    #visualToolbar button:hover {
        background: rgba(255,255,255,0.2) !important;
        transform: translateY(-1px) !important;
    }

    .heading-btn {
        font-size: 12px !important;
        font-weight: 700 !important;
        background: rgba(52,152,219,0.2) !important;
        border-color: rgba(52,152,219,0.3) !important;
    }

    .heading-btn:hover {
        background: rgba(52,152,219,0.3) !important;
    }

    .toolbar-separator {
        color: rgba(255,255,255,0.4) !important;
        margin: 0 8px !important;
        font-weight: 300 !important;
        font-size: 16px !important;
    }

    .editor-wrapper {
        position: relative;
        min-height: 400px;
    }

    .visual-editor {
        padding: 20px;
        min-height: 400px;
        max-height: 600px;
        overflow-y: auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        border: none;
        outline: none;
        background: white;
    }

    .visual-editor:focus {
        background: #fdfdfd;
        box-shadow: inset 0 0 10px rgba(102, 126, 234, 0.1);
    }

    .html-editor {
        padding: 20px !important;
        min-height: 400px !important;
        font-family: 'Monaco', 'Menlo', 'Consolas', monospace !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        background: #1e1e1e !important;
        color: #e6e6e6 !important;
        border: none !important;
        outline: none !important;
        resize: vertical !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .visual-editor h1 {
        font-size: 2rem !important;
        font-weight: 700 !important;
        color: #2c3e50 !important;
        margin-top: 1.5em !important;
        margin-bottom: 0.5em !important;
        line-height: 1.3 !important;
        border-bottom: 2px solid #667eea !important;
        padding-bottom: 0.3em !important;
    }

    .visual-editor h2 {
        font-size: 1.7rem !important;
        font-weight: 600 !important;
        color: #34495e !important;
        margin-top: 1.4em !important;
        margin-bottom: 0.5em !important;
        line-height: 1.3 !important;
        border-bottom: 1px solid #bdc3c7 !important;
        padding-bottom: 0.2em !important;
    }

    .visual-editor h3 {
        font-size: 1.4rem !important;
        font-weight: 600 !important;
        color: #34495e !important;
        margin-top: 1.3em !important;
        margin-bottom: 0.5em !important;
        line-height: 1.3 !important;
    }

    .visual-editor h1, .visual-editor h2, .visual-editor h3 {
        margin-top: 1.5em;
        margin-bottom: 0.5em;
        font-weight: 600;
        line-height: 1.3;
    }

    .visual-editor p {
        margin: 0.8em 0;
    }

    .visual-editor ul, .visual-editor ol {
        padding-left: 2em;
        margin: 1em 0;
    }

    .visual-editor blockquote {
        border-left: 4px solid #667eea;
        padding-left: 1em;
        margin: 1em 0;
        font-style: italic;
        background: #f8f9fa;
        padding: 0.5em 1em;
        border-radius: 0 4px 4px 0;
    }

    .visual-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
        margin: 1em 0;
    }

    .visual-editor a {
        color: #667eea;
        text-decoration: underline;
    }

    /* Стили для изображений */
    .image-upload {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image-upload:hover {
        border-color: #667eea;
        background: #f8f9fa;
    }

    .image-upload.has-image {
        border-style: solid;
        padding: 10px;
    }

    .image-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        display: block;
        margin: 0 auto;
    }

    .remove-image-btn {
        display: block;
        margin: 10px auto 0;
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .upload-icon {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .upload-hint, .upload-formats {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
    }

    .auto-save-status {
        margin-top: 10px;
        font-size: 12px;
        color: #666;
        text-align: center;
    }

    @media (max-width: 768px) {
        .editor-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .toolbar-section {
            justify-content: center;
        }
        
        #visualToolbar {
            order: -1;
        }
    }
    </style>

    <script>
    let isHTMLMode = false;
    let autoSaveInterval;

    // Переключение режимов редактора
    function toggleEditorMode() {
        const visualEditor = document.getElementById('visualEditor');
        const htmlEditor = document.getElementById('content');
        const toggleBtn = document.getElementById('htmlToggleBtn');
        const visualToolbar = document.getElementById('visualToolbar');

        if (!isHTMLMode) {
            // Переход в HTML режим
            const htmlContent = visualEditor.innerHTML;
            htmlEditor.value = htmlContent;
            htmlEditor.style.display = 'block';
            visualEditor.style.display = 'none';
            visualToolbar.style.display = 'none';
            toggleBtn.textContent = '📝 Визуальный режим';
            toggleBtn.classList.add('active');
            isHTMLMode = true;
            showNotification('HTML режим активирован', 'success');
        } else {
            // Переход в визуальный режим
            const htmlContent = htmlEditor.value;
            visualEditor.innerHTML = htmlContent;
            visualEditor.style.display = 'block';
            htmlEditor.style.display = 'none';
            visualToolbar.style.display = 'flex';
            toggleBtn.textContent = '📝 HTML режим';
            toggleBtn.classList.remove('active');
            isHTMLMode = false;
            showNotification('Визуальный режим активирован', 'success');
        }
    }

    // Форматирование заголовков
    function formatHeading(tag) {
        if (isHTMLMode) return;
        
        const selection = window.getSelection();
        if (selection.rangeCount === 0) return;
        
        // Получаем выделенный текст или текущую строку
        let range = selection.getRangeAt(0);
        let selectedText = range.toString();
        
        // Если ничего не выделено, выделяем текущую строку
        if (!selectedText) {
            // Расширяем выделение до границ строки
            range.selectNode(range.startContainer);
            if (range.startContainer.nodeType === Node.TEXT_NODE) {
                // Если это текстовый узел, выделяем родительский элемент
                const parent = range.startContainer.parentElement;
                if (parent && parent !== document.getElementById('visualEditor')) {
                    range.selectNode(parent);
                }
            }
            selectedText = range.toString() || 'Новый заголовок';
        }
        
        // Создаем новый заголовок
        const heading = document.createElement(tag);
        heading.textContent = selectedText || 'Новый заголовок';
        
        // Применяем стили заголовка
        switch(tag) {
            case 'h1':
                heading.style.fontSize = '2rem';
                heading.style.fontWeight = '700';
                heading.style.color = '#2c3e50';
                heading.style.marginTop = '1.5em';
                heading.style.marginBottom = '0.5em';
                heading.style.lineHeight = '1.3';
                break;
            case 'h2':
                heading.style.fontSize = '1.7rem';
                heading.style.fontWeight = '600';
                heading.style.color = '#34495e';
                heading.style.marginTop = '1.4em';
                heading.style.marginBottom = '0.5em';
                heading.style.lineHeight = '1.3';
                break;
            case 'h3':
                heading.style.fontSize = '1.4rem';
                heading.style.fontWeight = '600';
                heading.style.color = '#34495e';
                heading.style.marginTop = '1.3em';
                heading.style.marginBottom = '0.5em';
                heading.style.lineHeight = '1.3';
                break;
        }
        
        try {
            // Удаляем выделенный контент
            range.deleteContents();
            
            // Вставляем заголовок
            range.insertNode(heading);
            
            // Добавляем перенос строки после заголовка
            const br = document.createElement('br');
            range.setStartAfter(heading);
            range.insertNode(br);
            
            // Устанавливаем курсор после заголовка
            range.setStartAfter(br);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            
            showNotification(`Заголовок ${tag.toUpperCase()} добавлен`, 'success');
            
        } catch (error) {
            console.error('Ошибка создания заголовка:', error);
            
            // Альтернативный способ через execCommand
            document.execCommand('formatBlock', false, tag);
            showNotification(`Заголовок ${tag.toUpperCase()} добавлен`, 'success');
        }
        
        document.getElementById('visualEditor').focus();
    }

    // Форматирование текста
    function formatText(command, value = null) {
        if (isHTMLMode) return;
        
        try {
            document.execCommand(command, false, value);
            document.getElementById('visualEditor').focus();
            
            // Показываем уведомление для некоторых команд
            const notifications = {
                'bold': 'Текст выделен жирным',
                'italic': 'Текст выделен курсивом',
                'insertUnorderedList': 'Маркированный список создан',
                'insertOrderedList': 'Нумерованный список создан',
                'removeFormat': 'Форматирование очищено'
            };
            
            if (notifications[command]) {
                showNotification(notifications[command], 'info');
            }
        } catch (error) {
            console.error('Ошибка форматирования:', error);
            showNotification('Ошибка форматирования текста', 'error');
        }
    }

    // Вставка ссылки
    function insertLink() {
        if (isHTMLMode) return;
        
        const url = prompt('Введите URL ссылки:');
        if (url) {
            document.execCommand('createLink', false, url);
        }
    }

    // Вставка изображения
    function insertImage() {
        if (isHTMLMode) return;
        
        const url = prompt('Введите URL изображения:');
        if (url) {
            document.execCommand('insertImage', false, url);
        }
    }

    // Предпросмотр
    function showPreview() {
        let content = '';
        const title = document.getElementById('title').value || 'Предпросмотр';
        
        if (isHTMLMode) {
            content = document.getElementById('content').value;
        } else {
            content = document.getElementById('visualEditor').innerHTML;
        }
        
        const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <title>Предпросмотр: ${title}</title>
                <style>
                    body { 
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                        max-width: 800px; margin: 20px auto; padding: 20px; line-height: 1.6; 
                        background: white; color: #333;
                    }
                    img { max-width: 100%; height: auto; border-radius: 8px; }
                    table { border-collapse: collapse; width: 100%; margin: 1em 0; }
                    td, th { border: 1px solid #ddd; padding: 8px; }
                    th { background: #f2f2f2; font-weight: 600; }
                    blockquote { 
                        border-left: 4px solid #667eea; padding-left: 1em; margin: 1em 0; 
                        background: #f8f9fa; padding: 0.5em 1em; border-radius: 0 4px 4px 0;
                    }
                    h1, h2, h3, h4, h5, h6 { margin-top: 1.5em; margin-bottom: 0.5em; color: #2c3e50; }
                    p { margin: 0.8em 0; }
                    code { background: #f1f3f4; padding: 2px 4px; border-radius: 3px; }
                    pre { background: #f8f9fa; padding: 1em; border-radius: 6px; overflow-x: auto; }
                    .preview-header { 
                        background: #667eea; color: white; padding: 10px 20px; 
                        margin: -20px -20px 20px -20px; border-radius: 0;
                        text-align: center; font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class="preview-header">📖 Предпросмотр статьи</div>
                <h1>${title}</h1>
                ${content || '<p><em>Содержимое пустое</em></p>'}
            </body>
            </html>
        `);
        previewWindow.document.close();
        
        showNotification('Предпросмотр открыт в новом окне', 'success');
    }

    // Синхронизация контента перед отправкой формы
    function syncContent() {
        if (isHTMLMode) {
            // В HTML режиме контент уже в textarea
            return;
        } else {
            // В визуальном режиме копируем из contenteditable в textarea
            const visualContent = document.getElementById('visualEditor').innerHTML;
            document.getElementById('content').value = visualContent;
        }
    }

    // Автосохранение
    function setupAutoSave() {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval);
        }
        
        autoSaveInterval = setInterval(() => {
            saveAsDraft();
        }, 30000);
        
        console.log('✅ Автосохранение настроено (каждые 30 сек)');
    }

    function saveAsDraft() {
        syncContent();
        
        const formData = new FormData();
        formData.append('auto_save', '1');
        formData.append('title', document.getElementById('title').value);
        formData.append('h1', document.getElementById('h1').value);
        formData.append('slug', document.getElementById('slug').value);
        formData.append('meta_description', document.getElementById('meta_description').value);
        formData.append('excerpt', document.getElementById('excerpt').value);
        formData.append('content', document.getElementById('content').value);
        formData.append('featured_image', document.getElementById('featured_image').value);
        formData.append('category_id', document.querySelector('select[name="category_id"]').value);
        formData.append('published_at', document.getElementById('published_at').value);

        updateAutoSaveStatus('Сохранение...');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAutoSaveStatus('✅ Автосохранено в ' + new Date().toLocaleTimeString());
            } else {
                updateAutoSaveStatus('❌ Ошибка сохранения');
                console.error('Ошибка автосохранения:', data.error);
            }
        })
        .catch(error => {
            updateAutoSaveStatus('❌ Ошибка сохранения');
            console.error('Ошибка автосохранения:', error);
        });
    }

    function updateAutoSaveStatus(message) {
        const statusElement = document.getElementById('autoSaveStatus');
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    // Работа с изображениями
    function removeImage(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        
        document.getElementById('featured_image').value = '';
        updateImagePreview();
        showNotification('Изображение удалено', 'info');
    }

    function updateImagePreview() {
        const imageUrl = document.getElementById('featured_image').value;
        const preview = document.getElementById('image_preview');
        const uploadDiv = preview.closest('.image-upload');
        
        if (imageUrl && imageUrl.trim()) {
            preview.innerHTML = `
                <img src="${imageUrl}" class="image-preview" alt="Изображение" onload="showNotification('Изображение загружено', 'success')" onerror="showNotification('Ошибка загрузки изображения', 'error')">
                <button type="button" class="remove-image-btn" onclick="removeImage(event)">🗑️ Удалить</button>
            `;
            uploadDiv.classList.add('has-image');
        } else {
            preview.innerHTML = `
                <div class="upload-icon">📸</div>
                <div class="upload-hint">Нажмите для выбора изображения</div>
                <div class="upload-formats">JPG, PNG, GIF, WebP до 5MB</div>
            `;
            uploadDiv.classList.remove('has-image');
        }
    }

    // Генерация slug
    function generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[а-я]/g, char => {
                const map = {
                    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
                    'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
                    'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
                    'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
                    'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
                };
                return map[char] || char;
            })
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // Уведомления
    function showNotification(message, type = 'info') {
        const existing = document.querySelectorAll('.notification');
        existing.forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
            color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
            border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
            padding: 12px 20px; border-radius: 8px; max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
            font-size: 14px; line-height: 1.4;
        `;
        notification.textContent = message;

        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            float: right; margin-left: 10px; cursor: pointer; 
            font-size: 16px; font-weight: bold; opacity: 0.7;
        `;
        closeBtn.onclick = () => notification.remove();
        notification.appendChild(closeBtn);

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    // Удаление новости
    function confirmDelete() {
        if (confirm('Вы уверены, что хотите удалить эту новость?\n\nЭто действие нельзя отменить!')) {
            const deleteBtn = event.target;
            deleteBtn.disabled = true;
            deleteBtn.textContent = '🗑️ Удаление...';
            
            window.location.href = `/admin/news/delete.php?id=<?php echo $news['id']; ?>`;
        }
    }

    // Горячие клавиши
    function setupHotkeys() {
        document.addEventListener('keydown', function(e) {
            // Ctrl+Shift+H - переключение HTML режима
            if (e.ctrlKey && e.shiftKey && e.key === 'H') {
                e.preventDefault();
                toggleEditorMode();
            }
            
            // Ctrl+S - сохранение
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveAsDraft();
            }
            
            // Ctrl+Shift+P - предпросмотр
            if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                showPreview();
            }
            
            // Ctrl+1/2/3 - заголовки (только в визуальном режиме)
            if (e.ctrlKey && !isHTMLMode && document.activeElement === document.getElementById('visualEditor')) {
                if (e.key === '1') {
                    e.preventDefault();
                    formatHeading('h1');
                } else if (e.key === '2') {
                    e.preventDefault();
                    formatHeading('h2');
                } else if (e.key === '3') {
                    e.preventDefault();
                    formatHeading('h3');
                }
            }
        });
        
        console.log('✅ Горячие клавиши настроены:');
        console.log('   Ctrl+Shift+H - HTML режим');
        console.log('   Ctrl+S - Автосохранение');
        console.log('   Ctrl+Shift+P - Предпросмотр');
        console.log('   Ctrl+1/2/3 - Заголовки H1/H2/H3');
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Страница редактирования загружена');
        
        // Настройка обработчиков
        const form = document.getElementById('newsForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                syncContent();
                
                const title = document.getElementById('title').value.trim();
                const content = document.getElementById('content').value.trim();

                if (!title) {
                    e.preventDefault();
                    showNotification('Заголовок обязателен для заполнения', 'error');
                    document.getElementById('title').focus();
                    return false;
                }

                if (!content) {
                    e.preventDefault();
                    showNotification('Содержимое статьи обязательно', 'error');
                    return false;
                }

                const submitButtons = form.querySelectorAll('button[type="submit"]');
                submitButtons.forEach(btn => {
                    btn.disabled = true;
                    const originalText = btn.textContent;
                    btn.textContent = '⏳ Сохранение...';
                    
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }, 10000);
                });
            });
        }

        // Обработка загрузки файла
        const imageInput = document.getElementById('image_input');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    showNotification('Выберите изображение', 'error');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showNotification('Размер файла не должен превышать 5MB', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                showNotification('Загрузка изображения...', 'info');

                fetch('/admin/upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('featured_image').value = data.file_url;
                        updateImagePreview();
                        showNotification('Изображение загружено!', 'success');
                    } else {
                        showNotification('Ошибка: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Ошибка загрузки:', error);
                    showNotification('Ошибка загрузки изображения', 'error');
                });
            });
        }

        // Обновление превью при изменении URL
        const featuredImageInput = document.getElementById('featured_image');
        if (featuredImageInput) {
            featuredImageInput.addEventListener('input', updateImagePreview);
        }

        // Генерация slug
        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                const title = this.value;
                const slugField = document.getElementById('slug');
                
                if (slugField && !slugField.value.trim()) {
                    const slug = generateSlug(title);
                    slugField.value = slug;
                }
            });
        }

        // Инициализация других компонентов
        updateImagePreview();
        setupAutoSave();
        setupHotkeys();
        
        // Показываем уведомление о готовности
        setTimeout(() => {
            showNotification('Редактор готов! 📝 HTML режим | H1/H2/H3 кнопки | Горячие клавиши: Ctrl+1/2/3', 'success');
        }, 1000);
    });

    // Добавляем стили для анимации
    const styles = document.createElement('style');
    styles.innerHTML = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(styles);

    console.log('✅ Скрипт редактора загружен. HTML режим доступен!');
    </script>
</body>
</html>