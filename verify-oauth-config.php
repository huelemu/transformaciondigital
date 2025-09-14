<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Verificación OAuth Config</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .info { background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .code { background: #f8f9fa; padding: 10px; font-family: monospace; margin: 10px 0; border-radius: 5px; }
        .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>";

echo "<h1>🔍 Verificación de Configuración OAuth</h1>";

// Mostrar configuración actual
echo "<div class='info'>";
echo "<h2>📋 Tu Configuración Actual:</h2>";
echo "<p><strong>CLIENT_ID:</strong></p>";
echo "<div class='code'>" . GOOGLE_CLIENT_ID . "</div>";
echo "<p><strong>REDIRECT_URI Principal:</strong></p>";
echo "<div class='code'>" . GOOGLE_REDIRECT_URI . "</div>";
echo "<p><strong>REDIRECT_URI para Debug:</strong></p>";
echo "<div class='code'>https://transformacion.skytel.tech/debug-oauth-callback.php</div>";
echo "</div>";

// Instrucciones específicas
echo "<div class='info'>";
echo "<h2>🛠️ URLs que DEBES configurar en Google Cloud Console:</h2>";
echo "<ol>";
echo "<li>Ve a: <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console → Credenciales</a></li>";
echo "<li>Busca el cliente OAuth con ID: <code>" . GOOGLE_CLIENT_ID . "</code></li>";
echo "<li>En 'URIs de redirección autorizados' agrega AMBAS URLs:</li>";
echo "</ol>";
echo "<div class='code'>";
echo "https://transformacion.skytel.tech/auth-callback.php<br>";
echo "https://transformacion.skytel.tech/debug-oauth-callback.php";
echo "</div>";
echo "<p><strong>⚠️ Importante:</strong> Copia y pega exactamente como aparece arriba.</p>";
echo "</div>";

// Test de configuración
try {
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    
    // Test URL principal
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $auth_url_main = $client->createAuthUrl();
    
    // Test URL debug
    $client->setRedirectUri('https://transformacion.skytel.tech/debug-oauth-callback.php');
    $auth_url_debug = $client->createAuthUrl();
    
    echo "<div class='success'>";
    echo "<h2>✅ URLs de Autenticación Generadas Correctamente</h2>";
    echo "<p>Las URLs se generaron sin errores. Ahora verifica que estén en Google Cloud Console.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error en la Configuración:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

// Botones de prueba (solo después de configurar Google Console)
echo "<div class='info'>";
echo "<h2>🧪 Pruebas (DESPUÉS de configurar Google Console):</h2>";
echo "<p><strong>⏰ Espera 5-10 minutos después de configurar Google Console, luego:</strong></p>";
echo "<a href='login.php' class='button'>🔗 Probar Login Normal</a>";
if (file_exists('debug-simple-start.php')) {
    echo "<a href='debug-simple-start.php' class='button'>🧪 Probar Debug OAuth</a>";
}
echo "</div>";

// Checklist
echo "<div class='info'>";
echo "<h2>✅ Checklist de Configuración:</h2>";
echo "<ul>";
echo "<li>☐ Accedí a Google Cloud Console</li>";
echo "<li>☐ Encontré el cliente OAuth con el ID correcto</li>";
echo "<li>☐ Agregué ambas URLs de redirección</li>";
echo "<li>☐ Guardé los cambios</li>";
echo "<li>☐ Esperé 5-10 minutos</li>";
echo "<li>☐ Probé el login</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>