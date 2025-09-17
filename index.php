<?php
// index.php - Versión minimalista original con Google OAuth
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/css/jquery/jquery.ui.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/bizagi-font.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/app.css" type="text/css" />
    <link href="libs/css/google-opensans.css" rel="stylesheet">
    <script src="libs/js/app/jquery.min.js"></script>

    <title>Portal SkyTel - Transformación Digital</title>

    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            height: 100vh;
            font-family: 'Open Sans', sans-serif;
            overflow: hidden;
        }
        
        #content {
            display: flex;
            flex: 1;
            height: 100vh;
        }
        
        #indice {
            width: 300px;
            padding: 5px;
            box-sizing: border-box;
            overflow-y: auto;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Personalizar scrollbar */
        #indice::-webkit-scrollbar {
            width: 8px;
        }
        
        #indice::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        #indice::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        #indice::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Contenedor del menú que hace scroll */
        .menu-container {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 10px;
        }
        
        #iframe-container {
            flex: 1;
            padding: 0;
            box-sizing: border-box;
            position: relative;
            height: 100vh;
        }
        
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
            color: #666;
            text-align: center;
        }
        
        #iframe-container .placeholder {
            display: block;
        }
        
        /* Estilos mejorados para el menú */
        .biz-ex-logo-navigate {
            display: block;
            text-align: center;
            margin-bottom: 15px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }
        
        .biz-ex-logo-navigate:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            text-decoration: none;
        }
        
        .biz-ex-title-process-jml {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            margin: 20px 0 10px 0;
            padding: 8px 12px;
            background: #e9ecef;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        
        .nav-bar {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }
        
        .nav-bar li {
            margin-bottom: 5px;
        }
        
        .biz-ex-navigate {
            display: block;
            padding: 10px 12px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s ease;
            font-size: 13px;
        }
        
        .biz-ex-navigate:hover {
            background: #f8f9fa;
            border-color: #667eea;
            transform: translateX(3px);
            text-decoration: none;
            color: #495057;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }
        
        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Sección de usuario mejorada - posición fija al final */
        .user-section {
            padding: 15px 12px;
            border-top: 2px solid #e9ecef;
            background: #fff;
            margin-top: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #e9ecef;
            object-fit: cover;
        }
        
        .user-avatar-fallback {
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .user-details {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-email {
            font-size: 11px;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: #6c757d;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .logout-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        /* Panel de control de visibilidad */
        .visibility-controls {
            width: 20px;
            border-right: 1px solid #ccc;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 10px 0;
        }
        
        .biz-ex-svg-toggle {
            width: 16px;
            height: 16px;
            cursor: pointer;
            margin: 2px auto;
            transition: opacity 0.3s ease;
        }
        
        .biz-ex-svg-toggle:hover {
            opacity: 0.7;
        }
        
        /* Estados de visibilidad */
        .biz-ex-menu-toggle-hide {
            display: none;
        }
        
        .biz-ex-menu-toggle-show {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #indice {
                width: 250px;
            }
        }
        
        @media (max-width: 480px) {
            #indice {
                width: 200px;
            }
            
            .biz-ex-title-process-jml {
                font-size: 12px;
                padding: 6px 8px;
            }
        }
        
        /* Estilo especial para el dashboard siempre visible */
        .dashboard-home {
            margin-bottom: 20px;
        }
        
        .dashboard-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            border: none !important;
        }
        
        .dashboard-link:hover {
            transform: translateX(5px) !important;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
            color: white !important;
        }
        
        /* Estilos para menús desplegables */
        .menu-section {
            margin-bottom: 10px;
        }
        
        .collapsible {
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .collapsible:hover {
            background: #f0f0f0;
            transform: translateX(2px);
        }
        
        .toggle-icon {
            float: right;
            transition: transform 0.3s ease;
            font-size: 12px;
            margin-top: 2px;
        }
        
        .toggle-icon.rotated {
            transform: rotate(-90deg);
        }
        
        /* Secciones contraídas por defecto - CSS simplificado */
        .collapsible-content {
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            opacity: 1;
        }
        
        .collapsible-content.collapsed {
            max-height: 0;
            opacity: 0;
            margin-bottom: 0;
        }
        
        .collapsible-content li {
            animation: slideInFromLeft 0.3s ease;
        }
        
        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Mejorar hover de las secciones colapsables */
        .collapsible::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .collapsible:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="content">
        <div id="indice">
            <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="loadDashboard()">
                <i class="biz-ex-logo-img"></i>
                <div style="margin-top: 10px; font-weight: 600; color: #667eea;">Transformación Digital</div>
            </a>
            
            <!-- Dashboard siempre visible -->
            <div class="dashboard-home">
                <a href="#" class="biz-ex-navigate dashboard-link" onclick="loadDashboard()">
                    <div class="truncate-text biz-ex-menu">🏠 Dashboard Principal</div>
                </a>
            </div>
            
            <!-- Herramientas -->
            <div class="menu-section">
                <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('herramientas')">
                    🛠️ Herramientas 
                    <span class="toggle-icon rotated" id="herramientas-icon">▼</span>
                </h1>
                <ul class="nav-bar collapsible-content collapsed" id="herramientas-content">
                    <?php
                    $directorio = "herramientas";
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

                    foreach ($subdirectorios as $subdirectorio) {
                        $ruta_index = "$directorio/$subdirectorio/";
                        echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Procesos -->
            <div class="menu-section">
                <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('procesos')">
                    ⚙️ Procesos 
                    <span class="toggle-icon rotated" id="procesos-icon">▼</span>
                </h1>
                <ul class="nav-bar collapsible-content collapsed" id="procesos-content">
                    <?php
                    $directorio = "procesos";
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

                    foreach ($subdirectorios as $subdirectorio) {
                        $ruta_index = "$directorio/$subdirectorio/index.html";
                        echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Videos -->
            <div class="menu-section">
                <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('Videos')">
                    📚 Videos 
                    <span class="toggle-icon" id="capacitaciones-icon">▼</span>
                </h1>
                <ul class="nav-bar collapsible-content" id="capacitaciones-content">
                    <?php
                    $directorio = "Videos";
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

                    foreach ($subdirectorios as $subdirectorio) {
                        $ruta_index = "$directorio/$subdirectorio/index.html";
                        echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                    }
                    ?>
                    </ul>
                </div>
            <!-- Recursos Compartidos SkyTel -->
            <div class="menu-section">
                <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('recursos')">
                    🌐 Recursos SkyTel 
                    <span class="toggle-icon" id="recursos-icon">▼</span>
                </h1>
                <ul class="nav-bar collapsible-content" id="recursos-content">
                    <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentaciones</div></a></li>
                    <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Alineación...</div></a></li>
                    <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Mapa de Procesos</div></a></li>
                    <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Contact Center</div></a></li>
                    <li><a href='https://sistemagestion.skytel.tech' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Sistema de Gestión</div></a></li>
                    <li><a href='https://agentevirtual.skytel.tech/' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Agentes Virtuales</div></a></li>
                </ul>
            </div>

            <!-- Sección de usuario al final del menú -->
            <div class="user-section">
                <div class="user-info">
                    <?php if (isset($user['picture']) && !empty($user['picture'])): ?>
                        <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Avatar" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar user-avatar-fallback">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Usuario') ?></div>
                        <div class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"/>
                    </svg>
                    Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="visibility-controls">
            <img id="menu-contract" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-contract.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-hide" alt="Contraer menú">
            <img id="menu-expand" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-expand.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-show" alt="Expandir menú">
        </div>

        <div id="iframe-container">
            <div class="placeholder" id="placeholder" style="display: none;">
                <h3>🎯 Portal SkyTel</h3>
                <p>Selecciona cualquier herramienta, proceso o capacitación del menú lateral</p>
            </div>
            <iframe id="miIframe" title="Portal de Gestión" frameborder="0" allowFullScreen="true"></iframe>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // JavaScript para manejar los clics en los enlaces
            const enlaces = document.querySelectorAll('#indice a');
            const iframe = document.getElementById('miIframe');
            const placeholder = document.getElementById('placeholder');

            enlaces.forEach(enlace => {
                enlace.addEventListener('click', (event) => {
                    // Si el enlace tiene el atributo data-new-tab o es logout, no interceptar
                    if (enlace.getAttribute('data-new-tab') === 'true' || 
                        enlace.classList.contains('logout-btn') ||
                        enlace.getAttribute('onclick')) {
                        return;
                    }

                    event.preventDefault();
                    const url = enlace.href;
                    
                    if (url && url !== window.location.href + '#') {
                        iframe.src = url;
                        iframe.style.display = 'block';
                        placeholder.style.display = 'none';
                    }
                });
            });

            // Controles de visibilidad del menú
            $('#menu-contract').on("click", function() {
                $('#menu-contract').removeClass("biz-ex-menu-toggle-show").addClass("biz-ex-menu-toggle-hide");
                $('#indice').hide();
                $('#menu-expand').removeClass("biz-ex-menu-toggle-hide").addClass("biz-ex-menu-toggle-show");
            });

            $('#menu-expand').on("click", function() {
                $('#menu-expand').removeClass("biz-ex-menu-toggle-show").addClass("biz-ex-menu-toggle-hide");
                $('#indice').show();
                $('#menu-contract').removeClass("biz-ex-menu-toggle-hide").addClass("biz-ex-menu-toggle-show");
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
            
            // Cargar dashboard automáticamente al inicio
            loadDashboard();
        });

        // Función para toggle de secciones - simplificada
        function toggleSection(sectionName) {
            const content = document.getElementById(sectionName + '-content');
            const icon = document.getElementById(sectionName + '-icon');
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                icon.classList.remove('rotated');
            } else {
                content.classList.add('collapsed');
                icon.classList.add('rotated');
            }
        }

        // Función para cargar el dashboard
        function loadDashboard() {
            const iframe = document.getElementById('miIframe');
            const placeholder = document.getElementById('placeholder');
            
            iframe.src = 'dashboard.php';
            iframe.style.display = 'block';
            placeholder.style.display = 'none';
        }

        console.log('Portal Transformación Digital cargado correctamente');
    </script>
</body>
</html>