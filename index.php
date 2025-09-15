<?php
// index.php - Dashboard con Google OAuth simplificado
session_start();

// Función simple para verificar autenticación
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['email']);
}

// Función para verificar si la sesión ha expirado (8 horas)
function isSessionValid() {
    if (!isAuthenticated()) {
        return false;
    }
    
    $session_lifetime = 8 * 60 * 60; // 8 horas
    $login_time = $_SESSION['user']['login_time'] ?? 0;
    
    if (time() - $login_time > $session_lifetime) {
        // Sesión expirada
        session_unset();
        session_destroy();
        return false;
    }
    
    // Actualizar última actividad
    $_SESSION['user']['last_activity'] = time();
    return true;
}

// Verificar autenticación y validez de sesión
if (!isSessionValid()) {
    header("Location: login.php?error=session_expired");
    exit;
}

// Información del usuario logueado
$user = $_SESSION['user'];
$nombre = $user['name'];
$email = $user['email'];
$picture = $user['picture'] ?? '';
$domain = $user['domain'];
$login_time = $user['login_time'];

// Función para formatear fecha
function formatearFecha($timestamp) {
    return date('d/m/Y H:i:s', $timestamp);
}

// Función para determinar el tipo de usuario
function getTipoUsuario($domain) {
    switch ($domain) {
        case 'skytel.tech':
            return ['tipo' => 'Administrador', 'icon' => '👑', 'color' => '#dc3545'];
        case 'skytel.com.ar':
            return ['tipo' => 'Argentina', 'icon' => '🇦🇷', 'color' => '#007bff'];
        case 'skytel.com.uy':
            return ['tipo' => 'Uruguay', 'icon' => '🇺🇾', 'color' => '#28a745'];
        case 'skytel.com.py':
            return ['tipo' => 'Paraguay', 'icon' => '🇵🇾', 'color' => '#fd7e14'];
        case 'skytel.com.es':
            return ['tipo' => 'España', 'icon' => '🇪🇸', 'color' => '#6f42c1'];
        case 'skytel.com.do':
            return ['tipo' => 'República Dominicana', 'icon' => '🇩🇴', 'color' => '#20c997'];
        default:
            return ['tipo' => 'Usuario', 'icon' => '👤', 'color' => '#6c757d'];
    }
}

$tipo_usuario = getTipoUsuario($domain);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Transformación Digital - SkyTel</title>
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo h1 {
            font-size: 2.2rem;
            font-weight: 300;
            margin: 0;
        }
        
        .logo p {
            opacity: 0.9;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            object-fit: cover;
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .user-email {
            opacity: 0.8;
            font-size: 0.85rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 18px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
            margin-left: 1rem;
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
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .welcome-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .welcome-icon {
            font-size: 3rem;
        }
        
        .welcome-text h2 {
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .welcome-text p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-title {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .stat-value {
            color: #333;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .user-type-card {
            background: linear-gradient(135deg, <?= $tipo_usuario['color'] ?>15, <?= $tipo_usuario['color'] ?>05);
            border: 2px solid <?= $tipo_usuario['color'] ?>30;
        }
        
        .tools-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .section-header h2 {
            color: #333;
            font-weight: 600;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        
        .tool-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .tool-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .tool-card:hover::before {
            transform: scaleX(1);
        }
        
        .tool-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
            background: white;
        }
        
        .tool-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .tool-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }
        
        .tool-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .session-info {
            background: linear-gradient(135deg, #e8f4f8, #f0f8ff);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            border: 1px solid #bee3f8;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .session-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #2c5282;
            font-size: 0.9rem;
        }
        
        .session-item strong {
            color: #1a365d;
        }
        
        .activity-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .welcome-header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .session-info {
                grid-template-columns: 1fr;
            }
        }
        
        .status-online {
            color: #28a745;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1>SkyTel</h1>
                <p>Transformación Digital</p>
            </div>
            <div class="user-info">
                <?php if (!empty($picture)): ?>
                    <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar" style="background: #667eea; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        <?= strtoupper(substr($nombre, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="user-details">
                    <div class="user-name"><?= htmlspecialchars($nombre) ?></div>
                    <div class="user-email"><?= htmlspecialchars($email) ?></div>
                </div>
                <a href="logout.php" class="logout-btn">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome-card">
            <div class="welcome-header">
                <div class="welcome-icon">🎉</div>
                <div class="welcome-text">
                    <h2>¡Bienvenido, <?= htmlspecialchars(explode(' ', $nombre)[0]) ?>!</h2>
                    <p>Has iniciado sesión correctamente en el sistema de Transformación Digital de SkyTel.</p>
                </div>
            </div>
            
            <div class="session-info">
                <div class="session-item">
                    <span class="activity-indicator"></span>
                    <strong>Estado:</strong> <span class="status-online">En línea</span>
                </div>
                <div class="session-item">
                    👤 <strong>Usuario:</strong> <?= htmlspecialchars($nombre) ?>
                </div>
                <div class="session-item">
                    📧 <strong>Email:</strong> <?= htmlspecialchars($email) ?>
                </div>
                <div class="session-item">
                    🏢 <strong>Organización:</strong> <?= htmlspecialchars($domain) ?>
                </div>
                <div class="session-item">
                    🕐 <strong>Ingreso:</strong> <?= formatearFecha($login_time) ?>
                </div>
                <div class="session-item">
                    🌐 <strong>IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Desconocida' ?>
                </div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card user-type-card">
                <div class="stat-icon"><?= $tipo_usuario['icon'] ?></div>
                <div class="stat-title">Tipo de Usuario</div>
                <div class="stat-value"><?= $tipo_usuario['tipo'] ?></div>
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
                <div class="stat-title">Seguridad</div>
                <div class="stat-value">OAuth 2.0</div>
            </div>
        </div>
        
        <div class="tools-section">
            <div class="section-header">
                <div style="font-size: 2rem;">🛠️</div>
                <h2>Herramientas Disponibles</h2>
            </div>
            
            <div class="tools-grid">
                <div class="tool-card" onclick="window.location.href='herramientas/1.Cotizador/'">
                    <div class="tool-icon">💰</div>
                    <div class="tool-title">Cotizador</div>
                    <div class="tool-description">Sistema completo de cotización de servicios y productos</div>
                </div>
                
                <div class="tool-card" onclick="showComingSoon('Reportes')">
                    <div class="tool-icon">📊</div>
                    <div class="tool-title">Reportes</div>
                    <div class="tool-description">Análisis avanzados y reportes de gestión empresarial</div>
                </div>
                
                <div class="tool-card" onclick="showComingSoon('Configuración')">
                    <div class="tool-icon">⚙️</div>
                    <div class="tool-title">Configuración</div>
                    <div class="tool-description">Ajustes y personalización del sistema</div>
                </div>
                
                <div class="tool-card" onclick="showComingSoon('CRM')">
                    <div class="tool-icon">👥</div>
                    <div class="tool-title">CRM</div>
                    <div class="tool-description">Gestión de clientes y relaciones comerciales</div>
                </div>
                
                <div class="tool-card" onclick="showComingSoon('Proyectos')">
                    <div class="tool-icon">📋</div>
                    <div class="tool-title">Proyectos</div>
                    <div class="tool-description">Gestión y seguimiento de proyectos</div>
                </div>
                
                <div class="tool-card" onclick="showComingSoon('Soporte')">
                    <div class="tool-icon">🎧</div>
                    <div class="tool-title">Soporte</div>
                    <div class="tool-description">Centro de ayuda y soporte técnico</div>
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
                if (confirm('Tu sesión expirará por inactividad en 2 minutos.\n¿Deseas mantener la sesión activa?')) {
                    resetInactivityTimer(); // Reiniciar si el usuario acepta
                } else {
                    window.location.href = 'logout.php';
                }
            }, INACTIVITY_TIME);
        }
        
        // Eventos para detectar actividad del usuario
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        events.forEach(event => {
            document.addEventListener(event, resetInactivityTimer, true);
        });
        
        // Iniciar el timer de inactividad
        resetInactivityTimer();
        
        // Función para mostrar mensaje de "próximamente"
        function showComingSoon(feature) {
            const messages = [
                `🚀 ${feature} estará disponible próximamente`,
                `⏳ Estamos trabajando en ${feature}`,
                `🔧 ${feature} en desarrollo`,
                `✨ ${feature} llegará pronto`
            ];
            
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            
            // Crear notificación moderna
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                z-index: 1000;
                font-family: inherit;
                font-weight: 500;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            notification.textContent = randomMessage;
            
            document.body.appendChild(notification);
            
            // Animar entrada
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Log para debug
        console.log('Dashboard con Google OAuth cargado correctamente');
        console.log('Usuario:', '<?= addslashes($nombre) ?>');
        console.log('Dominio:', '<?= addslashes($domain) ?>');
    </script>
</body>
</html>