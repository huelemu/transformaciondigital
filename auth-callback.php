<?php
// auth-callback.php - Callback de autenticación
require_once 'config.php';
require_once 'session-manager.php';
require_once 'vendor/autoload.php';

// Asegurarse de que la sesión esté iniciada
SessionManager::start();

// Debug: Log para troubleshooting
error_log('Auth callback started - Session ID: ' . session_id());

if (!isset($_GET['code'])) {
    error_log('Auth callback: No code parameter received');
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
        error_log('Auth callback: Token error - ' . $token['error']);
        throw new Exception('Error al obtener token: ' . $token['error']);
    }
    
    $client->setAccessToken($token);

    // Obtener información del usuario
    $oauth2 = new Google_Service_Oauth2($client);
    $userinfo = $oauth2->userinfo->get();
    
    $email = $userinfo->email;
    $name = $userinfo->name;
    $picture = $userinfo->picture;

    error_log('Auth callback: User authenticated - ' . $email);

    // Verificar dominio autorizado
    if (!isDomainAllowed($email)) {
        $user_domain = substr(strrchr($email, "@"), 1);
        error_log('Auth callback: Domain not allowed - ' . $user_domain);
        header('Location: login.php?error=domain_not_allowed&domain=' . urlencode($user_domain));
        exit();
    }

    // Guardar información del usuario en la sesión
    $_SESSION['user'] = [
        'email' => $email,
        'name' => $name,
        'picture' => $picture,
        'domain' => substr(strrchr($email, "@"), 1),
        'login_time' => time(),
        'last_activity' => time()
    ];

    // Log de éxito
    error_log('Auth callback: Session created successfully for ' . $email);
    
    // Verificar que la sesión se guardó correctamente
    if (!isset($_SESSION['user'])) {
        error_log('Auth callback: Failed to save session data');
        throw new Exception('Error al guardar datos de sesión');
    }

    // Log adicional para tracking (si Utils está disponible)
    if (class_exists('Utils')) {
        Utils::logToFile("User login successful: $email from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 'INFO');
    }

    // Redirigir al dashboard
    header('Location: index.php');
    exit();

} catch (Exception $e) {
    error_log('Error en autenticación: ' . $e->getMessage());
    error_log('Auth callback: Exception details - ' . $e->getTraceAsString());
    header('Location: login.php?error=auth_failed&details=' . urlencode($e->getMessage()));
    exit();
}
?>