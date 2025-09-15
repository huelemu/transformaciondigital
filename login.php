<?php
// login.php - Google OAuth simplificado sin session manager complejo
session_start();

// Función simple para verificar autenticación
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['email']);
}

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    header('Location: index.php');
    exit();
}

// Configuración de Google OAuth (desde tu config existente)
require_once 'vendor/autoload.php';

// Configuración directa (puedes mover esto a un archivo config-simple.php si prefieres)
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

// Función para verificar dominio
function isDomainAllowed($email) {
    global $allowed_domains;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $user_domain = substr(strrchr($email, "@"), 1);
    return in_array($user_domain, $allowed_domains);
}

// Configurar cliente de Google
$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");
$client->addScope("openid");

$auth_url = $client->createAuthUrl();

// Manejo de errores
$error_info = null;
$domain = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_code':
            $error_info = [
                'title' => 'Error de Autenticación',
                'message' => 'No se recibió el código de autorización de Google.',
                'type' => 'error'
            ];
            break;
        case 'auth_failed':
            $error_info = [
                'title' => 'Error de Autenticación',
                'message' => 'Hubo un problema al autenticar con Google. Por favor, inténtalo nuevamente.',
                'type' => 'error'
            ];
            break;
        case 'domain_not_allowed':
            $domain = $_GET['domain'] ?? '';
            $error_info = [
                'title' => 'Acceso Denegado',
                'message' => 'Tu dominio de correo electrónico no está autorizado para acceder a este sistema.',
                'type' => 'warning'
            ];
            break;
        case 'session_expired':
            $error_info = [
                'title' => 'Sesión Expirada',
                'message' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.',
                'type' => 'info'
            ];
            break;
    }
}

function renderAlert($error_info, $domain = '') {
    if (!$error_info) return '';
    
    $icon_map = [
        'error' => '❌',
        'warning' => '⚠️',
        'success' => '✅',
        'info' => 'ℹ️'
    ];
    
    $color_map = [
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'success' => '#28a745',
        'info' => '#17a2b8'
    ];
    
    $bg_map = [
        'error' => '#f8d7da',
        'warning' => '#fff3cd',
        'success' => '#d4edda',
        'info' => '#d1ecf1'
    ];
    
    $text_map = [
        'error' => '#721c24',
        'warning' => '#856404',
        'success' => '#155724',
        'info' => '#0c5460'
    ];
    
    $icon = $icon_map[$error_info['type']] ?? 'ℹ️';
    $color = $color_map[$error_info['type']] ?? '#17a2b8';
    $bg_color = $bg_map[$error_info['type']] ?? '#d1ecf1';
    $text_color = $text_map[$error_info['type']] ?? '#0c5460';
    
    $message = $error_info['message'];
    if ($error_info['type'] === 'warning' && $domain) {
        $message .= "<br><small>Dominio detectado: <strong>$domain</strong></small>";
    }
    
    return "
    <div class='alert' style='
        background: $bg_color;
        border: 1px solid $color;
        color: $text_color;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        animation: slideIn 0.3s ease-out;
    '>
        <div style='display: flex; align-items: center; gap: 10px;'>
            <span style='font-size: 18px;'>$icon</span>
            <div>
                <strong>{$error_info['title']}</strong><br>
                $message
            </div>
        </div>
    </div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Transformación Digital - SkyTel</title>
    <link rel="stylesheet" href="libs/css/jquery/jquery.ui.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/bizagi-font.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/app.css" type="text/css" />
    <link href="libs/css/google-opensans.css" rel="stylesheet">
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 450px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
        }
        
        .logo {
            margin-bottom: 2rem;
        }
        
        .biz-ex-logo-img {
            display: block;
            width: 166px;
            height: 55px;
            margin: 0 auto 1rem auto;
            background: url("libs/img/biz-ex-logo.png") no-repeat;
            background-size: contain;
        }
        
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .biz-ex-logo-img {
                background: url("libs/img/biz-ex-logo-2x.png") no-repeat;
                background-size: contain;
            }
        }
        
        .welcome-text {
            margin-bottom: 2rem;
        }
        
        .welcome-text h1 {
            color: #333;
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            font-weight: 300;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-text p {
            color: #666;
            margin: 0;
            font-size: 1.1rem;
            font-weight: 300;
        }
        
        .google-login-section {
            margin: 2rem 0;
        }
        
        .google-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 16px 24px;
            background: white;
            color: #333;
            border: 2px solid #dadce0;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .google-btn:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transform: translateY(-2px);
            text-decoration: none;
            color: #333;
        }
        
        .google-btn:active {
            transform: translateY(0);
            animation: pulse 0.3s ease;
        }
        
        .google-icon {
            width: 20px;
            height: 20px;
        }
        
        .domains-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
            border-left: 4px solid #667eea;
            text-align: left;
        }
        
        .domains-info h4 {
            color: #333;
            margin: 0 0 1rem 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .domains-list {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .domains-list div {
            margin-bottom: 0.3rem;
        }
        
        .security-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #e8f4f8;
            border-radius: 8px;
            color: #2c5282;
            font-size: 0.9rem;
            border: 1px solid #bee3f8;
        }
        
        .loading {
            display: none;
            margin-top: 1rem;
            color: #666;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        /* Alertas */
        .alert {
            margin-bottom: 20px;
            animation: slideIn 0.3s ease-out;
        }
        
        @media (max-width: 500px) {
            .login-container {
                padding: 2rem;
                margin: 10px;
            }
            
            .welcome-text h1 {
                font-size: 1.8rem;
            }
            
            .biz-ex-logo-img {
                width: 140px;
                height: 46px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="biz-ex-logo-img"></i>
        </div>
        
        <div class="welcome-text">
            <h1>Bienvenido</h1>
            <p>Transformación Digital - SkyTel</p>
        </div>
        
        <?= renderAlert($error_info, $domain) ?>
        
        <?php if (isset($_GET['logged_out'])): ?>
            <div class="alert" style="background: #d4edda; border: 1px solid #28a745; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">✅</span>
                    <div>
                        <strong>Sesión Cerrada</strong><br>
                        Has cerrado sesión correctamente
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="google-login-section">
            <a href="<?= htmlspecialchars($auth_url) ?>" class="google-btn" id="googleLoginBtn">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continuar con Google
            </a>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                Conectando con Google...
            </div>
        </div>
        
        <div class="domains-info">
            <h4>
                🏢 Dominios Autorizados
            </h4>
            <div class="domains-list">
                <div>• skytel.tech</div>
                <div>• skytel.com.ar</div>
                <div>• skytel.com.uy</div>
                <div>• skytel.com.py</div>
                <div>• skytel.com.es</div>
                <div>• skytel.com.do</div>
            </div>
        </div>
        
        <div class="security-note">
            🔒 <strong>Acceso Seguro:</strong> Solo usuarios con cuentas de los dominios autorizados pueden acceder al sistema.
        </div>
    </div>
    
    <script>
        document.getElementById('googleLoginBtn').addEventListener('click', function() {
            document.getElementById('loading').style.display = 'block';
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';
        });
        
        // Ocultar loading si el usuario regresa a la página
        window.addEventListener('pageshow', function() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('googleLoginBtn').style.opacity = '1';
            document.getElementById('googleLoginBtn').style.pointerEvents = 'auto';
        });
    </script>
</body>
</html>