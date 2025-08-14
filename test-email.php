<?php
// Простой тест отправки почты
echo "<h2>Тест отправки почты</h2>";

$to = "reestrgarant@mail.ru";
$subject = "Тест с сайта";
$message = "Это тестовое письмо для проверки работы почты.\nВремя: " . date('Y-m-d H:i:s');
$headers = "From: noreply@vnesenie-v-reestr.ru\r\nContent-Type: text/plain; charset=UTF-8";

echo "<p><strong>Отправляю письмо на:</strong> " . $to . "</p>";
echo "<p><strong>Тема:</strong> " . $subject . "</p>";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "<p style='color: green;'><strong>✅ Письмо отправлено успешно!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ Ошибка отправки письма</strong></p>";
}

// Проверяем настройки PHP
echo "<h3>Настройки PHP почты:</h3>";
echo "<p><strong>sendmail_path:</strong> " . ini_get('sendmail_path') . "</p>";
echo "<p><strong>SMTP:</strong> " . ini_get('SMTP') . "</p>";
echo "<p><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</p>";

// Проверяем функцию mail
if (function_exists('mail')) {
    echo "<p style='color: green;'>✅ Функция mail() доступна</p>";
} else {
    echo "<p style='color: red;'>❌ Функция mail() недоступна</p>";
}
?>