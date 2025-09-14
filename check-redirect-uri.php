<?php
// check-redirect-uri.php - Verificación de configuración actual
require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Verificación OAuth - Portal SkyTel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .code { background: #f1f1f1; padding: 10px; font-family: monospace; margin: 10px 0; }
        .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px 10px 0; }
        .button:hover { background: #0056b3; }
        .green { background: #28a745; }
        .green:hover { background: #1e7e34; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>";

echo "<h1>🔍 Verificación de Configuración OAuth</h1>";

// 1. Verificar configuración actual
echo "<div class='section'>";
echo "<h2>📋 Configuración Actual</h2>";

$config_data = [
    'CLIENT_ID' => GOOGLE_CLIENT_ID,
    'CLIENT_SECRET' => !empty(GOOGLE_CLIENT_SECRET) ? '✅ Configurado (' . strlen(GOOGLE_CLIENT_SECRET) . ' caracteres)' : '❌ Vacío',
    'REDIRECT_URI' => GOOGLE_REDIRECT_URI,
    'APP_ENV' => APP_ENV,
    'APP_DEBUG' => APP_DEBUG ? 'Activado' : 'Desactivado'
];

echo "<table>";
foreach ($config_data as $key => $value) {
    echo "<tr><th>$key</th><td>$value</td></tr>";
}
echo "</table>";
echo "</div>";

// 2. Verificar archivo .env
echo "<div class='section ";
if (file_exists(__DIR__ . '/.env')) {
    echo "success'>";
    echo "<h2>✅ Archivo .env</h2>";
    echo "<p>El archivo .env existe y se está usando.</p>";
} else {
    echo "warning'>";
    echo "<h2>⚠️ Archivo .env</h2>";
    echo "<p>No se encontró archivo .env. Se está usando configuración hardcodeada.</p>";
    echo "<p><strong>Recomendación:</strong> Crear archivo .env para mayor seguridad.</p>";
}
echo "</div>";

// 3. Verificar URLs
echo "<div class='section'>";
echo "<h2>🔗 Verificación de URLs</h2>";

$redirect_uri = GOOGLE_REDIRECT_URI;
$is_valid_url = filter_var($redirect_uri, FILTER_VALIDATE_URL);
$is_https = strpos($redirect_uri, 'https://') === 0;
$parsed = parse_url($redirect_uri);

echo "<table>";
echo "<tr><th>Verificación</th><th>Estado</th><th>Valor</th></tr>";
echo "<tr><td>Formato de URL válido</td><td>" . ($is_valid_url ? "✅ Válido" : "❌ Inválido") . "</td><td>$redirect_uri</td></tr>";
echo "<tr><td>Usa HTTPS</td><td>" . ($is_https ? "✅ Sí" : "❌ No (requerido)") . "</td><td>" . $parsed['scheme'] . "://</td></tr>";
echo "<tr><td>Dominio</td><td>✅ OK</td><td>" . $parsed['host'] . "</td></tr>";
echo "<tr><td>Ruta</td><td>✅ OK</td><td>" . $parsed['path'] . "</td></tr>";
echo "</table>";
echo "</div>";

// 4. URLs para Google Cloud Console
echo "<div class='section warning'>";
echo "<h2>🛠️ Configuración en Google Cloud Console</h2>";
echo "<p><strong>Paso 1:</strong> Ve a <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console → Credenciales</a></p>";
echo "<p><strong>Paso 2:</strong> Busca tu ID de cliente OAuth 2.0:</p>";
echo "<div class='code'>" . GOOGLE_CLIENT_ID . "</div>";
echo "<p><strong>Paso 3:</strong> En 'URIs de redirección autorizados' debe aparecer EXACTAMENTE:</p>";
echo "<div class='code'>" . GOOGLE_REDIRECT_URI . "</div>";

echo "<p><strong>URLs adicionales que podrías necesitar:</strong></p>";
echo "<div class='code'>";
echo GOOGLE_REDIRECT_URI . "<br>";
echo str_replace('auth-callback.php', 'debug-oauth-callback.php', GOOGLE_REDIRECT_URI) . "<br>";
echo "</div>";
echo "</div>";

// 5. Verificar dominios permitidos
echo "<div class='section'>";
echo "<h2>🏢 Dominios Permitidos</h2>";
global $allowed_domains;
echo "<p>Los siguientes dominios están autorizados para acceder:</p>";
echo "<ul>";
foreach ($allowed_domains as $domain) {
    echo "<li><strong>@$domain</strong></li>";
}
echo "</ul>";

// Test de dominio manual
echo "<h3>🧪 Test de tu email:</h3>";
echo "<form method='POST' style='margin: 15px 0;'>";
echo "<input type='email' name='test_email' placeholder='tu-email@ejemplo.com' value='" . (isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : '') . "' required style='padding: 8px; width: 300px;'>";
echo "<button type='submit' style='padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px; margin-left: 10px;'>Verificar</button>";
echo "</form>";

if (isset($_POST['test_email'])) {
    $test_email = trim($_POST['test_email']);
    $domain_allowed = isDomainAllowed($test_email);
    $user_domain = substr(strrchr($test_email, "@"), 1);
    
    echo "<div class='section " . ($domain_allowed ? "success" : "error") . "'>";
    echo "<h4>Resultado para: $test_email</h4>";
    echo "<p><strong>Tu dominio:</strong> @$user_domain</p>";
    echo "<p><strong>¿Permitido?</strong> " . ($domain_allowed ? "✅ SÍ" : "❌ NO") . "</p>";
    
    if (!$domain_allowed) {
        echo "<p><strong>💡 Solución:</strong> Tu dominio no está en la lista permitida. ";
        echo "¿Deberías usar una cuenta con dominio @skytel.tech o agregar @$user_domain a la lista?</p>";
    }
    echo "</div>";
}
echo "</div>";

// 6. Tests de conectividad
echo "<div class='section'>";
echo "<h2>🌐 Tests de Conectividad</h2>";

// Verificar que el archivo auth-callback.php existe
$callback_file = __DIR__ . '/auth-callback.php';
$callback_exists = file_exists($callback_file);

echo "<table>";
echo "<tr><th>Verificación</th><th>Estado</th></tr>";
echo "<tr><td>auth-callback.php existe</td><td>" . ($callback_exists ? "✅ Sí" : "❌ No") . "</td></tr>";

// Verificar Composer
$composer_exists = file_exists(__DIR__ . '/vendor/autoload.php');
echo "<tr><td>Composer instalado</td><td>" . ($composer_exists ? "✅ Sí" : "❌ No") . "</td></tr>";

// Verificar Google Client
if ($composer_exists) {
    require_once __DIR__ . '/vendor/autoload.php';
    $google_client_available = class_exists('Google\Client');
    echo "<tr><td>Google Client disponible</td><td>" . ($google_client_available ? "✅ Sí" : "❌ No") . "</td></tr>";
} else {
    echo "<tr><td>Google Client disponible</td><td>❓ No se puede verificar (Composer no instalado)</td></tr>";
}

echo "</table>";
echo "</div>";

// 7. Acciones recomendadas
echo "<div class='section success'>";
echo "<h2>🚀 Próximos Pasos</h2>";
echo "<ol>";
echo "<li><strong>Configurar Google Cloud Console</strong> con las URLs mostradas arriba</li>";
echo "<li><strong>Esperar 5-10 minutos</strong> para que los cambios se propaguen</li>";
echo "<li><strong>Probar el login</strong> con una cuenta del dominio permitido</li>";
echo "</ol>";

echo "<p><strong>Botones de prueba:</strong></p>";
echo "<a href='login.php' class='button green'>🔗 Probar Login Normal</a>";

// Solo mostrar debug si existe
if (file_exists(__DIR__ . '/debug-oauth-start.php')) {
    echo "<a href='debug-oauth-start.php' class='button'>🧪 Debug OAuth</a>";
}

echo "<a href='logout.php' class='button'>🚪 Logout (limpiar sesión)</a>";
echo "</div>";

// 8. Información de debugging
if (APP_DEBUG) {
    echo "<div class='section warning'>";
    echo "<h2>🐛 Información de Debug</h2>";
    echo "<p><strong>Sesión actual:</strong></p>";
    echo "<pre>" . htmlspecialchars(print_r($_SESSION, true)) . "</pre>";
    echo "<p><strong>Variables de servidor relevantes:</strong></p>";
    $server_vars = ['HTTP_HOST', 'REQUEST_URI', 'HTTPS', 'SERVER_NAME', 'REMOTE_ADDR'];
    echo "<table>";
    foreach ($server_vars as $var) {
        echo "<tr><th>$var</th><td>" . ($_SERVER[$var] ?? 'No definida') . "</td></tr>";
    }
    echo "</table>";
    echo "</div>";
}

echo "</body></html>";
?>