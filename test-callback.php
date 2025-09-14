<?php
// crear archivo: test-callback.php
require_once 'config.php';
require_once 'session-manager.php';

echo "<h1>Test de Configuración OAuth</h1>";

echo "<h2>1. Configuración Google OAuth:</h2>";
echo "CLIENT_ID: " . GOOGLE_CLIENT_ID . "<br>";
echo "CLIENT_SECRET: " . (empty(GOOGLE_CLIENT_SECRET) ? "❌ VACÍO" : "✅ Configurado") . "<br>";
echo "REDIRECT_URI: " . GOOGLE_REDIRECT_URI . "<br><br>";

echo "<h2>2. Verificación de bibliotecas:</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ Composer autoload encontrado<br>";
    require_once 'vendor/autoload.php';
    
    if (class_exists('Google\Client')) {
        echo "✅ Google Client disponible<br>";
        
        try {
            $client = new Google\Client();
            $client->setClientId(GOOGLE_CLIENT_ID);
            $client->setClientSecret(GOOGLE_CLIENT_SECRET);
            $client->setRedirectUri(GOOGLE_REDIRECT_URI);
            echo "✅ Cliente Google configurado correctamente<br>";
        } catch (Exception $e) {
            echo "❌ Error configurando cliente: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Google Client NO disponible<br>";
    }
} else {
    echo "❌ Composer autoload NO encontrado<br>";
}

echo "<h2>3. Verificación de sesiones:</h2>";
echo "Session status: " . session_status() . "<br>";
echo "SessionManager disponible: " . (class_exists('SessionManager') ? "✅ SÍ" : "❌ NO") . "<br>";

if (class_exists('SessionManager')) {
    try {
        SessionManager::start();
        echo "✅ SessionManager iniciado<br>";
    } catch (Exception $e) {
        echo "❌ Error iniciando SessionManager: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>4. Verificación de dominios:</h2>";
$test_emails = [
    'test@skytel.tech',
    'user@skytel.com.ar',
    'admin@gmail.com'
];

foreach ($test_emails as $email) {
    $allowed = isDomainAllowed($email);
    echo "$email: " . ($allowed ? "✅ Permitido" : "❌ Denegado") . "<br>";
}
?>