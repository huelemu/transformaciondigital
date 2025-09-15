<?php
// validar_login.php - Versión mejorada sin session manager
session_start();

// Usuarios válidos (puedes agregar más o conectar a una base de datos)
$usuarios_validos = [
    'admin' => '1234',
    'usuario' => 'password',
    'skytel' => 'skytel123'
];

// Verificar que se enviaron los datos por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php?error=1");
    exit;
}

// Obtener datos del formulario
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

// Limpiar datos de entrada
$usuario = trim($usuario);
$password = trim($password);

// Validar que no estén vacíos
if (empty($usuario) || empty($password)) {
    header("Location: login.php?error=1");
    exit;
}

// Verificar credenciales
if (isset($usuarios_validos[$usuario]) && $usuarios_validos[$usuario] === $password) {
    // Login exitoso
    $_SESSION['usuario'] = $usuario;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Log de login exitoso (opcional)
    error_log("Login exitoso: Usuario '{$usuario}' desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    // Redirigir al dashboard
    header("Location: index.php");
    exit;
} else {
    // Login fallido
    error_log("Intento de login fallido: Usuario '{$usuario}' desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    // Agregar un pequeño delay para prevenir ataques de fuerza bruta
    sleep(2);
    
    header("Location: login.php?error=1");
    exit;
}
?>