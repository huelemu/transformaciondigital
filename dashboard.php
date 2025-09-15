<?php
// dashboard.php - Dashboard moderno para cargar en iframe
session_start();

// Función simple para verificar autenticación
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['email']);
}

// Verificar autenticación
if (!isAuthenticated()) {
    // Si no está autenticado desde el iframe, mostrar mensaje
    echo "<h3>Sesión no válida. Por favor, <a href='login.php' target='_top'>inicia sesión</a>.</h3>";
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

// Función para obtener directorios
function obtenerDirectorios($directorio) {
    $subdirectorios = [];
    
    if (is_dir($directorio)) {
        if ($dh = opendir($directorio)) {
            while (($subdirectorio = readdir($dh)) !== false) {
                if ($subdirectorio != "." && $subdirectorio != ".." && is_dir("$directorio/$subdirectorio")) {
                    $subdirectorios[] = $subdirectorio;
                }
            }
            closedir($dh);
        }
    }
    
    sort($subdirectorios);
    return $subdirectorios;
}

// Obtener listas de directorios para estadísticas
$herramientas = obtenerDirectorios("herramientas");
$procesos = obtenerDirectorios("procesos");
$capacitaciones = obtenerDirectorios("capacitaciones");

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
    <title>Dashboard - SkyTel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-header {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid #f0f0f0;
            object-fit: cover;
        }
        
        .user-avatar-fallback {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 2rem;
        }
        
        .user-info-large {
            flex: 1;
        }
        
        .user-info-large h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .user-info-large p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }
        
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, <?= $tipo_usuario['color'] ?>20, <?= $tipo_usuario['color'] ?>10);
            color: <?= $tipo_usuario['color'] ?>;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            border: 2px solid <?= $tipo_usuario['color'] ?>30;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .stat-title {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .stat-value {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-description {
            color: #888;
            font-size: 0.8rem;
        }
        
        .activity-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .section-title h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .activity-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .activity-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .activity-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .activity-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .session-info {
            background: linear-gradient(135deg, #e8f4f8, #f0f8ff);
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid #bee3f8;
            margin-bottom: 2rem;
        }
        
        .session-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .session-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #2c5282;
            font-size: 0.95rem;
            padding: 0.8rem;
            background: rgba(255,255,255,0.7);
            border-radius: 8px;
        }
        
        .session-item strong {
            color: #1a365d;
        }
        
        .activity-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .tools-preview {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .tool-preview-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .tool-preview-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }
        
        .tool-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .tool-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .tool-count {
            color: #667eea;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .time-greeting {
            display: inline-block;
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            border: 1px solid #ffeaa7;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .user-welcome {
                flex-direction: column;
                text-align: center;
            }
            
            .user-info-large h1 {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            
            .activity-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header de Bienvenida -->
        <div class="welcome-header">
            <?php
            $hora = date('H');
            if ($hora < 12) {
                $saludo = "Buenos días";
                $emoji = "🌅";
            } elseif ($hora < 18) {
                $saludo = "Buenas tardes";
                $emoji = "☀️";
            } else {
                $saludo = "Buenas noches";
                $emoji = "🌙";
            }
            ?>
            
            <div class="time-greeting">
                <?= $emoji ?> <?= $saludo ?> - <?= date('l, j \d\e F \d\e Y') ?>
            </div>
            
            <div class="user-welcome">
                <?php if (!empty($picture)): ?>
                    <img src="<?= htmlspecialchars($picture) ?>" alt="Avatar" class="user-avatar-large">
                <?php else: ?>
                    <div class="user-avatar-large user-avatar-fallback">
                        <?= strtoupper(substr($nombre, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                
                <div class="user-info-large">
                    <h1>¡Bienvenido, <?= htmlspecialchars(explode(' ', $nombre)[0]) ?>!</h1>
                    <p><?= htmlspecialchars($email) ?></p>
                    <div class="user-badge">
                        <?= $tipo_usuario['icon'] ?> <?= $tipo_usuario['tipo'] ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">⚙️</div>
                <div class="stat-title">Procesos</div>
                <div class="stat-value"><?= count($procesos) ?></div>
                <div class="stat-description">Procesos disponibles</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛠️</div>
                <div class="stat-title">Herramientas</div>
                <div class="stat-value"><?= count($herramientas) ?></div>
                <div class="stat-description">Herramientas activas</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-title">Videos</div>
                <div class="stat-value"><?= count($capacitaciones) ?></div>
                <div class="stat-description">Temas disponibles</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🕐</div>
                <div class="stat-title">Tiempo de Sesión</div>
                <div class="stat-value"><?= round((time() - $login_time) / 60) ?></div>
                <div class="stat-description">Minutos conectado</div>
            </div>
        </div>
        
        <!-- Información de Sesión -->
        <div class="session-info">
            <div class="section-title">
                <span style="font-size: 1.5rem;">📊</span>
                <h2>Información de Sesión</h2>
            </div>
            
            <div class="session-grid">
                <div class="session-item">
                    <span class="activity-indicator"></span>
                    <strong>Estado:</strong> <span style="color: #28a745; font-weight: 600;">En línea</span>
                </div>
                <div class="session-item">
                    👤 <strong>Usuario:</strong> <?= htmlspecialchars($nombre) ?>
                </div>
                <div class="session-item">
                    🏢 <strong>Organización:</strong> <?= htmlspecialchars($domain) ?>
                </div>
                <div class="session-item">
                    🕐 <strong>Inicio de sesión:</strong> <?= formatearFecha($login_time) ?>
                </div>
                <div class="session-item">
                    🌐 <strong>Dirección IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Desconocida' ?>
                </div>
                <div class="session-item">
                    🔒 <strong>Método:</strong> Google OAuth 2.0
                </div>
            </div>
        </div>
        
        <!-- Actividades Recientes -->
        <div class="activity-section">
            <div class="section-title">
                <span style="font-size: 1.5rem;">🚀</span>
                <h2>Actividades y Accesos Rápidos</h2>
            </div>
            
            <div class="activity-grid">
                <div class="activity-card">
                    <div class="activity-title">
                        💰 Cotizador
                    </div>
                    <div class="activity-description">
                        Sistema minimalista de cotización de servicios. Gestiona costos, márgenes y genera presupuestos.
                    </div>
                </div>
                
                <div class="activity-card">
                    <div class="activity-title">
                        📋 Procesos de Negocio
                    </div>
                    <div class="activity-description">
                        Accede a los procesos documentados de SkyTel. Flujos de trabajo, procedimientos y metodologías.
                    </div>
                </div>
                
                <div class="activity-card">
                    <div class="activity-title">
                        📚 Centro Videos
                    </div>
                    <div class="activity-description">
                        Material de entrenamiento, videos tutoriales y recursos de aprendizaje para el equipo.
                    </div>
                </div>
                
                <div class="activity-card">
                    <div class="activity-title">
                        🌐 Recursos Corporativos
                    </div>
                    <div class="activity-description">
                        Enlaces directos a sistemas internos, documentación compartida y herramientas corporativas.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vista Previa de Herramientas -->
        <div class="tools-preview">
            <div class="section-title">
                <span style="font-size: 1.5rem;">🎯</span>
                <h2>Resumen de Recursos</h2>
            </div>
            
            <div class="tools-grid">
                <div class="tool-preview-card">
                    <div class="tool-icon">🛠️</div>
                    <div class="tool-name">Herramientas</div>
                    <div class="tool-count"><?= count($herramientas) ?> disponibles</div>
                </div>
                
                <div class="tool-preview-card">
                    <div class="tool-icon">⚙️</div>
                    <div class="tool-name">Procesos</div>
                    <div class="tool-count"><?= count($procesos) ?> activos</div>
                </div>
                
                <div class="tool-preview-card">
                    <div class="tool-icon">📚</div>
                    <div class="tool-name">Videos</div>
                    <div class="tool-count"><?= count($capacitaciones) ?> cursos</div>
                </div>
                
                <div class="tool-preview-card">
                    <div class="tool-icon">🌐</div>
                    <div class="tool-name">Enlaces</div>
                    <div class="tool-count">Varios recursos</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Actualizar tiempo de sesión cada minuto
        setInterval(function() {
            const tiempoElement = document.querySelector('.stat-card:nth-child(4) .stat-value');
            if (tiempoElement) {
                const tiempoInicial = <?= $login_time ?>;
                const tiempoActual = Math.floor(Date.now() / 1000);
                const minutos = Math.round((tiempoActual - tiempoInicial) / 60);
                tiempoElement.textContent = minutos;
            }
        }, 60000);
        
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .activity-card, .tool-preview-card');
            
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        console.log('Dashboard cargado en iframe correctamente');
        console.log('Usuario:', '<?= addslashes($nombre) ?>');
        console.log('Dominio:', '<?= addslashes($domain) ?>');
    </script>
</body>
</html>