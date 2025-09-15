<?php
// session-manager.php - Gestor avanzado de sesiones
class SessionManager {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuración segura de sesiones
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            
            // Tiempo de vida de la sesión (8 horas)
            ini_set('session.gc_maxlifetime', 28800);
            
            session_start();
            
            // Regenerar ID de sesión periódicamente para seguridad
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutos
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
    
    public static function destroy() {
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION = array();
            
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            session_destroy();
        }
    }
    
    public static function isValid() {
        // Verificar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }
        
        // Verificar que exista el usuario en la sesión
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            return false;
        }
        
        // Verificar que tenga los campos mínimos requeridos
        if (!isset($_SESSION['user']['email']) || !isset($_SESSION['user']['login_time'])) {
            return false;
        }
        
        // Verificar tiempo de vida de la sesión
        $session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 28800; // 8 horas por defecto
        
        if (time() - $_SESSION['user']['login_time'] > $session_lifetime) {
            // Sesión expirada
            self::destroy();
            return false;
        }
        
        return true;
    }
    
    public static function extend() {
        if (self::isValid()) {
            $_SESSION['user']['last_activity'] = time();
        }
    }
    
    // Método de debug para diagnosticar problemas
    public static function debug() {
        return [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_data' => $_SESSION ?? null,
            'user_exists' => isset($_SESSION['user']),
            'user_data' => $_SESSION['user'] ?? null,
            'is_valid' => self::isValid(),
            'session_lifetime' => defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 'NOT DEFINED',
            'current_time' => time()
        ];
    }
}
?>