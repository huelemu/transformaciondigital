<?php
// debug-oauth-flow.php - Debugging completo del flujo OAuth
require_once 'config.php';
require_once 'vendor/autoload.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug OAuth Flow - SkyTel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .code { background: #f1f1f1; padding: 10px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
        .button { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px 10px 0; font-size: 16px; }
        .button:hover { background: #0056b3; text-decoration: none; color: white; }
        .green { background: #28a745; } .green:hover { background: #1e7e34; }
        .red { background: #dc3545; } .red:hover { background: #c82333; }
        .orange { background: #fd7e14; } .orange:hover { background: #e8590c; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
        .log-entry { font-family: monospace; font-size: 12px; background: #f8f9fa; padding: 5px; margin: 2px 0; border-left: 3px solid #007bff; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Debug Completo del Flujo OAuth</h1>";

// Función para logging con timestamp
function debugLog($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $color = $type === 'ERROR' ? '#dc3545' : ($type === 'WARNING' ? '#ffc107' : '#007bff');
    echo "<div class='log-entry' style='border-left-color: $color;'>[$timestamp] [$type] $message</div>";
    
    // También escribir al error log del servidor
    error_log("[$type] OAuth Debug: $message");
}

// PASO 1: Verificar configuración
echo "<div class='step'>";
echo "<h2>📋 Paso 1: Verificación de Configuración</h2>";

debugLog("Iniciando verificación de configuración OAuth");
debugLog("CLIENT_ID: " . GOOGLE_CLIENT_ID);
debugLog("REDIRECT_URI: " . GOOGLE_REDIRECT_URI);
debugLog("CLIENT_SECRET configurado: " . (!empty(GOOGLE_CLIENT_SECRET) ? "SÍ" : "NO"));

try {
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope("email");
    $client->addScope("profile");
    
    debugLog("Google Client configurado correctamente", "SUCCESS");
    echo "<p>✅ <strong>Google Client configurado correctamente</strong></p>";
    
} catch (Exception $e) {
    debugLog("Error configurando Google Client: " . $e->getMessage(), "ERROR");
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div></div></body></html>";
    exit;
}
echo "</div>";

// PASO 2: Generar URL de autenticación
echo "<div class='step'>";
echo "<h2>🔗 Paso 2: Generación de URL de Autenticación</h2>";

try {
    // Agregar parámetros adicionales para debugging
    $client->setState(json_encode([
        'timestamp' => time(),
        'debug' => true,
        'source' => 'debug-flow'
    ]));
    
    // Forzar renovación del consentimiento para debugging
    $client->setApprovalPrompt('force');
    $client->setAccessType('offline');
    
    $auth_url = $client->createAuthUrl();
    
    debugLog("URL de autenticación generada correctamente");
    debugLog("URL completa: " . $auth_url);
    
    echo "<p>✅ <strong>URL generada correctamente</strong></p>";
    echo "<div class='code'>$auth_url</div>";
    
    // Verificar componentes de la URL
    $parsed_auth = parse_url($auth_url);
    parse_str($parsed_auth['query'] ?? '', $params);
    
    echo "<h3>🔍 Componentes de la URL:</h3>";
    echo "<table>";
    $important_params = ['client_id', 'redirect_uri', 'scope', 'response_type', 'state'];
    foreach ($important_params as $param) {
        $value = $params[$param] ?? 'No presente';
        if ($param === 'redirect_uri') {
            $status = ($value === GOOGLE_REDIRECT_URI) ? "✅ CORRECTO" : "❌ INCORRECTO";
        } else {
            $status = !empty($value) ? "✅ OK" : "❌ VACÍO";
        }
        echo "<tr><th>$param</th><td>$value</td><td>$status</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    debugLog("Error generando URL de autenticación: " . $e->getMessage(), "ERROR");
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
echo "</div>";

// PASO 3: Crear callback de debugging
echo "<div class='step'>";
echo "<h2>🔄 Paso 3: Configuración de Callback de Debug</h2>";

$debug_callback_content = '<?php
// debug-oauth-callback.php - Callback específico para debugging
require_once "config.php";
require_once "session-manager.php";
require_once "vendor/autoload.php";

// Función de logging mejorada
function debugLog($message, $type = "INFO") {
    $timestamp = date("Y-m-d H:i:s");
    error_log("[$timestamp] [$type] OAuth Callback Debug: $message");
    
    // También guardar en archivo específico
    $log_file = __DIR__ . "/oauth-debug.log";
    $log_entry = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

debugLog("=== INICIO DEL CALLBACK DEBUG ===");
debugLog("Request Method: " . $_SERVER["REQUEST_METHOD"]);
debugLog("Request URI: " . $_SERVER["REQUEST_URI"]);
debugLog("Query String: " . $_SERVER["QUERY_STRING"]);

echo "<h1>🔍 Debug OAuth Callback</h1>";
echo "<div style=\"font-family: monospace; background: #f5f5f5; padding: 20px;\">";

// Mostrar todos los parámetros recibidos
echo "<h2>📥 Parámetros Recibidos:</h2>";
echo "<pre>" . print_r($_GET, true) . "</pre>";

if (!isset($_GET["code"])) {
    debugLog("ERROR: No se recibió código de autorización", "ERROR");
    if (isset($_GET["error"])) {
        debugLog("Error de Google: " . $_GET["error"], "ERROR");
        if (isset($_GET["error_description"])) {
            debugLog("Descripción del error: " . $_GET["error_description"], "ERROR");
        }
    }
    echo "<p style=\"color: red;\">❌ <strong>Error: No se recibió código de autorización</strong></p>";
    if (isset($_GET["error"])) {
        echo "<p><strong>Error:</strong> " . $_GET["error"] . "</p>";
        if (isset($_GET["error_description"])) {
            echo "<p><strong>Descripción:</strong> " . $_GET["error_description"] . "</p>";
        }
    }
    exit;
}

debugLog("Código de autorización recibido: " . substr($_GET["code"], 0, 20) . "...");

try {
    // Configurar cliente
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri("https://transformacion.skytel.tech/debug-oauth-callback.php");
    
    debugLog("Cliente OAuth configurado para callback debug");
    
    // Intercambiar código por token
    debugLog("Intercambiando código por token...");
    $token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);
    
    if (isset($token["error"])) {
        throw new Exception("Error en token: " . $token["error"]);
    }
    
    debugLog("Token obtenido exitosamente");
    echo "<h2>✅ Token obtenido correctamente</h2>";
    
    $client->setAccessToken($token);
    
    // Obtener información del usuario
    debugLog("Obteniendo información del usuario...");
    $oauth2 = new Google_Service_Oauth2($client);
    $userinfo = $oauth2->userinfo->get();
    
    $email = $userinfo->email;
    $name = $userinfo->name;
    $picture = $userinfo->picture;
    
    debugLog("Usuario obtenido: $email");
    
    echo "<h2>👤 Información del Usuario:</h2>";
    echo "<table border=\"1\" style=\"border-collapse: collapse; width: 100%;\">";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>Email</td><td>$email</td></tr>";
    echo "<tr><td>Nombre</td><td>$name</td></tr>";
    echo "<tr><td>Foto</td><td>$picture</td></tr>";
    echo "<tr><td>Email Verificado</td><td>" . ($userinfo->verifiedEmail ? "Sí" : "No") . "</td></tr>";
    if (!empty($userinfo->hd)) {
        echo "<tr><td>Hosted Domain</td><td>" . $userinfo->hd . "</td></tr>";
    }
    echo "</table>";
    
    // Verificar dominio
    debugLog("Verificando dominio para email: $email");
    $domain_allowed = isDomainAllowed($email);
    debugLog("Resultado verificación dominio: " . ($domain_allowed ? "PERMITIDO" : "DENEGADO"));
    
    echo "<h2>🏢 Verificación de Dominio:</h2>";
    if ($domain_allowed) {
        echo "<p style=\"color: green;\">✅ <strong>Dominio permitido</strong></p>";
        
        // Intentar guardar sesión
        debugLog("Guardando sesión...");
        try {
            SessionManager::start();
            
            $_SESSION["user"] = [
                "email" => $email,
                "name" => $name,
                "picture" => $picture,
                "domain" => substr(strrchr($email, "@"), 1),
                "login_time" => time()
            ];
            
            debugLog("Sesión guardada exitosamente");
            echo "<h2>💾 Sesión:</h2>";
            echo "<p style=\"color: green;\">✅ <strong>Sesión guardada correctamente</strong></p>";
            echo "<pre>" . print_r($_SESSION["user"], true) . "</pre>";
            
            // Verificar autenticación
            $is_auth = isAuthenticated();
            debugLog("isAuthenticated(): " . ($is_auth ? "true" : "false"));
            echo "<p><strong>isAuthenticated():</strong> " . ($is_auth ? "✅ SÍ" : "❌ NO") . "</p>";
            
            if ($is_auth) {
                echo "<h2>🚀 Login Exitoso</h2>";
                echo "<p><a href=\"index.php\" style=\"background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">🔗 Ir al Portal</a></p>";
            } else {
                debugLog("ERROR: Usuario no autenticado después de guardar sesión", "ERROR");
                echo "<p style=\"color: red;\">❌ <strong>Error: Usuario no autenticado después de guardar sesión</strong></p>";
            }
            
        } catch (Exception $e) {
            debugLog("Error guardando sesión: " . $e->getMessage(), "ERROR");
            echo "<p style=\"color: red;\">❌ <strong>Error guardando sesión:</strong> " . $e->getMessage() . "</p>";
        }
        
    } else {
        $user_domain = substr(strrchr($email, "@"), 1);
        debugLog("Dominio no permitido: $user_domain", "WARNING");
        echo "<p style=\"color: red;\">❌ <strong>Dominio no permitido:</strong> $user_domain</p>";
        
        global $allowed_domains;
        echo "<p><strong>Dominios permitidos:</strong></p>";
        echo "<ul>";
        foreach ($allowed_domains as $domain) {
            echo "<li>@$domain</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    debugLog("Error en callback: " . $e->getMessage(), "ERROR");
    echo "<p style=\"color: red;\">❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

debugLog("=== FIN DEL CALLBACK DEBUG ===");
echo "<h2>📄 Log del Proceso:</h2>";
$log_file = __DIR__ . "/oauth-debug.log";
if (file_exists($log_file)) {
    echo "<pre style=\"background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; max-height: 300px; overflow-y: auto;\">";
    echo htmlspecialchars(file_get_contents($log_file));
    echo "</pre>";
}

echo "</div>";
?>';

// Guardar el archivo de callback debug
file_put_contents(__DIR__ . '/debug-oauth-callback.php', $debug_callback_content);
debugLog("Archivo debug-oauth-callback.php creado");

echo "<p>✅ <strong>Archivo de callback de debug creado:</strong> <code>debug-oauth-callback.php</code></p>";
echo "<p>⚠️ <strong>Importante:</strong> Agrega esta URL a Google Cloud Console:</p>";
echo "<div class='code'>https://transformacion.skytel.tech/debug-oauth-callback.php</div>";
echo "</div>";

// PASO 4: Botones de acción
echo "<div class='step success'>";
echo "<h2>🚀 Paso 4: Ejecutar Pruebas</h2>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Agrega la URL del callback debug a Google Cloud Console</li>";
echo "<li>Haz clic en 'Probar OAuth con Debug'</li>";
echo "<li>Autoriza con tu cuenta de Google</li>";
echo "<li>Revisa los logs detallados</li>";
echo "</ol>";

echo "<a href='$auth_url' class='button green'>🧪 Probar OAuth con Debug</a>";
echo "<a href='oauth-debug.log' class='button orange' target='_blank'>📄 Ver Logs</a>";
echo "<a href='login.php' class='button'>🔗 Login Normal</a>";
echo "</div>";

echo "</div></body></html>";
?>