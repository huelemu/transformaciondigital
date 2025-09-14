<?php
// crear archivo: simulate-auth.php
require_once 'config.php';
require_once 'session-manager.php';

// Simular un usuario autenticado para test
$test_user = [
    'email' => 'test@skytel.tech',  // Cambiar por tu email real
    'name' => 'Usuario Test',
    'picture' => 'https://via.placeholder.com/150',
    'domain' => 'skytel.tech',
    'login_time' => time()
];

echo "<h1>Simulación de Autenticación</h1>";

// Verificar dominio
if (!isDomainAllowed($test_user['email'])) {
    echo "❌ Dominio no permitido: " . $test_user['email'] . "<br>";
    exit;
}

// Intentar guardar en sesión
try {
    SessionManager::start();
    $_SESSION['user'] = $test_user;
    echo "✅ Usuario guardado en sesión<br>";
    
    // Verificar que se guardó
    if (isset($_SESSION['user'])) {
        echo "✅ Sesión verificada<br>";
        echo "<pre>Datos de sesión:\n" . print_r($_SESSION['user'], true) . "</pre>";
    } else {
        echo "❌ Error: No se guardó en sesión<br>";
    }
    
    // Probar validación
    if (isAuthenticated()) {
        echo "✅ isAuthenticated() devuelve TRUE<br>";
    } else {
        echo "❌ isAuthenticated() devuelve FALSE<br>";
    }
    
    if (SessionManager::isValid()) {
        echo "✅ SessionManager::isValid() devuelve TRUE<br>";
    } else {
        echo "❌ SessionManager::isValid() devuelve FALSE<br>";
    }
    
    echo "<br><a href='index.php'>🔗 Probar acceso a index.php</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>