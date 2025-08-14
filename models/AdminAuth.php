<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class AdminAuth {
    private $db;

    public function __construct() {
        $this->db = getDatabase();
    }

    /**
     * Авторизация администратора
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM admins WHERE username = :username AND is_active = 1";
        $admin = $this->db->fetchOne($sql, ['username' => $username]);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Создаем сессию
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_name'] = $admin['full_name'];

            // Обновляем время последнего входа
            $this->updateLastLogin($admin['id']);

            return true;
        }

        return false;
    }

    /**
     * Выход из системы
     */
    public function logout() {
        session_destroy();
        header('Location: /admin/login.php');
        exit;
    }

    /**
     * Проверка авторизации
     */
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Получение данных текущего администратора
     */
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'role' => $_SESSION['admin_role'],
            'name' => $_SESSION['admin_name']
        ];
    }

    /**
     * Проверка роли
     */
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['admin_role'] === $role;
    }

    /**
     * Проверка прав доступа
     */
    public function canEdit() {
        return $this->isLoggedIn() && in_array($_SESSION['admin_role'], ['admin', 'editor']);
    }

    /**
     * Проверка прав администратора
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }

    /**
     * Обязательная авторизация
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    /**
     * Обязательные права администратора
     */
    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            die('Access denied');
        }
    }

    /**
     * Обновление времени последнего входа
     */
    private function updateLastLogin($admin_id) {
        $sql = "UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
        $this->db->query($sql, ['id' => $admin_id]);
    }

    /**
     * Создание нового администратора
     */
    public function createAdmin($data) {
        $sql = "INSERT INTO admins (username, email, password_hash, full_name, role) 
                VALUES (:username, :email, :password_hash, :full_name, :role)";
        
        $params = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'full_name' => $data['full_name'],
            'role' => $data['role'] ?? 'editor'
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Получение всех администраторов
     */
    public function getAllAdmins() {
        $sql = "SELECT id, username, email, full_name, role, is_active, last_login, created_at 
                FROM admins ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Проверка существования пользователя
     */
    public function userExists($username, $email, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM admins 
                WHERE (username = :username OR email = :email)";
        $params = ['username' => $username, 'email' => $email];

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Обновление профиля администратора
     */
    public function updateProfile($admin_id, $data) {
        $sql = "UPDATE admins SET 
                username = :username,
                email = :email,
                full_name = :full_name,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $params = [
            'id' => $admin_id,
            'username' => $data['username'],
            'email' => $data['email'],
            'full_name' => $data['full_name']
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Изменение пароля
     */
    public function changePassword($admin_id, $old_password, $new_password) {
        // Получаем текущий хеш пароля
        $sql = "SELECT password_hash FROM admins WHERE id = :id";
        $admin = $this->db->fetchOne($sql, ['id' => $admin_id]);

        if (!$admin || !password_verify($old_password, $admin['password_hash'])) {
            return false;
        }

        // Обновляем пароль
        $sql = "UPDATE admins SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        return $this->db->query($sql, [
            'id' => $admin_id,
            'password_hash' => password_hash($new_password, PASSWORD_DEFAULT)
        ]);
    }
}

// Создаем экземпляр для использования
$auth = new AdminAuth();

// Функции-помощники
function requireAuth() {
    global $auth;
    $auth->requireAuth();
}

function requireAdmin() {
    global $auth;
    $auth->requireAdmin();
}

function getCurrentAdmin() {
    global $auth;
    return $auth->getCurrentAdmin();
}

function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

function canEdit() {
    global $auth;
    return $auth->canEdit();
}
?>