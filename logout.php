<?php
// logout.php - Versión simplificada
session_start();

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