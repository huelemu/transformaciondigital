<?php
// config.php - Configuración de la aplicación con variables de entorno
require_once 'session-manager.php';

// Iniciar sesión segura
SessionManager::start();

// Cargar variables de entorno
function loadEnvConfig() {
    $env_file = __DIR__ . '/.env';
    
    if (!file_exists($env_file)) {
        throw new Exception('Archivo .env no encontrado. Copia .env.example a .env y configura tus valores.');
    }
    
    $env_vars = parse_ini_file($env_file);
    
    if ($env_vars === false) {
        throw new Exception('Error al cargar el archivo .env');
    }
    
    return $env_vars;
}

try {
    $env = loadEnvConfig();
} catch (Exception $e) {
    // Fallback a configuración hardcodeada para desarrollo
    error_log('Warning: ' . $e->getMessage() . ' - Usando configuración por defecto');
    $env = [
        'GOOGLE_CLIENT_ID' => '1060539804507-ujrlt0dldfr0henc75v0nt5f6ij1l5iq.apps.googleusercontent.com',
        'GOOGLE_CLIENT_SECRET' => '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',
        'GOOGLE_REDIRECT_URI' => 'https://transformacion.skytel.tech/auth-callback.php',
        'APP_ENV' => 'development',
        'SESSION_LIFETIME' => '28800',
        'LOG_LEVEL' => 'INFO'
    ];
}

// Configuración de Google OAuth
define('GOOGLE_CLIENT_ID', $env['GOOGLE_CLIENT_ID']);
define('GOOGLE_CLIENT_SECRET', $env['GOOGLE_CLIENT_SECRET']);  
define('GOOGLE_REDIRECT_URI', $env['GOOGLE_REDIRECT_URI']);

// Configuración de la aplicación
define('APP_NAME', $env['APP_NAME'] ?? 'Portal SkyTel');
define('APP_ENV', $env['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($env['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('SESSION_LIFETIME', intval($env['SESSION_LIFETIME'] ?? 28800));

// Dominios permitidos para autenticación
$allowed_domains = [
    'skytel.tech',
    'skytel.com.ar', 
    'skytel.com.uy',
    'skytel.com.py',
    'skytel.com.es',
    'skytel.com.do'
];

// Configuración de logging
define('LOG_LEVEL', $env['LOG_LEVEL'] ?? 'INFO');
define('LOG_MAX_SIZE', $env['LOG_MAX_SIZE'] ?? '10MB');
define('LOG_MAX_FILES', intval($env['LOG_MAX_FILES'] ?? 30));

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
    return SessionManager::isValid();
}

/**
 * Verificar si el dominio del email está autorizado
 */
function isDomainAllowed($email) {
    global $allowed_domains;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $user_domain = substr(strrchr($email, "@"), 1);
    return in_array($user_domain, $allowed_domains);
}

/**
 * Redirigir a login si no está autenticado
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // Log del intento de acceso no autorizado
        Utils::logToFile('Unauthorized access attempt from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 'WARNING');
        
        header('Location: login.php');
        exit();
    }
    
    // Extender la sesión en cada acceso autorizado
    SessionManager::extend();
}

/**
 * Verificar si el usuario es administrador
 */
function isAdmin() {
    return isAuthenticated() && 
           isset($_SESSION['user']['domain']) && 
           $_SESSION['user']['domain'] === 'skytel.tech';
}

/**
 * Requerir permisos de administrador
 */
function requireAdmin() {
    requireAuth();
    
    if (!isAdmin()) {
        http_response_code(403);
        Utils::logToFile('Admin access denied for user: ' . $_SESSION['user']['email'], 'WARNING');
        die('Acceso denegado: Se requieren permisos de administrador');
    }
}

/**
 * Configurar headers de seguridad
 */
function setSecurityHeaders() {
    // Headers básicos de seguridad
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN'); 
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Solo en producción
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        
        // CSP básico (ajustar según necesidades)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://www.gstatic.com https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "frame-src 'self' https:";
        
        header("Content-Security-Policy: $csp");
    }
}

// Aplicar configuración de seguridad
setSecurityHeaders();

// Verificar que las dependencias estén instaladas
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Error: Las dependencias no están instaladas. Ejecuta: composer install');
}

// Incluir utilidades globales si están disponibles
if (file_exists(__DIR__ . '/utils.php')) {
    require_once 'utils.php';
}
?>