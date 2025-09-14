<?php
// auth-callback.php - VERSIÓN CORREGIDA
require_once 'config.php';
require_once 'session-manager.php';  // ← AGREGAR ESTA LÍNEA
require_once 'vendor/autoload.php';

// Asegurar que no hay salida antes de los headers
ob_start();

if (!isset($_GET['code'])) {
    header('Location: login.php?error=no_code');
    exit();
}

try {
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);

    // Intercambiar código por token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        throw new Exception('Error al obtener token: ' . $token['error']);
    }
    
    $client->setAccessToken($token);

    // Obtener información del usuario
    $oauth2 = new Google_Service_Oauth2($client);
    $userinfo = $oauth2->userinfo->get();
    
    $email = $userinfo->email;
    $name = $userinfo->name;
    $picture = $userinfo->picture;

    // DEBUG: Agregar log para debugging
    error_log("Auth callback - Email: $email");
    
    // Verificar dominio autorizado
    if (!isDomainAllowed($email)) {
        $user_domain = substr(strrchr($email, "@"), 1);
        error_log("Domain not allowed: $user_domain for email: $email");
        header('Location: login.php?error=domain_not_allowed&domain=' . urlencode($user_domain));
        exit();
    }

    // IMPORTANTE: Usar SessionManager para guardar
    SessionManager::start(); // Asegurar que la sesión esté iniciada
    
    $_SESSION['user'] = [
        'email' => $email,
        'name' => $name,
        'picture' => $picture,
        'domain' => substr(strrchr($email, "@"), 1),
        'login_time' => time()
    ];

    // DEBUG: Verificar que se guardó
    error_log("Session saved: " . print_r($_SESSION['user'], true));

    // Limpiar buffer y redirigir
    ob_end_clean();
    header('Location: index.php');
    exit();

} catch (Exception $e) {
    error_log('Error en autenticación: ' . $e->getMessage());
    ob_end_clean();
    header('Location: login.php?error=auth_failed');
    exit();
}
?>