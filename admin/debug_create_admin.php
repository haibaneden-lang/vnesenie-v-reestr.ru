<?php
// Включаем показ ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Отладка создания администратора<br><br>";

echo "Текущая папка: " . __DIR__ . "<br>";
echo "Ищем файл: " . __DIR__ . '/../models/AdminAuth.php<br>';

// Проверяем существование файла
if (file_exists(__DIR__ . '/../models/AdminAuth.php')) {
    echo "✅ Файл AdminAuth.php найден<br>";
} else {
    echo "❌ Файл AdminAuth.php НЕ найден<br>";
    echo "Проверьте структуру папок:<br>";
    echo "- /models/AdminAuth.php<br>";
    echo "- /admin/debug_create_admin.php<br>";
    exit;
}

// Проверяем подключение к базе
echo "<br>🔍 Проверяем подключение к базе данных...<br>";
try {
    require_once __DIR__ . '/../config/database.php';
    $db = getDatabase();
    echo "✅ Подключение к базе данных работает<br>";
} catch (Exception $e) {
    echo "❌ Ошибка подключения к БД: " . $e->getMessage() . "<br>";
    exit;
}

// Пробуем подключить AdminAuth
echo "<br>🔍 Подключаем AdminAuth...<br>";
try {
    require_once __DIR__ . '/../models/AdminAuth.php';
    echo "✅ AdminAuth.php подключен<br>";
} catch (Exception $e) {
    echo "❌ Ошибка подключения AdminAuth: " . $e->getMessage() . "<br>";
    exit;
}

// Проверяем существование таблицы admins
echo "<br>🔍 Проверяем таблицу admins...<br>";
try {
    $result = $db->fetchOne("SHOW TABLES LIKE 'admins'");
    if ($result) {
        echo "✅ Таблица admins существует<br>";
    } else {
        echo "❌ Таблица admins НЕ существует<br>";
        echo "Создайте таблицу admins в базе данных<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Ошибка проверки таблицы: " . $e->getMessage() . "<br>";
    exit;
}

// Пробуем создать объект auth
echo "<br>🔍 Создаем объект AdminAuth...<br>";
try {
    $auth = new AdminAuth();
    echo "✅ Объект AdminAuth создан<br>";
} catch (Exception $e) {
    echo "❌ Ошибка создания AdminAuth: " . $e->getMessage() . "<br>";
    exit;
}

// Проверяем существующих админов
echo "<br>🔍 Проверяем существующих администраторов...<br>";
try {
    $existing_admins = $auth->getAllAdmins();
    echo "✅ Запрос к таблице admins выполнен<br>";
    echo "Найдено администраторов: " . count($existing_admins) . "<br>";
    
    if (!empty($existing_admins)) {
        echo "<br>Существующие администраторы:<br>";
        foreach ($existing_admins as $admin) {
            echo "- {$admin['username']} ({$admin['email']}) - {$admin['role']}<br>";
        }
        echo "<br>❌ Администраторы уже существуют! Создание нового админа отменено.<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Ошибка получения списка админов: " . $e->getMessage() . "<br>";
    exit;
}

// Данные для нового админа
$admin_data = [
    'username' => 'admin',
    'email' => 'admin@vnesenie-v-reestr.ru',
    'password' => 'admin123',
    'full_name' => 'Главный администратор',
    'role' => 'admin'
];

echo "<br>🔍 Создаем нового администратора...<br>";
try {
    $result = $auth->createAdmin($admin_data);
    
    if ($result) {
        echo "<br>✅ Администратор успешно создан!<br>";
        echo "Логин: " . $admin_data['username'] . "<br>";
        echo "Пароль: " . $admin_data['password'] . "<br>";
        echo "Email: " . $admin_data['email'] . "<br><br>";
        echo "🔗 Ссылка для входа: <a href='/admin/login.php'>https://vnesenie-v-reestr.ru/admin/login.php</a><br><br>";
        echo "⚠️ ВАЖНО: Обязательно смените пароль после первого входа!<br>";
        echo "⚠️ УДАЛИТЕ этот файл после создания администратора!<br>";
    } else {
        echo "❌ Ошибка при создании администратора (результат: false)<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка создания администратора: " . $e->getMessage() . "<br>";
}
?>