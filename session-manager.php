<?php
// session-manager.php - Gestor avanzado de sesiones con debug
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
            
            // Intentar iniciar sesión
            if (!session_start()) {
                error_log('SessionManager: Failed to start session');
                return false;
            }
            
            error_log('SessionManager: Session started - ID: ' . session_id());
            
            // Regenerar ID de sesión periódicamente para seguridad
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
                error_log('SessionManager: New session created');
            } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutos
                session_regenerate_id(true);
                $_SESSION['created'] = time();
                error_log('SessionManager: Session ID regenerated');
            }
            
            return true;
        }
        
        error_log('SessionManager: Session already active - ID: ' . session_id());
        return true;
    }
    
    public static function destroy() {
        if (session_status() !== PHP_SESSION_NONE) {
            $session_id = session_id();
            $_SESSION = array();
            
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            session_destroy();
            error_log('SessionManager: Session destroyed - ID: ' . $session_id);
        }
    }
    
    public static function isValid() {
        // Verificar que la sesión esté activa
        if (session_status() === PHP_SESSION_NONE) {
            error_log('SessionManager: No active session');
            return false;
        }
        
        if (!isset($_SESSION['user'])) {
            error_log('SessionManager: No user data in session');
            return false;
        }
        
        // Verificar tiempo de vida de la sesión
        if (isset($_SESSION['user']['login_time'])) {
            $session_lifetime = 28800; // 8 horas
            if (time() - $_SESSION['user']['login_time'] > $session_lifetime) {
                error_log('SessionManager: Session expired for user: ' . $_SESSION['user']['email']);
                return false;
            }
        }
        
        return true;
    }
    
    public static function extend() {
        if (self::isValid()) {
            $_SESSION['user']['last_activity'] = time();
            error_log('SessionManager: Session extended for user: ' . $_SESSION['user']['email']);
        }
    }
    
    public static function debug() {
        return [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'has_user_data' => isset($_SESSION['user']),
            'user_email' => $_SESSION['user']['email'] ?? 'none',
            'login_time' => $_SESSION['user']['login_time'] ?? 'none',
            'last_activity' => $_SESSION['user']['last_activity'] ?? 'none'
        ];
    }
}
?>