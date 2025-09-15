<?php
// auth-callback.php - Callback de autenticación simplificado
session_start();

require_once 'vendor/autoload.php';

// Configuración directa (igual que en login.php)
define('GOOGLE_CLIENT_ID', '1060539804507-ujrlt0dldfr0henc75v0nt5f6ij1l5iq.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'https://transformacion.skytel.tech/auth-callback.php');

// Dominios permitidos
$allowed_domains = [
    'skytel.tech',
    'skytel.com.ar', 
    'skytel.com.uy',
    'skytel.com.py',
    'skytel.com.es',
    'skytel.com.do'
];

// Función para verificar dominio
function isDomainAllowed($email) {
    global $allowed_domains;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $user_domain = substr(strrchr($email, "@"), 1);
    return in_array($user_domain, $allowed_domains);
}

// Verificar que se recibió el código
if (!isset($_GET['code'])) {
    header('Location: login.php?error=no_code');
    exit();
}

try {
    // Configurar cliente de Google
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

    // Guardar información del usuario en la sesión (estructura simplificada)
    $_SESSION['user'] = [
        'email' => $email,
        'name' => $name,
        'picture' => $picture,
        'domain' => substr(strrchr($email, "@"), 1),
        'login_time' => time(),
        'last_activity' => time()
    ];

    // Log del login exitoso
    error_log("Login exitoso: Usuario '{$email}' desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

    // Redirigir al dashboard
    header('Location: index.php');
    exit();

} catch (Exception $e) {
    // Log del error
    error_log('Error en autenticación: ' . $e->getMessage());
    
    // Redirigir al login con error
    header('Location: login.php?error=auth_failed');
    exit();
}
?>