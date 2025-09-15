<?php
// login.php actualizado con mejor manejo de errores
require_once 'config.php';
require_once 'error-handler.php';
require_once 'vendor/autoload.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    header('Location: index.php');
    exit();
}

// Obtener información del error si existe
$error_info = null;
$domain = '';

if (isset($_GET['error'])) {
    $error_info = getErrorMessage($_GET['error']);
    if (isset($_GET['domain'])) {
        $domain = htmlspecialchars($_GET['domain']);
    }
}

$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");
$client->addScope("openid");

$auth_url = $client->createAuthUrl();
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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        
        .logo {
            margin-bottom: 2rem;
        }
        
        .logo img {
            max-width: 200px;
            height: auto;
        }
        
        .welcome-text {
            color: #333;
            margin-bottom: 2rem;
        }
        
        .welcome-text h1 {
            margin: 0 0 1rem 0;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .welcome-text p {
            color: #7f8c8d;
            margin: 0;
        }
        
        .google-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #4285f4;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }
        
        .google-btn:hover {
            background: #3367d6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 133, 244, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .google-icon {
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .footer-text {
            margin-top: 2rem;
            color: #95a5a6;
            font-size: 14px;
        }
        
        .security-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            border-left: 4px solid #4285f4;
        }
        
        .security-info h3 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .security-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .authorized-domains {
            margin-top: 1rem;
            text-align: left;
        }
        
        .domain-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 5px;
            margin-top: 8px;
        }
        
        .domain-item {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            text-align: center;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .loading {
            display: none;
            margin-top: 15px;
            color: #666;
        }
        
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4285f4;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="biz-ex-logo-img" style="font-size: 48px; color: #4285f4;"></i>
        </div>
        
        <div class="welcome-text">
            <h1>Bienvenido</h1>
            <p>Transformación Digital - SkyTel</p>
        </div>
        
        <?php if ($error_info): ?>
            <?= renderAlert($error_info, $domain) ?>
        <?php endif; ?>
        
        <a href="<?php echo htmlspecialchars($auth_url); ?>" class="google-btn" id="loginBtn">
            <div class="google-icon">
                <svg width="16" height="16" viewBox="0 0 24 24">
                    <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
            </div>
            <span id="btnText">Iniciar sesión con Google</span>
        </a>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            Conectando con Google...
        </div>
        
        <div class="security-info">
            <h3>🔒 Acceso Seguro</h3>
            <p>Solo usuarios con cuentas de los siguientes dominios pueden acceder:</p>
            <div class="authorized-domains">
                <div class="domain-list">
                    <?php 
                    global $allowed_domains;
                    foreach ($allowed_domains as $domain): 
                    ?>
                        <div class="domain-item">@<?= $domain ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="footer-text">
            Portal de Gestión SkyTel<br>
            Transformación Digital
        </div>
    </div>
    
    <script>
        document.getElementById('loginBtn').addEventListener('click', function() {
            document.getElementById('btnText').textContent = 'Conectando...';
            document.getElementById('loading').style.display = 'block';
        });
        
        // Auto-hide success/info messages after 5 seconds
        const alerts = document.querySelectorAll('.alert-success, .alert-info');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>
