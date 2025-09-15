<?php
session_start();

// Ejemplo simple con usuario en duro (luego lo podés cambiar a DB o LDAP)
$usuario_valido = "admin";
$password_valido = "1234";

if ($_POST['usuario'] === $usuario_valido && $_POST['password'] === $password_valido) {
    $_SESSION['usuario'] = $_POST['usuario'];
    header("Location: index.php");
    exit;
} else {
    header("Location: login.php?error=1");
    exit;
}
?>
