<?php
// index.php - Página principal
require_once 'config.php';
require_once 'utils.php';

// Verificar autenticación (redirige a login si no está autenticado)
requireAuth();

// Log de acceso al dashboard
Utils::logToFile("User accessed dashboard: " . $_SESSION['user']['email'], 'INFO');

// Obtener información del usuario
$user = $_SESSION['user'];
$user_domain = $user['domain'];
$is_admin = ($user_domain === 'skytel.tech');

// Si es modo debug, mostrar información adicional
$debug_info = '';
if (defined('APP_DEBUG') && APP_DEBUG) {
    $debug_info = [
        'session_data' => $_SESSION,
        'server_info' => [
            'PHP_VERSION' => PHP_VERSION,
            'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
        ],
        'config_info' => [
            'APP_ENV' => APP_ENV,
            'SESSION_LIFETIME' => SESSION_LIFETIME,
            'LOG_LEVEL' => LOG_LEVEL
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #667eea;
        }

        .user-details h2 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .user-details p {
            color: #666;
            font-size: 0.9rem;
        }

        .admin-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }

        .card-button:hover {
            transform: translateY(-2px);
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .tool-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .tool-card:hover {
            transform: translateY(-3px);
        }

        .tool-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .debug-panel {
            background: rgba(0, 0, 0, 0.8);
            color: #00ff00;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .debug-panel h4 {
            color: #ffff00;
            margin-bottom: 10px;
        }

        .debug-panel pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header con información del usuario -->
        <div class="header">
            <div class="user-info">
                <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Avatar" class="user-avatar">
                <div class="user-details">
                    <h2>
                        ¡Bienvenido, <?= htmlspecialchars($user['name']) ?>!
                        <?php if ($is_admin): ?>
                            <span class="admin-badge">ADMIN</span>
                        <?php endif; ?>
                    </h2>
                    <p><?= htmlspecialchars($user['email']) ?> | <?= htmlspecialchars($user_domain) ?></p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>

        <!-- Grid principal del dashboard -->
        <div class="dashboard-grid">
            <!-- Herramientas Principales -->
            <div class="card">
                <h3>🛠️ Herramientas de Transformación</h3>
                <p>Accede a las herramientas principales para la transformación digital de SkyTel.</p>
                
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-icon">💰</div>
                        <h4>Cotizador</h4>
                        <p>Sistema de cotizaciones automatizado</p>
                        <a href="herramientas/1.Cotizador/" class="card-button">Acceder</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">📊</div>
                        <h4>Analytics</h4>
                        <p>Análisis y métricas del negocio</p>
                        <a href="herramientas/2.Analytics/" class="card-button">Acceder</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-icon">🤖</div>
                        <h4>Automatización</h4>
                        <p>Procesos automatizados</p>
                        <a href="herramientas/3.Automatizacion/" class="card-button">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- Información del Sistema -->
            <div class="card">
                <h3>ℹ️ Información del Sistema</h3>
                <p><strong>Última conexión:</strong> <?= Utils::formatDate($user['login_time']) ?></p>
                <p><strong>Entorno:</strong> <?= APP_ENV ?></p>
                <p><strong>Versión PHP:</strong> <?= PHP_VERSION ?></p>
                
                <?php if ($is_admin): ?>
                    <a href="admin.php" class="card-button">Panel de Admin</a>
                <?php endif; ?>
            </div>

            <!-- Accesos Rápidos -->
            <div class="card">
                <h3>⚡ Accesos Rápidos</h3>
                <p>Enlaces directos a funcionalidades importantes.</p>
                
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="herramientas/1.Cotizador/nueva-cotizacion.php" class="card-button">Nueva Cotización</a>
                    <a href="herramientas/2.Analytics/dashboard.php" class="card-button">Ver Reportes</a>
                    <?php if ($is_admin): ?>
                        <a href="admin.php" class="card-button">Administración</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notificaciones -->
            <div class="card">
                <h3>🔔 Notificaciones</h3>
                <p>Manténte al día con las últimas actualizaciones del sistema.</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <p style="margin: 0; color: #28a745; font-weight: 600;">✅ Sistema funcionando correctamente</p>
                    <small style="color: #6c757d;">Última verificación: <?= date('d/m/Y H:i') ?></small>
                </div>
            </div>
        </div>

        <!-- Panel de Debug (solo en desarrollo) -->
        <?php if (defined('APP_DEBUG') && APP_DEBUG && $debug_info): ?>
            <div class="debug-panel">
                <h4>🐛 Panel de Debug (Solo Desarrollo)</h4>
                <pre><?= htmlspecialchars(json_encode($debug_info, JSON_PRETTY_PRINT)) ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-logout después de inactividad
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;

            function logout() {
                alert('Sesión expirada por inactividad');
                window.location.href = 'logout.php';
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, <?= SESSION_LIFETIME * 1000 ?>); // Convertir a milisegundos
            }
        };

        inactivityTime();

        // Verificar estado de la sesión periódicamente
        setInterval(function() {
            fetch('api-endpoint.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check_session'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.authenticated) {
                    alert('Tu sesión ha expirado');
                    window.location.href = 'login.php';
                }
            })
            .catch(error => console.log('Error verificando sesión:', error));
        }, 300000); // Verificar cada 5 minutos
    </script>
</body>
</html>