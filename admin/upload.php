<?php
/**
 * Обработчик загрузки файлов для админки
 * Файл: /admin/upload.php
 * Поддерживает как обычную загрузку, так и загрузку через CKEditor
 */

require_once __DIR__ . '/../models/AdminAuth.php';

// Проверяем авторизацию
requireAuth();

// Настройки загрузки
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'image/gif',
    'image/webp'
]);

header('Content-Type: application/json; charset=utf-8');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен'
    ]);
    exit;
}

// Определяем тип загрузки (CKEditor или обычная)
$is_ckeditor = isset($_GET['type']) && $_GET['type'] === 'ckeditor';

// Получаем файл (CKEditor использует поле 'upload', обычная загрузка - 'file')
$file_field = $is_ckeditor ? 'upload' : 'file';

if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер',
        UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер формы',
        UPLOAD_ERR_PARTIAL => 'Файл загружен частично',
        UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл',
        UPLOAD_ERR_EXTENSION => 'Загрузка остановлена расширением'
    ];
    
    $error = $error_messages[$_FILES[$file_field]['error']] ?? 'Неизвестная ошибка загрузки';
    
    if ($is_ckeditor) {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => $error]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    exit;
}

$file = $_FILES[$file_field];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_type = $file['type'];

// Проверяем размер файла
if ($file_size > MAX_FILE_SIZE) {
    $error = 'Файл слишком большой. Максимальный размер: ' . formatBytes(MAX_FILE_SIZE);
    
    if ($is_ckeditor) {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => $error]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    exit;
}

// Получаем расширение файла
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Проверяем расширение
if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
    $error = 'Недопустимый тип файла. Разрешены: ' . implode(', ', ALLOWED_EXTENSIONS);
    
    if ($is_ckeditor) {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => $error]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    exit;
}

// Проверяем MIME-тип
if (!in_array($file_type, ALLOWED_MIME_TYPES)) {
    $error = 'Недопустимый MIME-тип файла';
    
    if ($is_ckeditor) {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => $error]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    exit;
}

// Проверяем, что это действительно изображение
$image_info = getimagesize($file_tmp);
if ($image_info === false) {
    $error = 'Файл не является изображением';
    
    if ($is_ckeditor) {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => $error]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    exit;
}

// Создаем папку uploads если её нет
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        $error = 'Не удалось создать папку для загрузки';
        
        if ($is_ckeditor) {
            echo json_encode([
                'uploaded' => false,
                'error' => ['message' => $error]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $error
            ]);
        }
        exit;
    }
}

// Создаем подпапки по годам и месяцам
$year = date('Y');
$month = date('m');
$upload_subdir = UPLOAD_DIR . $year . '/' . $month . '/';
$upload_url_subdir = UPLOAD_URL . $year . '/' . $month . '/';

if (!is_dir($upload_subdir)) {
    if (!mkdir($upload_subdir, 0755, true)) {
        $error = 'Не удалось создать папку для загрузки';
        
        if ($is_ckeditor) {
            echo json_encode([
                'uploaded' => false,
                'error' => ['message' => $error]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $error
            ]);
        }
        exit;
    }
}

// Генерируем уникальное имя файла
$file_base_name = pathinfo($file_name, PATHINFO_FILENAME);
$file_base_name = sanitizeFileName($file_base_name);
$new_file_name = $file_base_name . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_extension;
$file_path = $upload_subdir . $new_file_name;
$file_url = $upload_url_subdir . $new_file_name;

// Загружаем файл
if (!move_uploaded_file($file_tmp, $file_path)) {
    $error = 'Не удалось сохранить файл';
    
    if ($is_ckeditor) {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => $error]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    exit;
}

// Создаем миниатюру (опционально)
$thumbnail_url = null;
if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $thumbnail_url = createThumbnail($file_path, $upload_subdir, $upload_url_subdir, $new_file_name);
}

// Получаем информацию об изображении
$image_width = $image_info[0];
$image_height = $image_info[1];

// Логируем загрузку
$current_admin = getCurrentAdmin();
$log_entry = date('Y-m-d H:i:s') . " | Admin ID: " . $current_admin['id'] . " | File: " . $new_file_name . " | Size: " . formatBytes($file_size) . " | Type: " . ($is_ckeditor ? 'CKEditor' : 'Manual') . "\n";

// Создаем папку logs если её нет
if (!is_dir(__DIR__ . '/../logs/')) {
    mkdir(__DIR__ . '/../logs/', 0755, true);
}

file_put_contents(__DIR__ . '/../logs/uploads.log', $log_entry, FILE_APPEND | LOCK_EX);

// Формируем полный URL с доменом
$full_file_url = 'https://' . $_SERVER['HTTP_HOST'] . $file_url;

// Возвращаем результат в зависимости от типа загрузки
if ($is_ckeditor) {
    // Ответ для CKEditor
    echo json_encode([
        'uploaded' => true,
        'url' => $full_file_url
    ]);
} else {
    // Обычный ответ
    echo json_encode([
        'success' => true,
        'file_url' => $file_url,
        'full_file_url' => $full_file_url,
        'thumbnail_url' => $thumbnail_url,
        'file_name' => $new_file_name,
        'file_size' => $file_size,
        'file_size_formatted' => formatBytes($file_size),
        'dimensions' => [
            'width' => $image_width,
            'height' => $image_height
        ]
    ]);
}

/**
 * Очистка имени файла от недопустимых символов
 */
function sanitizeFileName($filename) {
    // Транслитерация русских символов
    $transliteration = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    $filename = mb_strtolower($filename, 'UTF-8');
    $filename = strtr($filename, $transliteration);
    
    // Удаляем недопустимые символы
    $filename = preg_replace('/[^a-z0-9\-_]/', '-', $filename);
    $filename = preg_replace('/\-+/', '-', $filename);
    $filename = trim($filename, '-');
    
    // Ограничиваем длину
    if (strlen($filename) > 50) {
        $filename = substr($filename, 0, 50);
    }
    
    return $filename ?: 'image';
}

/**
 * Создание миниатюры изображения
 */
function createThumbnail($source_path, $upload_dir, $upload_url, $filename) {
    $thumb_width = 300;
    $thumb_height = 200;
    
    $pathinfo = pathinfo($filename);
    $thumb_filename = $pathinfo['filename'] . '_thumb.' . $pathinfo['extension'];
    $thumb_path = $upload_dir . $thumb_filename;
    $thumb_url = $upload_url . $thumb_filename;
    
    // Получаем информацию об изображении
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return null;
    }
    
    list($orig_width, $orig_height, $image_type) = $image_info;
    
    // Если изображение меньше чем нужная миниатюра, не создаем её
    if ($orig_width <= $thumb_width && $orig_height <= $thumb_height) {
        return null;
    }
    
    // Вычисляем пропорции
    $ratio = min($thumb_width / $orig_width, $thumb_height / $orig_height);
    $new_width = round($orig_width * $ratio);
    $new_height = round($orig_height * $ratio);
    
    // Создаем изображение в зависимости от типа
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP:
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            return null;
    }
    
    if (!$source_image) {
        return null;
    }
    
    // Создаем миниатюру
    $thumb_image = imagecreatetruecolor($new_width, $new_height);
    
    // Сохраняем прозрачность для PNG и GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($thumb_image, false);
        imagesavealpha($thumb_image, true);
        $transparent = imagecolorallocatealpha($thumb_image, 255, 255, 255, 127);
        imagefill($thumb_image, 0, 0, $transparent);
    }
    
    // Изменяем размер
    imagecopyresampled($thumb_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    
    // Сохраняем миниатюру
    $success = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($thumb_image, $thumb_path, 85);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($thumb_image, $thumb_path, 6);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($thumb_image, $thumb_path);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($thumb_image, $thumb_path, 85);
            break;
    }
    
    // Освобождаем память
    imagedestroy($source_image);
    imagedestroy($thumb_image);
    
    return $success ? $thumb_url : null;
}

/**
 * Форматирование размера файла
 */
function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>