<?php
// install.php - Script de instalación y configuración inicial
require_once 'config.php';

// Verificar si ya está configurado
if (file_exists('.installed')) {
    die('La aplicación ya está instalada. Elimina el archivo .installed para reinstalar.');
}

$errors = [];
$warnings = [];
$success_messages = [];

// Verificar requisitos del servidor
function checkRequirements() {
    global $errors, $warnings;
    
    // PHP version
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'Se requiere PHP 7.4 o superior. Versión actual: ' . PHP_VERSION;
    }
    
    // Extensions
    $required_extensions = ['curl', 'json', 'session', 'openssl'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Extensión PHP requerida no encontrada: $ext";
        }
    }
    
    // Composer
    if (!file_exists('vendor/autoload.php')) {
        $errors[] = 'Dependencias de Composer no encontradas. Ejecuta: composer install';
    }
    
    // Permisos de escritura
    $writable_dirs = ['logs'];
    foreach ($writable_dirs as $dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $errors[] = "No se pudo crear el directorio: $dir";
            }
        }
        if (!is_writable($dir)) {
            $warnings[] = "Directorio sin permisos de escritura: $dir";
        }
    }
    
    // Archivos de configuración
    if (!defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === 'your_client_id') {
        $errors[] = 'Google Client ID no configurado en config.php';
    }
    
    if (!defined('GOOGLE_CLIENT_SECRET') || GOOGLE_CLIENT_SECRET === 'your_client_secret') {
        $errors[] = 'Google Client Secret no configurado en config.php';
    }
}

// Crear archivos necesarios
function createRequiredFiles() {
    global $success_messages;
    
    // .htaccess para Apache
    $htaccess_content = "
# Seguridad básica
RewriteEngine On

# Redirigir HTTP a HTTPS (descomenta en producción)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos de configuración
<FilesMatch \"^(config|database|middleware)\.php$\">
    Order deny,allow
    Deny from all
</FilesMatch>

# Proteger logs
<Directory \"logs\">
    Order deny,allow
    Deny from all
</Directory>

# Headers de seguridad
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>
";
    
    if (!file_exists('.htaccess')) {
        file_put_contents('.htaccess', trim($htaccess_content));
        $success_messages[] = 'Archivo .htaccess creado';
    }
    
    // robots.txt
    $robots_content = "
User-agent: *
Disallow: /logs/
Disallow: /vendor/
Disallow: /config.php
Disallow: /auth-callback.php
";
    
    if (!file_exists('robots.txt')) {
        file_put_contents('robots.txt', trim($robots_content));
        $success_messages[] = 'Archivo robots.txt creado';
    }
}

// Ejecutar verificaciones
checkRequirements();

if (empty($errors)) {
    createRequiredFiles();
    
    // Marcar como instalado
    file_put_contents('.installed', date('Y-m-d H:i:s'));
    $success_messages[] = 'Instalación completada exitosamente';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Portal SkyTel</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; margin-bottom: 30px; }
        h2 { color: #34495e; margin-top: 30px; }
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .alert-error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .requirements-table th,
        .requirements-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .requirements-table th {
            background: #f8f9fa;
        }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .install-steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .step {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        code {
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalación del Portal SkyTel</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>❌ Errores que deben corregirse:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($warnings)): ?>
            <div class="alert alert-warning">
                <h3>⚠️ Advertencias:</h3>
                <ul>
                    <?php foreach ($warnings as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_messages)): ?>
            <div class="alert alert-success">
                <h3>✅ Éxito:</h3>
                <ul>
                    <?php foreach ($success_messages as $message): ?>
                        <li><?= htmlspecialchars($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <h2>Verificación de Requisitos</h2>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th>Requisito</th>
                    <th>Estado</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHP Version</td>
                    <td class="<?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'status-ok' : 'status-error' ?>">
                        <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? '✓ OK' : '✗ ERROR' ?>
                    </td>
                    <td><?= PHP_VERSION ?></td>
                </tr>
                <?php 
                $extensions = ['curl', 'json', 'session', 'openssl'];
                foreach ($extensions as $ext): 
                ?>
                <tr>
                    <td>Extensión <?= $ext ?></td>
                    <td class="<?= extension_loaded($ext) ? 'status-ok' : 'status-error' ?>">
                        <?= extension_loaded($ext) ? '✓ OK' : '✗ ERROR' ?>
                    </td>
                    <td><?= extension_loaded($ext) ? 'Instalada' : 'No encontrada' ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td>Composer</td>
                    <td class="<?= file_exists('vendor/autoload.php') ? 'status-ok' : 'status-error' ?>">
                        <?= file_exists('vendor/autoload.php') ? '✓ OK' : '✗ ERROR' ?>
                    </td>
                    <td><?= file_exists('vendor/autoload.php') ? 'Instalado' : 'Ejecutar: composer install' ?></td>
                </tr>
            </tbody>
        </table>
        
        <?php if (empty($errors)): ?>
            <div class="alert alert-success">
                <h3>🎉 ¡Instalación Completada!</h3>
                <p>Tu portal está listo para usar. Puedes acceder a:</p>
                <ul>
                    <li><strong><a href="login.php">Página de Login</a></strong> - Para autenticarse</li>
                    <li><strong><a href="index.php">Portal Principal</a></strong> - Dashboard (requiere autenticación)</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="install-steps">
                <h3>Pasos para completar la instalación:</h3>
                
                <div class="step">
                    <h4>1. Instalar dependencias</h4>
                    <p>Ejecuta en el directorio del proyecto:</p>
                    <code>composer install</code>
                </div>
                
                <div class="step">
                    <h4>2. Configurar Google OAuth</h4>
                    <p>Edita <code>config.php</code> con tus credenciales:</p>
                    <ul>
                        <li>Ve a <a href="https://console.developers.google.com/" target="_blank">Google Console</a></li>
                        <li>Crea un proyecto y habilita Google+ API</li>
                        <li>Configura las credenciales OAuth 2.0</li>
                        <li>Actualiza GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET</li>
                    </ul>
                </div>
                
                <div class="step">
                    <h4>3. Permisos de archivos</h4>
                    <p>Asegúrate de que estos directorios sean escribibles:</p>
                    <code>chmod 755 logs/</code>
                </div>
            </div>
        <?php endif; ?>
        
        <h2>Configuración de Seguridad</h2>
        <div class="install-steps">
            <div class="step">
                <h4>Para Producción:</h4>
                <ul>
                    <li>Configura HTTPS en tu servidor</li>
                    <li>Actualiza las URLs de redirección en Google Console</li>
                    <li>Revisa los permisos de archivos y directorios</li>
                    <li>Configura copias de seguridad regulares</li>
                    <li>Elimina este archivo de instalación: <code>install.php</code></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
