<?php
// debug-auth.php - Archivo temporal para debug
require_once 'config.php';

echo "<h2>Debug de Autenticación</h2>";

echo "<h3>1. Estado de la sesión:</h3>";
echo "session_status(): " . session_status() . "<br>";
echo "session_id(): " . session_id() . "<br>";

echo "<h3>2. Contenido de \$_SESSION:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>3. Verificación de isAuthenticated():</h3>";
echo "isAuthenticated(): " . (isAuthenticated() ? 'TRUE' : 'FALSE') . "<br>";

echo "<h3>4. Verificación de SessionManager::isValid():</h3>";
echo "SessionManager::isValid(): " . (SessionManager::isValid() ? 'TRUE' : 'FALSE') . "<br>";

if (isset($_SESSION['user'])) {
    echo "<h3>5. Datos del usuario en sesión:</h3>";
    echo "Email: " . ($_SESSION['user']['email'] ?? 'NO SET') . "<br>";
    echo "Name: " . ($_SESSION['user']['name'] ?? 'NO SET') . "<br>";
    echo "Login time: " . ($_SESSION['user']['login_time'] ?? 'NO SET') . "<br>";
    
    if (isset($_SESSION['user']['login_time'])) {
        $session_lifetime = 28800; // 8 horas
        $time_passed = time() - $_SESSION['user']['login_time'];
        echo "Tiempo transcurrido: " . $time_passed . " segundos<br>";
        echo "Límite de sesión: " . $session_lifetime . " segundos<br>";
        echo "Sesión válida por tiempo: " . ($time_passed <= $session_lifetime ? 'TRUE' : 'FALSE') . "<br>";
    }
}

echo "<h3>6. Constantes definidas:</h3>";
echo "SESSION_LIFETIME: " . (defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 'NO DEFINIDO') . "<br>";
echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NO DEFINIDO') . "<br>";

echo "<h3>7. Test manual de requireAuth():</h3>";
try {
    if (!isAuthenticated()) {
        echo "requireAuth() redirigiría a login.php<br>";
    } else {
        echo "requireAuth() permitiría el acceso<br>";
    }
} catch (Exception $e) {
    echo "Error en requireAuth(): " . $e->getMessage() . "<br>";
}
?>