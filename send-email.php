<?php
// Отключаем отображение ошибок для продакшена (включите для отладки)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Устанавливаем заголовки для CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Определяем формат ответа (JSON для fetch/AJAX, HTML для обычного submit)
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$wantsJson = (stripos($accept, 'application/json') !== false) || (stripos($contentType, 'application/json') !== false) || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
} else {
    header('Content-Type: text/html; charset=utf-8');
}

function respond($ok, $message, $extra = [], $statusCode = 200) {
    global $wantsJson;
    http_response_code($statusCode);
    if ($wantsJson) {
        echo json_encode(array_merge(['success' => $ok], $extra, $ok ? ['message' => $message] : ['error' => $message]), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $title = $ok ? 'Заявка принята' : 'Ошибка отправки';
    $body = $ok
        ? '✅ Заявка принята! Мы свяжемся с вами в течение 30 минут в рабочее время.'
        : ('❌ Ошибка отправки: ' . htmlspecialchars($message));
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $title . '</title></head><body style="font-family:Arial,sans-serif;padding:24px;">';
    echo '<h2>' . $title . '</h2><p>' . $body . '</p><p><a href="' . htmlspecialchars($back) . '">Вернуться назад</a></p>';
    echo '</body></html>';
    exit;
}

// Обрабатываем preflight запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Проверяем метод запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    respond(false, 'Method not allowed', ['debug' => 'Только POST запросы разрешены'], 405);
}

// Получаем данные (поддержка JSON и обычного POST)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$input_data = file_get_contents('php://input');
$input = null;

if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode($input_data, true);
} else {
    // Классический POST (form submit)
    $input = $_POST;
    $input_data = json_encode($input, JSON_UNESCAPED_UNICODE);
}

// Генерируем уникальный ID для этой заявки
$request_id = uniqid('lead_', true);

// Дополнительное логирование в файл для отладки
$logfile = sys_get_temp_dir() . '/send-email.log';
$logLine = sprintf(
    "[%s] %s | ip=%s | ua=%s | referer=%s | page_url=%s | raw=%s\n",
    date('Y-m-d H:i:s'),
    $request_id,
    $_SERVER['REMOTE_ADDR'] ?? '-',
    $_SERVER['HTTP_USER_AGENT'] ?? '-',
    $_SERVER['HTTP_REFERER'] ?? '-',
    $input['page_url'] ?? '-',
    substr($input_data, 0, 500)
);
@file_put_contents($logfile, $logLine, FILE_APPEND);

// Генерируем уникальный ID для этой заявки
$request_id = uniqid('lead_', true);

// Логирование для отладки (закомментируйте в продакшене)
error_log("=== EMAIL DEBUG [$request_id] ===");
error_log("Received data: " . $input_data);
error_log("Parsed data: " . print_r($input, true));

// Проверяем, что данные получены
if (!$input || !is_array($input)) {
    respond(false, 'No data received', ['debug' => 'Данные не получены или неверный JSON'], 400);
}

// Обязательные поля: имя, и хотя бы один из phone/email
$required_fields = ['name'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $missing_fields[] = $field;
    }
}

// Дополнительная проверка: телефон или email должен быть заполнен хотя бы один
if (empty($input['phone']) && empty($input['email'])) {
    $missing_fields[] = 'phone_or_email';
}

if (!empty($missing_fields)) {
    respond(false, 'Не заполнены обязательные поля: ' . implode(', ', $missing_fields), ['debug' => 'Нужно указать имя и хотя бы телефон или email'], 400);
}

// Очистка и валидация данных
$name = strip_tags(trim($input['name']));
$phone_raw = strip_tags(trim($input['phone'] ?? ''));
$phone_digits = preg_replace('/\D/', '', $phone_raw);
$phone = $phone_raw;
$email = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$company = strip_tags(trim($input['company'] ?? ''));
$message = strip_tags(trim($input['message'] ?? ''));
$service = strip_tags(trim($input['service'] ?? 'Консультация'));
// Получаем URL страницы откуда отправлена форма (исправление для правильного сохранения в БД)
$page_url = strip_tags(trim($input['page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
if (empty($page_url) || strpos($page_url, 'send-email') !== false) {
    // Если URL содержит send-email или пустой, используем referer
    $page_url = $_SERVER['HTTP_REFERER'] ?? 'https://vnesenie-v-reestr.ru/';
}

// Проверка email (если указан)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Укажите корректный email', ['debug' => 'Неверный формат email'], 400);
}

// Дополнительная валидация
if (strlen($name) < 2) {
    respond(false, 'Имя слишком короткое (минимум 2 символа)', ['debug' => 'Имя слишком короткое'], 400);
}

// Телефон: проверяем только если указан; считаем по количеству цифр (нормализация скобок, пробелов, дефисов)
if (!empty($phone_raw)) {
    if (strlen($phone_digits) < 10) {
        respond(false, 'Укажите номер телефона полностью (минимум 10 цифр)', ['debug' => 'Телефон слишком короткий'], 400);
    }
}

// Настройки письма
$to = "reestrgarant@mail.ru";
$subject = "=?UTF-8?B?" . base64_encode("Новая заявка с сайта: " . $service) . "?=";

// Формируем текст письма
$email_body = "Новая заявка с сайта vnesenie-v-reestr.ru\n\n";
$email_body .= "=== ДЕТАЛИ ЗАЯВКИ ===\n";
$email_body .= "Услуга: " . $service . "\n";
$email_body .= "Имя: " . $name . "\n";
$email_body .= "Телефон: " . $phone . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Компания: " . ($company ?: 'Не указана') . "\n";
$email_body .= "Сообщение: " . ($message ?: 'Не указано') . "\n\n";
$email_body .= "=== ТЕХНИЧЕСКАЯ ИНФОРМАЦИЯ ===\n";
$email_body .= "Дата отправки: " . date('d.m.Y H:i:s') . "\n";
$email_body .= "IP адрес: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестен') . "\n";
$email_body .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'неизвестен') . "\n";
$email_body .= "Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'неизвестен') . "\n";

// Заголовки письма
$headers = array();
$headers[] = "From: noreply@vnesenie-v-reestr.ru";
$headers[] = "Reply-To: " . ($email ?: 'noreply@vnesenie-v-reestr.ru');
$headers[] = "Content-Type: text/plain; charset=UTF-8";
$headers[] = "Content-Transfer-Encoding: 8bit";
$headers[] = "X-Mailer: PHP/" . phpversion();
$headers[] = "X-Priority: 1"; // Высокий приоритет
$headers[] = "Importance: High";

$headers_string = implode("\r\n", $headers);

// Логирование отправки
error_log("Attempting to send email...");
error_log("To: " . $to);
error_log("Subject: " . $subject);
error_log("Headers: " . $headers_string);

// Отправка письма с указанием envelope-from (некоторые почтовики требуют)
$mail_sent = mail($to, $subject, $email_body, $headers_string, '-f noreply@vnesenie-v-reestr.ru');

// Детальное логирование результата
error_log("Mail function result: " . ($mail_sent ? 'TRUE' : 'FALSE'));
$lastErr = error_get_last();
error_log("Error get last message: " . (is_array($lastErr) ? ($lastErr['message'] ?? 'no error') : 'no error'));

// Дополнительный лог в файл
$mailLog = sprintf(
    "[%s] %s | mail_sent=%s | to=%s | from=%s | reply=%s | subject=%s | err=%s\n",
    date('Y-m-d H:i:s'),
    $request_id,
    $mail_sent ? 'YES' : 'NO',
    $to,
    'noreply@vnesenie-v-reestr.ru',
    $email,
    $subject,
    is_array(error_get_last()) ? (error_get_last()['message'] ?? 'no error') : 'no error'
);
@file_put_contents($logfile, $mailLog, FILE_APPEND);

// Проверяем настройки PHP для почты
$smtp_settings = [
    'sendmail_path' => ini_get('sendmail_path'),
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'mail.log' => ini_get('mail.log')
];

error_log("PHP Mail settings: " . print_r($smtp_settings, true));

if ($mail_sent) {
    error_log("✅ EMAIL SUCCESS: Sent to " . $to . " from " . $email);
} else {
    $last_error = error_get_last();
    error_log("❌ EMAIL FAILED: " . ($last_error['message'] ?? 'Unknown error'));
}

// Сохраняем lead в базу данных ВСЕГДА (даже если письмо не отправилось)
try {
    error_log("🔄 [$request_id] Начинаем сохранение lead в базу данных...");
    error_log("🔄 [$request_id] Page URL для сохранения: " . $page_url);
    require_once __DIR__ . '/models/Lead.php';
    $input['page_url'] = $page_url;
    $leadSaved = saveLeadToDatabase($input, $service);
    if ($leadSaved) {
        error_log("✅ [$request_id] LEAD SAVED: Успешно сохранен в базу данных с URL: " . $page_url);
    } else {
        error_log("⚠️ [$request_id] LEAD SAVE FAILED: Не удалось сохранить в базу данных");
    }
} catch (Exception $e) {
    error_log("❌ [$request_id] LEAD SAVE ERROR: " . $e->getMessage());
}

if ($mail_sent) {
    respond(true, 'Email sent successfully', ['debug' => 'Письмо успешно отправлено'], 200);
} else {
    $last_error = error_get_last();
    respond(false, 'Failed to send email', [
        'debug' => 'Не удалось отправить письмо. Возможные причины: настройки SMTP, блокировка провайдера, неверные заголовки',
        'php_error' => is_array($last_error) ? ($last_error['message'] ?? 'Unknown error') : 'Unknown error',
        'smtp_settings' => $smtp_settings
    ], 500);
}

error_log("=== EMAIL DEBUG [$request_id] END ===");
?>