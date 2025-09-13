<?php
// config.php - Configuración de la aplicación
session_start();

// Configuración de Google OAuth
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

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Función para verificar dominio autorizado
function isDomainAllowed($email) {
    global $allowed_domains;
    $user_domain = substr(strrchr($email, "@"), 1);
    return in_array($user_domain, $allowed_domains);
}

// Función para redirigir a login si no está autenticado
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}
?>




