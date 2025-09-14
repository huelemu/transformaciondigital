<?php
// debug-simple-start.php - Inicio simplificado del debug OAuth
require_once 'config.php';
require_once 'vendor/autoload.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug OAuth - Inicio</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; text-align: center; }
        .button { background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px; font-size: 18px; }
        .button:hover { background: #0056b3; text-decoration: none; color: white; }
        .green { background: #28a745; } .green:hover { background: #1e7e34; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .code { background: #f8f9fa; padding: 10px; font-family: monospace; margin: 10px 0; border-radius: 5px; text-align: left; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Debug OAuth - Portal SkyTel</h1>";

// Verificar configuración básica
echo "<div class='info'>";
echo "<h3>📋 Configuración Actual:</h3>";
echo "<div class='code'>";
echo "CLIENT_ID: " . GOOGLE_CLIENT_ID . "<br>";
echo "REDIRECT_URI: " . GOOGLE_REDIRECT_URI . "<br>";
echo "CLIENT_SECRET: " . (empty(GOOGLE_CLIENT_SECRET) ? "❌ Vacío" : "✅ Configurado") . "<br>";
echo "</div>";
echo "</div>";

try {
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri('https://transformacion.skytel.tech/debug-oauth-callback.php');
    $client->addScope('email');
    $client->addScope('profile');
    
    // Forzar nueva autorización para debugging
    $client->setApprovalPrompt('force');
    $client->setAccessType('offline');
    
    $auth_url = $client->createAuthUrl();
    
    echo "<div class='info'>";
    echo "<h3>✅ Google Client configurado correctamente</h3>";
    echo "<p><strong>IMPORTANTE:</strong> Antes de continuar, asegúrate de que esta URL esté en Google Cloud Console:</p>";
    echo "<div class='code'>https://transformacion.skytel.tech/debug-oauth-callback.php</div>";
    echo "</div>";
    
    echo "<h2>🚀 Iniciar Debug OAuth</h2>";
    echo "<p>Este proceso te va a mostrar información detallada de cada paso.</p>";
    
    echo "<a href='$auth_url' class='button green'>🧪 Comenzar Debug OAuth</a>";
    
    echo "<br><br>";
    echo "<a href='login.php' class='button'>🔗 Volver al Login Normal</a>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error de Configuración:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>