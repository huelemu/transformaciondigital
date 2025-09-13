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
        if (!isset($_SESSION['user'])) {
            return false;
        }
        
        // Verificar tiempo de vida de la sesión
        if (isset($_SESSION['user']['login_time'])) {
            $session_lifetime = 28800; // 8 horas
            if (time() - $_SESSION['user']['login_time'] > $session_lifetime) {
                return false;
            }
        }
        
        return true;
    }
    
    public static function extend() {
        if (self::isValid()) {
            $_SESSION['user']['last_activity'] = time();
        }
    }
}
?>