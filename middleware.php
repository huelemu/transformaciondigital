<?php
// middleware.php - Middleware para verificación de sesión y seguridad
require_once 'config.php';

class SecurityMiddleware {
    
    public static function checkSessionExpiry() {
        if (isAuthenticated()) {
            $login_time = $_SESSION['user']['login_time'] ?? 0;
            $session_duration = 8 * 60 * 60; // 8 horas
            
            if (time() - $login_time > $session_duration) {
                session_destroy();
                header('Location: login.php?error=session_expired');
                exit();
            }
        }
    }
    
    public static function preventDirectAccess($allowed_files = []) {
        $current_file = basename($_SERVER['PHP_SELF']);
        $public_files = array_merge(['login.php', 'auth-callback.php'], $allowed_files);
        
        if (!in_array($current_file, $public_files) && !isAuthenticated()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public static function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // HTTPS redirect en producción
        if (!isset($_SERVER['HTTPS']) && $_SERVER['SERVER_NAME'] !== 'localhost') {
            $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url", true, 301);
            exit();
        }
    }
    
    public static function logActivity($action, $details = '') {
        if (isAuthenticated()) {
            $log_entry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'user' => $_SESSION['user']['email'],
                'action' => $action,
                'details' => $details,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            // Log a archivo (opcional)
            $log_line = json_encode($log_entry) . "\n";
            file_put_contents('logs/activity.log', $log_line, FILE_APPEND | LOCK_EX);
        }
    }
}

// Aplicar middleware en todas las páginas
SecurityMiddleware::setSecurityHeaders();
SecurityMiddleware::checkSessionExpiry();
?>