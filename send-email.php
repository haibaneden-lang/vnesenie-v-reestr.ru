<?php
// Отключаем отображение ошибок для продакшена (включите для отладки)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Устанавливаем заголовки для CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обрабатываем preflight запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Проверяем метод запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method not allowed',
        'debug' => 'Только POST запросы разрешены'
    ]);
    exit;
}

// Получаем данные
$input_data = file_get_contents('php://input');
$input = json_decode($input_data, true);

// Генерируем уникальный ID для этой заявки
$request_id = uniqid('lead_', true);

// Логирование для отладки (закомментируйте в продакшене)
error_log("=== EMAIL DEBUG [$request_id] ===");
error_log("Received data: " . $input_data);
error_log("Parsed data: " . print_r($input, true));

// Проверяем, что данные получены
if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'No data received',
        'debug' => 'Данные не получены или неверный JSON'
    ]);
    exit;
}

// Обязательные поля
$required_fields = ['name', 'phone', 'email'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Missing required fields: ' . implode(', ', $missing_fields),
        'debug' => 'Не заполнены обязательные поля'
    ]);
    exit;
}

// Очистка и валидация данных
$name = strip_tags(trim($input['name']));
$phone = strip_tags(trim($input['phone']));
$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$company = strip_tags(trim($input['company'] ?? ''));
$message = strip_tags(trim($input['message'] ?? ''));
$service = strip_tags(trim($input['service'] ?? 'Консультация'));

// Проверка email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid email format',
        'debug' => 'Неверный формат email'
    ]);
    exit;
}

// Дополнительная валидация
if (strlen($name) < 2) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Name too short',
        'debug' => 'Имя слишком короткое'
    ]);
    exit;
}

if (strlen($phone) < 10) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Phone too short',
        'debug' => 'Телефон слишком короткий'
    ]);
    exit;
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
$headers[] = "Reply-To: " . $email;
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

// Отправка письма
$mail_sent = mail($to, $subject, $email_body, $headers_string);

// Детальное логирование результата
error_log("Mail function result: " . ($mail_sent ? 'TRUE' : 'FALSE'));
error_log("Error get last message: " . error_get_last()['message'] ?? 'no error');

// Проверяем настройки PHP для почты
$smtp_settings = [
    'sendmail_path' => ini_get('sendmail_path'),
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'mail.log' => ini_get('mail.log')
];

error_log("PHP Mail settings: " . print_r($smtp_settings, true));

if ($mail_sent) {
    // Успешная отправка
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Email sent successfully',
        'debug' => 'Письмо успешно отправлено'
    ]);
    
    // Логируем успех
    error_log("✅ EMAIL SUCCESS: Sent to " . $to . " from " . $email);
    
    // Сохраняем lead в базу данных
    try {
        error_log("🔄 [$request_id] Начинаем сохранение lead в базу данных...");
        require_once __DIR__ . '/models/Lead.php';
        $leadSaved = saveLeadToDatabase($input, $service);
        if ($leadSaved) {
            error_log("✅ [$request_id] LEAD SAVED: Успешно сохранен в базу данных");
        } else {
            error_log("⚠️ [$request_id] LEAD SAVE FAILED: Не удалось сохранить в базу данных");
        }
    } catch (Exception $e) {
        error_log("❌ [$request_id] LEAD SAVE ERROR: " . $e->getMessage());
    }
    
} else {
    // Ошибка отправки
    $last_error = error_get_last();
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to send email',
        'debug' => 'Не удалось отправить письмо. Возможные причины: настройки SMTP, блокировка провайдера, неверные заголовки',
        'php_error' => $last_error['message'] ?? 'Unknown error',
        'smtp_settings' => $smtp_settings
    ]);
    
    // Логируем ошибку
    error_log("❌ EMAIL FAILED: " . ($last_error['message'] ?? 'Unknown error'));
}

error_log("=== EMAIL DEBUG [$request_id] END ===");
?>