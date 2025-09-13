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
     * Registrar actividad en log
     */
    public static function logToFile($message, $level = 'INFO') {
        $log_dir = 'logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/app_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $user = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : 'anonymous';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $log_entry = "[$timestamp] [$level] [$user] [$ip] $message" . PHP_EOL;
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
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
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Detectar navegador básico
        if (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $browser = 'Edge';
        } else {
            $browser = 'Unknown';
        }
        
        // Detectar sistema operativo
        if (strpos($user_agent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($user_agent, 'Mac') !== false) {
            $os = 'Mac';
        } elseif (strpos($user_agent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($user_agent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($user_agent, 'iOS') !== false) {
            $os = 'iOS';
        } else {
            $os = 'Unknown';
        }
        
        return [
            'browser' => $browser,
            'os' => $os,
            'user_agent' => $user_agent
        ];
    }
}
?>