<?php
// index.php - Dashboard con Google OAuth y Procesos Bizagi
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

// Obtener listas de directorios
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
    <title>Portal SkyTel - Transformación Digital</title>
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
            display: flex;
            flex-direction: column;
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
            max-width: 1400px;
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
        
        .main-content {
            display: flex;
            flex: 1;
            max-width: 1400px;
            margin: 2rem auto;
            gap: 2rem;
            padding: 0 2rem;
        }
        
        .sidebar {
            width: 350px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 2rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .sidebar-section {
            margin-bottom: 2rem;
        }
        
        .sidebar-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-list {
            list-style: none;
            padding: 0;
        }
        
        .section-item {
            margin-bottom: 0.5rem;
        }
        
        .section-link {
            display: block;
            padding: 10px 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .section-link:hover {
            background: #e9ecef;
            border-color: #667eea;
            transform: translateX(5px);
            text-decoration: none;
            color: #495057;
        }
        
        .external-link {
            position: relative;
        }
        
        .external-link::after {
            content: '🔗';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            opacity: 0.6;
        }
        
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .welcome-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
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
        
        .process-viewer {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .viewer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .viewer-content {
            height: 600px;
            position: relative;
            background: #f8f9fa;
        }
        
        .iframe-container {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #666;
        }
        
        .placeholder h3 {
            margin-bottom: 1rem;
            color: #333;
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
        
        .toggle-sidebar {
            display: none;
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: static;
            }
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
            
            .main-content {
                padding: 0 1rem;
            }
            
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                height: 100vh;
                z-index: 999;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            .viewer-content {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1>SkyTel</h1>
                <p>Portal de Transformación Digital</p>
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
    
    <button class="toggle-sidebar" onclick="toggleSidebar()">☰</button>
    
    <div class="main-content">
        <aside class="sidebar" id="sidebar">
            <!-- Herramientas -->
            <div class="sidebar-section">
                <h3 class="section-title">
                    🛠️ Herramientas
                </h3>
                <ul class="section-list">
                    <?php foreach ($herramientas as $herramienta): ?>
                        <li class="section-item">
                            <a href="herramientas/<?= urlencode($herramienta) ?>/" class="section-link">
                                <?= htmlspecialchars($herramienta) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Procesos Bizagi -->
            <div class="sidebar-section">
                <h3 class="section-title">
                    ⚙️ Procesos Bizagi
                </h3>
                <ul class="section-list">
                    <?php foreach ($procesos as $proceso): ?>
                        <li class="section-item">
                            <a href="#" class="section-link process-link" 
                               data-url="procesos/<?= urlencode($proceso) ?>/index.html">
                                <?= htmlspecialchars($proceso) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Capacitaciones -->
            <div class="sidebar-section">
                <h3 class="section-title">
                    📚 Capacitaciones
                </h3>
                <ul class="section-list">
                    <?php foreach ($capacitaciones as $capacitacion): ?>
                        <li class="section-item">
                            <a href="#" class="section-link process-link" 
                               data-url="capacitaciones/<?= urlencode($capacitacion) ?>/index.html">
                                <?= htmlspecialchars($capacitacion) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Recursos Compartidos -->
            <div class="sidebar-section">
                <h3 class="section-title">
                    🌐 Recursos SkyTel
                </h3>
                <ul class="section-list">
                    <li class="section-item">
                        <a href="https://sites.google.com/skytel.tech/gws/multimedia?authuser=0" 
                           target="_blank" class="section-link external-link">
                            Presentaciones
                        </a>
                    </li>
                    <li class="section-item">
                        <a href="https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0" 
                           target="_blank" class="section-link external-link">
                            Alineación
                        </a>
                    </li>
                    <li class="section-item">
                        <a href="https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588" 
                           target="_blank" class="section-link external-link">
                            Mapa de Procesos
                        </a>
                    </li>
                    <li class="section-item">
                        <a href="https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280" 
                           target="_blank" class="section-link external-link">
                            Contact Center
                        </a>
                    </li>
                    <li class="section-item">
                        <a href="https://sistemagestion.skytel.tech" 
                           target="_blank" class="section-link external-link">
                            Sistema de Gestión
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
        
        <main class="content-area">
            <div class="welcome-card">
                <div class="welcome-header">
                    <div class="welcome-icon">🎯</div>
                    <div class="welcome-text">
                        <h2>Portal de Transformación Digital</h2>
                        <p>Accede a procesos Bizagi, herramientas y recursos compartidos desde una plataforma unificada</p>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card user-type-card">
                        <div class="stat-icon"><?= $tipo_usuario['icon'] ?></div>
                        <div class="stat-title">Tipo de Usuario</div>
                        <div class="stat-value"><?= $tipo_usuario['tipo'] ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">⚙️</div>
                        <div class="stat-title">Procesos Bizagi</div>
                        <div class="stat-value"><?= count($procesos) ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🛠️</div>
                        <div class="stat-title">Herramientas</div>
                        <div class="stat-value"><?= count($herramientas) ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-title">Capacitaciones</div>
                        <div class="stat-value"><?= count($capacitaciones) ?></div>
                    </div>
                </div>
                
                <div class="session-info">
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
                        🕐 <strong>Sesión:</strong> <?= formatearFecha($login_time) ?>
                    </div>
                </div>
            </div>
            
            <div class="process-viewer">
                <div class="viewer-header">
                    <div style="font-size: 1.5rem;">📋</div>
                    <div>
                        <h3>Visor de Procesos</h3>
                        <p style="opacity: 0.9; margin: 0;">Selecciona un proceso o herramienta del menú lateral</p>
                    </div>
                </div>
                <div class="viewer-content">
                    <div class="iframe-container">
                        <div class="placeholder" id="placeholder">
                            <h3>🚀 Bienvenido al Portal SkyTel</h3>
                            <p>Selecciona un proceso, herramienta o capacitación del menú lateral para comenzar</p>
                            <p style="margin-top: 1rem; opacity: 0.7;">
                                💡 Los procesos se cargarán aquí automáticamente
                            </p>
                        </div>
                        <iframe id="processFrame" style="display: none;"></iframe>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Manejo del iframe para procesos
        document.addEventListener('DOMContentLoaded', function() {
            const processLinks = document.querySelectorAll('.process-link');
            const iframe = document.getElementById('processFrame');
            const placeholder = document.getElementById('placeholder');
            
            processLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('data-url');
                    const title = this.textContent.trim();
                    
                    if (url) {
                        iframe.src = url;
                        iframe.style.display = 'block';
                        placeholder.style.display = 'none';
                        
                        // Actualizar título del visor
                        const viewerTitle = document.querySelector('.viewer-header h3');
                        viewerTitle.textContent = title;
                        
                        // Cerrar sidebar en móvil
                        if (window.innerWidth <= 768) {
                            document.getElementById('sidebar').classList.remove('show');
                        }
                    }
                });
            });
        });
        
        // Toggle sidebar en móvil
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Cerrar sidebar al hacer click fuera en móvil
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
        
        // Auto-logout por inactividad (30 minutos)
        let inactivityTimer;
        const INACTIVITY_TIME = 30 * 60 * 1000;
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(function() {
                if (confirm('Tu sesión expirará por inactividad.\n¿Deseas mantener la sesión activa?')) {
                    resetInactivityTimer();
                } else {
                    window.location.href = 'logout.php';
                }
            }, INACTIVITY_TIME);
        }
        
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetInactivityTimer, true);
        });
        
        resetInactivityTimer();
        
        console.log('Portal SkyTel cargado correctamente');
        console.log('Procesos disponibles:', <?= json_encode($procesos) ?>);
        console.log('Herramientas disponibles:', <?= json_encode($herramientas) ?>);
    </script>
</body>
</html>