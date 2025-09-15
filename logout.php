<?php
// logout.php - Logout simplificado para Google OAuth
session_start();

// Log del logout si hay usuario en sesión
if (isset($_SESSION['user']['email'])) {
    error_log("Logout: Usuario '{$_SESSION['user']['email']}' cerró sesión desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

// Limpiar todas las variables de sesión
session_unset();

// Destruir la sesión completamente
session_destroy();

// Limpiar la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirigir al login con mensaje de confirmación
header("Location: login.php?logged_out=1");
exit;
?>