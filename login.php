<?php
// login.php - Versión simplificada sin session manager
session_start();

// Si ya está logueado, redirigir al index
if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

// Mostrar error si existe
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1':
            $error = 'Usuario o contraseña incorrectos';
            break;
        case 'session_expired':
            $error = 'Tu sesión ha expirado';
            break;
        default:
            $error = 'Error de autenticación';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkyTel</title>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
            margin: 20px;
        }
        
        .logo {
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #667eea;
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        .login-form {
            margin-top: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .login-btn:hover {
            transform: translateY(-1px);
        }
        
        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }
        
        .info-message {
            background-color: #e8f4f8;
            color: #2c5282;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #bee3f8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>SkyTel</h1>
            <p style="color: #666; margin: 0;">Transformación Digital</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['logged_out'])): ?>
            <div class="info-message">
                Has cerrado sesión correctamente
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="validar_login.php">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn">Iniciar Sesión</button>
        </form>
        
        <p style="margin-top: 2rem; color: #666; font-size: 0.9rem;">
            Usuario de prueba: <strong>admin</strong><br>
            Contraseña: <strong>1234</strong>
        </p>
    </div>
</body>
</html>