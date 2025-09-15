<?php
// utils.php - Utilidades generales
class Utils {
    
    /**
     * Sanitizar entrada de usuario
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Registrar actividad en log con manejo de errores mejorado
     */
    public static function logToFile($message, $level = 'INFO') {
        try {
            $log_dir = __DIR__ . '/logs';
            
            // Intentar crear el directorio si no existe
            if (!is_dir($log_dir)) {
                if (!@mkdir($log_dir, 0755, true)) {
                    // Si no se puede crear, usar directorio temporal del sistema
                    $log_dir = sys_get_temp_dir() . '/skytel_logs';
                    if (!is_dir($log_dir)) {
                        @mkdir($log_dir, 0755, true);
                    }
                }
            }
            
            // Verificar que el directorio sea escribible
            if (!is_writable($log_dir)) {
                // Fallback: usar error_log del sistema
                error_log("[SkyTel] [$level] $message");
                return false;
            }
            
            $log_file = $log_dir . '/app_' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $user = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : 'anonymous';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $log_entry = "[$timestamp] [$level] [$user] [$ip] $message" . PHP_EOL;
            
            // Intentar escribir al archivo
            if (@file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX) === false) {
                // Fallback: usar error_log del sistema
                error_log("[SkyTel] [$level] [$user] [$ip] $message");
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            // Último fallback: error_log del sistema
            error_log("[SkyTel] [ERROR] Failed to log message: " . $e->getMessage());
            error_log("[SkyTel] [$level] $message");
            return false;
        }
    }
    
    /**
     * Verificar si el logging está funcionando
     */
    public static function testLogging() {
        $test_result = self::logToFile('Test log entry', 'TEST');
        return [
            'success' => $test_result,
            'log_dir_exists' => is_dir(__DIR__ . '/logs'),
            'log_dir_writable' => is_writable(__DIR__ . '/logs'),
            'temp_dir' => sys_get_temp_dir(),
            'current_dir' => __DIR__
        ];
    }
    
    /**
     * Formatear fecha para mostrar
     */
    public static function formatDate($timestamp, $format = 'Y-m-d H:i:s') {
        if (is_numeric($timestamp)) {
            return date($format, $timestamp);
        }
        return date($format, strtotime($timestamp));
    }
    
    /**
     * Generar breadcrumbs
     */
    public static function generateBreadcrumbs($current_page = '') {
        $breadcrumbs = ['Inicio' => 'index.php'];
        
        if ($current_page && $current_page !== 'index.php') {
            $page_name = ucfirst(str_replace(['.php', '_', '-'], ['', ' ', ' '], basename($current_page)));
            $breadcrumbs[$page_name] = $current_page;
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Verificar si es una solicitud AJAX
     */
    public static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Obtener información del navegador
     */
    public static function getBrowserInfo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Detectar navegador básico
        if (strpos($user_agent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            return 'Edge';
        } else {
            return 'Other';
        }
    }
    
    /**
     * Log simplificado que siempre funciona
     */
    public static function simpleLog($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $user = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : 'anonymous';
        error_log("[$timestamp] [SkyTel] [$level] [$user] $message");
    }
}