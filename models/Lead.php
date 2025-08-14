<?php
/**
 * Класс для работы с leads в базе данных
 */

require_once __DIR__ . '/../config/database.php';

class Lead {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    /**
     * Сохранить новый lead в базу данных
     * @param array $data Данные формы
     * @param string $service Услуга/страница
     * @return bool
     */
    public function save($data, $service = 'Консультация') {
        if (!$this->pdo) {
            error_log("Lead::save: Нет подключения к базе данных");
            return false;
        }
        
        try {
            // Подготавливаем данные
            $leadData = $this->prepareLeadData($data, $service);
            
            $sql = "INSERT INTO leads (
                name, phone, email, company, message, service, 
                ip_address, user_agent, page_url, utm_source, 
                utm_medium, utm_campaign, created_at, site_name
            ) VALUES (
                :name, :phone, :email, :company, :message, :service,
                :ip_address, :user_agent, :page_url, :utm_source,
                :utm_medium, :utm_campaign, NOW(), :site_name
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($leadData);
            
            if ($result) {
                error_log("Lead::save: Успешно сохранен lead для " . $leadData['email']);
                return true;
            } else {
                error_log("Lead::save: Ошибка выполнения запроса");
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Lead::save: PDO ошибка: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Подготовить данные для сохранения
     * @param array $data Данные формы
     * @param string $service Услуга
     * @return array
     */
    private function prepareLeadData($data, $service) {
        // Получаем UTM метки из URL
        $utm = $this->getUtmParams();
        
        // Получаем информацию о странице
        $pageInfo = $this->getPageInfo();
        
        return [
            'name' => $data['name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'company' => $data['company'] ?? '',
            'message' => $data['message'] ?? '',
            'service' => $service,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'page_url' => $pageInfo['url'],
            'utm_source' => $utm['utm_source'] ?? '',
            'utm_medium' => $utm['utm_medium'] ?? '',
            'utm_campaign' => $utm['utm_campaign'] ?? '',
            'site_name' => 'Реестр Гарант'
        ];
    }
    
    /**
     * Получить IP адрес клиента
     * @return string
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Получить UTM параметры
     * @return array
     */
    private function getUtmParams() {
        $utm = [];
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign'];
        
        foreach ($utmKeys as $key) {
            if (isset($_GET[$key])) {
                $utm[$key] = $_GET[$key];
            } elseif (isset($_POST[$key])) {
                $utm[$key] = $_POST[$key];
            } else {
                $utm[$key] = '';
            }
        }
        
        return $utm;
    }
    
    /**
     * Получить информацию о странице
     * @return array
     */
    private function getPageInfo() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        return [
            'url' => $protocol . '://' . $host . $uri,
            'host' => $host,
            'uri' => $uri
        ];
    }
    
    /**
     * Получить все leads
     * @return array|false
     */
    public function getAll() {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->query("SELECT * FROM leads ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lead::getAll: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить статистику leads
     * @return array|false
     */
    public function getStats() {
        if (!$this->pdo) return false;
        
        try {
            $stats = [];
            
            // Общее количество
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM leads");
            $stats['total'] = $stmt->fetch()['total'];
            
            // По услугам
            $stmt = $this->pdo->query("SELECT service, COUNT(*) as count FROM leads GROUP BY service ORDER BY count DESC");
            $stats['by_service'] = $stmt->fetchAll();
            
            // По дням (последние 30 дней)
            $stmt = $this->pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date DESC");
            $stats['by_date'] = $stmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Lead::getStats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Проверить подключение к базе данных
     * @return bool
     */
    public function testConnection() {
        return testDbConnection();
    }
}

/**
 * Функция для быстрого сохранения lead
 * @param array $data Данные формы
 * @param string $service Услуга
 * @return bool
 */
function saveLeadToDatabase($data, $service = 'Консультация') {
    $lead = new Lead();
    return $lead->save($data, $service);
}
?>
