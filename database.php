<?php
// database.php - Configuración opcional de base de datos para logs y sesiones
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // Configurar aquí tu base de datos si decides usarla
        $host = 'localhost';
        $dbname = 'skytel_portal';
        $username = 'your_username';
        $password = 'your_password';
        
        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // Si no hay base de datos, continúa sin ella
            $this->pdo = null;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function logUserActivity($user_email, $action, $details = '', $ip = '') {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO user_activity_log (user_email, action, details, ip_address, created_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            return $stmt->execute([$user_email, $action, $details, $ip]);
        } catch (PDOException $e) {
            error_log("Database log error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserStats($user_email) {
        if (!$this->pdo) return [];
        
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    COUNT(*) as total_logins,
                    MAX(created_at) as last_login,
                    COUNT(DISTINCT DATE(created_at)) as active_days
                 FROM user_activity_log 
                 WHERE user_email = ? AND action = 'login'"
            );
            $stmt->execute([$user_email]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            error_log("Database stats error: " . $e->getMessage());
            return [];
        }
    }
}
