<?php
// index.php - Versión simplificada sin session manager
session_start();

// Función simple para verificar autenticación
function isAuthenticated() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

// Si no está autenticado, redirigir al login
if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

// Información del usuario logueado
$usuario = $_SESSION['usuario'];
$login_time = $_SESSION['login_time'] ?? time();

// Función para formatear fecha
function formatearFecha($timestamp) {
    return date('d/m/Y H:i:s', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SkyTel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo h1 {
            font-size: 2rem;
            font-weight: 300;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .welcome-card h2 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-title {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .tools-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .tool-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .tool-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        
        .tool-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .tool-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .tool-description {
            color: #666;
            font-size: 0.9rem;
        }
        
        .session-info {
            background: #e8f4f8;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #2c5282;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1>SkyTel</h1>
                <p style="opacity: 0.8; font-size: 0.9rem;">Transformación Digital</p>
            </div>
            <div class="user-info">
                <span>Bienvenido, <strong><?= htmlspecialchars($usuario) ?></strong></span>
                <a href="logout.php" class="logout-btn">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome-card">
            <h2>🎉 ¡Bienvenido al Dashboard!</h2>
            <p>Has iniciado sesión correctamente en el sistema de Transformación Digital de SkyTel.</p>
            
            <div class="session-info">
                <strong>Información de la sesión:</strong><br>
                Usuario: <?= htmlspecialchars($usuario) ?><br>
                Hora de ingreso: <?= formatearFecha($login_time) ?><br>
                IP: <?= $_SERVER['REMOTE_ADDR'] ?? 'Desconocida' ?>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👤</div>
                <div class="stat-title">Usuario Activo</div>
                <div class="stat-value"><?= htmlspecialchars($usuario) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🕐</div>
                <div class="stat-title">Sesión Iniciada</div>
                <div class="stat-value"><?= date('H:i', $login_time) ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-title">Fecha</div>
                <div class="stat-value"><?= date('d/m/Y') ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔒</div>
                <div class="stat-title">Estado</div>
                <div class="stat-value">Autenticado</div>
            </div>
        </div>
        
        <div class="tools-section">
            <h2>🛠️ Herramientas Disponibles</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="window.location.href='herramientas/1.Cotizador/'">
                    <div class="tool-icon">💰</div>
                    <div class="tool-title">Cotizador</div>
                    <div class="tool-description">Sistema de cotización de servicios</div>
                </div>
                
                <div class="tool-card" onclick="alert('Próximamente...')">
                    <div class="tool-icon">📊</div>
                    <div class="tool-title">Reportes</div>
                    <div class="tool-description">Análisis y reportes de gestión</div>
                </div>
                
                <div class="tool-card" onclick="alert('Próximamente...')">
                    <div class="tool-icon">⚙️</div>
                    <div class="tool-title">Configuración</div>
                    <div class="tool-description">Ajustes del sistema</div>
                </div>
                
                <div class="tool-card" onclick="alert('Próximamente...')">
                    <div class="tool-title">Admin</div>
                    <div class="tool-description">Panel de administración</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-logout por inactividad (30 minutos)
        let inactivityTimer;
        const INACTIVITY_TIME = 30 * 60 * 1000; // 30 minutos en milisegundos
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(function() {
                alert('Tu sesión expirará por inactividad en 2 minutos. Haz clic en OK para mantenerla activa.');
                setTimeout(function() {
                    window.location.href = 'logout.php';
                }, 2 * 60 * 1000); // 2 minutos más
            }, INACTIVITY_TIME);
        }
        
        // Eventos para detectar actividad
        document.addEventListener('mousedown', resetInactivityTimer);
        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('keypress', resetInactivityTimer);
        document.addEventListener('scroll', resetInactivityTimer);
        document.addEventListener('touchstart', resetInactivityTimer);
        
        // Iniciar el timer
        resetInactivityTimer();
        
        console.log('Sistema de autenticación simplificado cargado correctamente');
    </script>
</body>
</html>