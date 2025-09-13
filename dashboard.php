<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

echo "Bienvenido, " . $_SESSION['user_name'];
?>
<a href="logout.php">Cerrar sesión</a>
