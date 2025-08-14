<?php
// Создание первого администратора
// Запустите этот файл один раз, затем удалите его

require_once __DIR__ . '/../models/AdminAuth.php';

// Данные админа
$admin_data = [
    'username' => 'admin',
    'email' => 'admin@vnesenie-v-reestr.ru',
    'password' => 'admin123', // ОБЯЗАТЕЛЬНО СМЕНИТЕ ПАРОЛЬ!
    'full_name' => 'Главный администратор',
    'role' => 'admin'
];

try {
    // Проверяем, есть ли уже админы
    $existing_admins = $auth->getAllAdmins();
    
    if (!empty($existing_admins)) {
        echo "❌ Администраторы уже существуют!\n";
        echo "Список существующих администраторов:\n";
        foreach ($existing_admins as $admin) {
            echo "- {$admin['username']} ({$admin['email']}) - {$admin['role']}\n";
        }
        exit;
    }
    
    // Создаем первого админа
    $result = $auth->createAdmin($admin_data);
    
    if ($result) {
        echo "✅ Администратор успешно создан!\n";
        echo "Логин: " . $admin_data['username'] . "\n";
        echo "Пароль: " . $admin_data['password'] . "\n";
        echo "Email: " . $admin_data['email'] . "\n\n";
        echo "🔗 Ссылка для входа: https://vnesenie-v-reestr.ru/admin/login.php\n\n";
        echo "⚠️  ВАЖНО: Обязательно смените пароль после первого входа!\n";
        echo "⚠️  УДАЛИТЕ этот файл после создания администратора!\n";
    } else {
        echo "❌ Ошибка при создании администратора!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Возможные причины:\n";
    echo "1. Неправильные настройки базы данных\n";
    echo "2. Таблица 'admins' не существует\n";
    echo "3. Нет прав на запись в базу данных\n";
}
?>