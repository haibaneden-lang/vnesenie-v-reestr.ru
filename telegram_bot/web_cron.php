<?php
/**
 * Веб-интерфейс для запуска бота через URL
 * Файл: web_cron.php
 */

// Проверка безопасности (опционально)
$secret = "telegram_bot_2025"; // Измените на свой секретный ключ
if (!isset($_GET["key"]) || $_GET["key"] !== $secret) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

require_once "bot.php";

// Устанавливаем заголовки
header("Content-Type: text/plain; charset=utf-8");

echo "🤖 Запуск Telegram бота через веб-интерфейс\n";
echo "============================================\n\n";

$startTime = microtime(true);

// Запускаем бота
$result = runBot();

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "\n⏱️ Время выполнения: {$executionTime} секунд\n";
echo "📊 Результат: " . ($result ? "Успешно" : "Ошибка") . "\n";
echo "🕐 Время: " . date("d.m.Y H:i:s") . "\n";
