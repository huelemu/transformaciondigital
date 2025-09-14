<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri('https://transformacion.skytel.tech/debug-oauth-callback.php'); // ← URL específica para debug
$client->addScope("email");
$client->addScope("profile");

$auth_url = $client->createAuthUrl();

echo "<h1>Debug OAuth - Paso 1</h1>";
echo "<p>Este enlace te llevará a Google y luego regresará con información detallada de tu cuenta:</p>";
echo "<a href='$auth_url' style='background: #4285f4; color: white; padding: 15px; text-decoration: none; border-radius: 5px; font-size: 18px;'>🔗 Autorizar con Google (Debug)</a>";
echo "<br><br>";
echo "<p><small>Nota: Esto usará debug-oauth-callback.php para capturar toda la información.</small></p>";
?>