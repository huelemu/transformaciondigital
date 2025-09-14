<?php
require_once 'config.php';
require_once 'session-manager.php';

echo "<h1>Debug de Autenticación</h1>";
echo "<h2>Estado de Sesión:</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h2>Estado de Autenticación:</h2>";
echo "isAuthenticated(): " . (isAuthenticated() ? 'SÍ' : 'NO') . "<br>";
echo "SessionManager::isValid(): " . (SessionManager::isValid() ? 'SÍ' : 'NO') . "<br>";

echo "<h2>Configuración:</h2>";
echo "GOOGLE_CLIENT_ID: " . GOOGLE_CLIENT_ID . "<br>";
echo "GOOGLE_REDIRECT_URI: " . GOOGLE_REDIRECT_URI . "<br>";

echo "<h2>Dominios Permitidos:</h2>";
global $allowed_domains;
foreach ($allowed_domains as $domain) {
    echo "- $domain<br>";
}

if (isset($_SESSION['user']['email'])) {
    echo "<h2>Verificación de Dominio:</h2>";
    $email = $_SESSION['user']['email'];
    echo "Email: $email<br>";
    echo "Dominio permitido: " . (isDomainAllowed($email) ? 'SÍ' : 'NO') . "<br>";
}
?>