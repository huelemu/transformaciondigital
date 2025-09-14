<?php
// debug-oauth-callback.php - Callback específico para debugging
require_once 'config.php';
require_once 'session-manager.php';
require_once 'vendor/autoload.php';

// Función de logging mejorada
function debugLog($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [$type] OAuth Callback Debug: $message");
}

debugLog('=== INICIO DEL CALLBACK DEBUG ===');
debugLog('Request Method: ' . $_SERVER['REQUEST_METHOD']);
debugLog('Request URI: ' . $_SERVER['REQUEST_URI']);
debugLog('Query String: ' . $_SERVER['QUERY_STRING']);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug OAuth Callback</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px 10px 0; }
        .green { background: #28a745; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Debug OAuth Callback</h1>";

// Mostrar todos los parámetros recibidos
echo "<div class='info'>";
echo "<h2>📥 Parámetros Recibidos:</h2>";
echo "<pre>" . htmlspecialchars(print_r($_GET, true)) . "</pre>";
echo "</div>";

if (!isset($_GET['code'])) {
    debugLog('ERROR: No se recibió código de autorización', 'ERROR');
    echo "<div class='error'>";
    echo "<h2>❌ Error: No se recibió código de autorización</h2>";
    
    if (isset($_GET['error'])) {
        debugLog('Error de Google: ' . $_GET['error'], 'ERROR');
        echo "<p><strong>Error:</strong> " . $_GET['error'] . "</p>";
        
        if (isset($_GET['error_description'])) {
            debugLog('Descripción del error: ' . $_GET['error_description'], 'ERROR');
            echo "<p><strong>Descripción:</strong> " . $_GET['error_description'] . "</p>";
        }
    }
    echo "</div>";
    echo "</div></body></html>";
    exit;
}

debugLog('Código de autorización recibido: ' . substr($_GET['code'], 0, 20) . '...');

try {
    // Configurar cliente
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri('https://transformacion.skytel.tech/debug-oauth-callback.php');
    
    debugLog('Cliente OAuth configurado para callback debug');
    
    // Intercambiar código por token
    debugLog('Intercambiando código por token...');
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        throw new Exception('Error en token: ' . $token['error']);
    }
    
    debugLog('Token obtenido exitosamente');
    echo "<div class='success'><h2>✅ Token obtenido correctamente</h2></div>";
    
    $client->setAccessToken($token);
    
    // Obtener información del usuario
    debugLog('Obteniendo información del usuario...');
    $oauth2 = new Google_Service_Oauth2($client);
    $userinfo = $oauth2->userinfo->get();
    
    $email = $userinfo->email;
    $name = $userinfo->name;
    $picture = $userinfo->picture;
    
    debugLog("Usuario obtenido: $email");
    
    echo "<div class='info'>";
    echo "<h2>👤 Información del Usuario:</h2>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>Email</td><td>$email</td></tr>";
    echo "<tr><td>Nombre</td><td>$name</td></tr>";
    echo "<tr><td>Foto</td><td><img src='$picture' width='50' style='border-radius: 25px;'> $picture</td></tr>";
    echo "<tr><td>Email Verificado</td><td>" . ($userinfo->verifiedEmail ? 'Sí' : 'No') . "</td></tr>";
    if (!empty($userinfo->hd)) {
        echo "<tr><td>Hosted Domain</td><td>" . $userinfo->hd . "</td></tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Verificar dominio
    debugLog("Verificando dominio para email: $email");
    $domain_allowed = isDomainAllowed($email);
    debugLog('Resultado verificación dominio: ' . ($domain_allowed ? 'PERMITIDO' : 'DENEGADO'));
    
    echo "<h2>🏢 Verificación de Dominio:</h2>";
    if ($domain_allowed) {
        echo "<div class='success'>";
        echo "<p>✅ <strong>Dominio permitido</strong></p>";
        echo "</div>";
        
        // Intentar guardar sesión
        debugLog('Guardando sesión...');
        try {
            SessionManager::start();
            
            $_SESSION['user'] = [
                'email' => $email,
                'name' => $name,
                'picture' => $picture,
                'domain' => substr(strrchr($email, '@'), 1),
                'login_time' => time()
            ];
            
            debugLog('Sesión guardada exitosamente');
            echo "<div class='success'>";
            echo "<h2>💾 Sesión Guardada:</h2>";
            echo "<pre>" . htmlspecialchars(print_r($_SESSION['user'], true)) . "</pre>";
            echo "</div>";
            
            // Verificar autenticación
            $is_auth = isAuthenticated();
            debugLog('isAuthenticated(): ' . ($is_auth ? 'true' : 'false'));
            
            echo "<div class='info'>";
            echo "<p><strong>isAuthenticated():</strong> " . ($is_auth ? '✅ SÍ' : '❌ NO') . "</p>";
            echo "</div>";
            
            if ($is_auth) {
                echo "<div class='success'>";
                echo "<h2>🚀 Login Exitoso</h2>";
                echo "<p><a href='index.php' class='button green'>🔗 Ir al Portal</a></p>";
                echo "</div>";
            } else {
                debugLog('ERROR: Usuario no autenticado después de guardar sesión', 'ERROR');
                echo "<div class='error'>";
                echo "<p>❌ <strong>Error: Usuario no autenticado después de guardar sesión</strong></p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            debugLog('Error guardando sesión: ' . $e->getMessage(), 'ERROR');
            echo "<div class='error'>";
            echo "<p>❌ <strong>Error guardando sesión:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        
    } else {
        $user_domain = substr(strrchr($email, '@'), 1);
        debugLog("Dominio no permitido: $user_domain", 'WARNING');
        
        echo "<div class='error'>";
        echo "<p>❌ <strong>Dominio no permitido:</strong> @$user_domain</p>";
        
        global $allowed_domains;
        echo "<p><strong>Dominios permitidos:</strong></p>";
        echo "<ul>";
        foreach ($allowed_domains as $domain) {
            echo "<li>@$domain</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    debugLog('Error en callback: ' . $e->getMessage(), 'ERROR');
    echo "<div class='error'>";
    echo "<h2>❌ Error en el proceso:</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

debugLog('=== FIN DEL CALLBACK DEBUG ===');

echo "</div></body></html>";
?>