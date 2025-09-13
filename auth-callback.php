<?php
// auth-callback.php - Callback de autenticación
require_once 'config.php';
require_once 'vendor/autoload.php';

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

    // Verificar dominio autorizado
    if (!isDomainAllowed($email)) {
        $user_domain = substr(strrchr($email, "@"), 1);
        header('Location: login.php?error=domain_not_allowed&domain=' . urlencode($user_domain));
        exit();
    }

    // Guardar información del usuario en la sesión
    $_SESSION['user'] = [
        'email' => $email,
        'name' => $name,
        'picture' => $picture,
        'domain' => substr(strrchr($email, "@"), 1),
        'login_time' => time()
    ];

    // Redirigir al dashboard
    header('Location: index.php');
    exit();

} catch (Exception $e) {
    error_log('Error en autenticación: ' . $e->getMessage());
    header('Location: login.php?error=auth_failed');
    exit();
}
?>
