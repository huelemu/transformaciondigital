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
    <link rel="stylesheet" href="libs/css/custom.css" type="text/css" />
    <link href="libs/css/google-opensans.css" rel="stylesheet">
    <script src="libs/js/app/jquery.min.js"></script>

    <title>Portal SkyTel - Transformación Digital</title>

    <style>
        
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

            <!-- Capacitaciones -->
            <div class="menu-section">
                <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('capacitaciones')">
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
                   <!--  <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentaciones</div></a></li> 
                    <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Alineación...</div></a></li>
                    <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Mapa de Procesos</div></a></li>
                    <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Contact Center</div></a></li> -->
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