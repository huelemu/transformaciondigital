<?php
// logout.php - Cerrar sesión
require_once 'config.php';
require_once 'session-manager.php';
require_once 'utils.php';

 // Logout
session_start();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;


// Log de la actividad de logout
if (isAuthenticated()) {
    Utils::logToFile("User logged out: " . $_SESSION['user']['email'], 'INFO');
}

// Destruir la sesión de forma segura
SessionManager::destroy();

// Redirigir al login con mensaje de confirmación
header('Location: login.php?logged_out=1');
exit();
?>