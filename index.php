<?php
// index.php - Portal SkyTel - Transformación Digital
session_start();

/**
 * Función para verificar si el usuario está autenticado
 * @return bool
 */
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['email']);
}

/**
 * Función para verificar si la sesión sigue válida
 * Expira a las 8 horas
 * @return bool
 */
function isSessionValid() {
    if (!isAuthenticated()) return false;

    $session_lifetime = 8 * 60 * 60; // 8 horas
    $login_time = $_SESSION['user']['login_time'] ?? 0;

    if (time() - $login_time > $session_lifetime) {
        session_unset();
        session_destroy();
        return false;
    }

    $_SESSION['user']['last_activity'] = time();
    return true;
}

// Redirigir al login si sesión no válida
if (!isSessionValid()) {
    header("Location: login.php?error=session_expired");
    exit;
}

$user = $_SESSION['user'];

/**
 * Función para listar subdirectorios y generar los elementos de menú
 * @param string $directorio
 * @param bool $withIndex - si se agrega 'index.html' al final del link
 */
function listarSubdirectorios($directorio, $withIndex = false) {
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
        $ruta = "$directorio/$subdirectorio";
        if ($withIndex) $ruta .= "/index.html";
        echo "<li><a href='$ruta' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal SkyTel - Transformación Digital</title>

    <!-- CSS -->
    <link rel="stylesheet" href="libs/css/jquery/jquery.ui.css">
    <link rel="stylesheet" href="libs/css/bizagi-font.css">
    <link rel="stylesheet" href="libs/css/app.css">
    <link href="libs/css/google-opensans.css" rel="stylesheet">

    <script src="libs/js/app/jquery.min.js"></script>

    <style>
        /* ===============================
           LAYOUT GENERAL
        ================================ */
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
            overflow: hidden;
        }

        /* ===============================
           MENU LATERAL
        ================================ */
        #indice {
            width: 300px;
            padding: 5px;
            box-sizing: border-box;
            overflow-y: auto;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
        }

        /* Scroll personalizado */
        #indice::-webkit-scrollbar { width: 8px; }
        #indice::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        #indice::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        #indice::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

        .menu-container { flex: 1; overflow-y: auto; padding-bottom: 10px; }

        /* Estilos de usuario */
        .user-section { padding: 15px 12px; border-top: 2px solid #e9ecef; background: #fff; margin-top: auto; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex-shrink: 0; }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #e9ecef; object-fit: cover; }
        .user-avatar-fallback { background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; }
        .user-details { flex: 1; min-width: 0; }
        .user-name { font-weight: 600; font-size: 13px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 11px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .logout-btn { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 8px 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; color: #6c757d; text-decoration: none; font-size: 12px; font-weight: 500; transition: all 0.2s ease; }
        .logout-btn:hover { background: #e9ecef; border-color: #adb5bd; color: #495057; text-decoration: none; transform: translateY(-1px); }

        /* ===============================
           IFRAME CENTRAL
        ================================ */
        #iframe-container { flex: 1; position: relative; overflow: hidden; }
        iframe { width: 100%; height: 100%; border: none; }
        .placeholder { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 18px; color: #666; text-align: center; }

        /* ===============================
           MENÚ COLAPSABLE
        ================================ */
        .biz-ex-title-process-jml { font-size: 14px; font-weight: 600; color: #495057; margin: 20px 0 10px 0; padding: 8px 12px; background: #e9ecef; border-radius: 6px; border-left: 4px solid #667eea; cursor: pointer; }
        .nav-bar { list-style: none; padding: 0; margin: 0 0 15px 0; }
        .nav-bar li { margin-bottom: 5px; }
        .biz-ex-navigate { display: block; padding: 10px 12px; background: #fff; border: 1px solid #e9ecef; border-radius: 6px; text-decoration: none; color: #495057; transition: all 0.3s ease; font-size: 13px; }
        .biz-ex-navigate:hover { background: #f8f9fa; border-color: #667eea; transform: translateX(3px); color: #495057; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15); }
        .truncate-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .collapsible-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; opacity: 0; }
        .collapsible-content.expanded { max-height: 1000px; opacity: 1; }
        .toggle-icon { float: right; transition: transform 0.3s ease; font-size: 12px; margin-top: 2px; }
        .toggle-icon.rotated { transform: rotate(-90deg); }

        /* Responsive */
        @media (max-width: 768px) { #indice { width: 250px; } }
        @media (max-width: 480px) { #indice { width: 200px; } .biz-ex-title-process-jml { font-size: 12px; padding: 6px 8px; } }
    </style>
</head>
<body>
<div id="content">

    <!-- ===============================
         MENÚ LATERAL
    ================================ -->
    <div id="indice">
        <!-- Logo y Dashboard -->
        <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="loadDashboard()">
            <i class="biz-ex-logo-img"></i>
            <div style="margin-top: 10px; font-weight: 600; color: #667eea;">Portal SkyTel</div>
        </a>

        <div class="dashboard-home">
            <a href="#" class="biz-ex-navigate dashboard-link" onclick="loadDashboard()">
                <div class="truncate-text biz-ex-menu">🏠 Dashboard Principal</div>
            </a>
        </div>

        <!-- Secciones dinámicas -->
        <div class="menu-section">
            <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('herramientas')">
                🛠️ Herramientas <span class="toggle-icon rotated" id="herramientas-icon">▼</span>
            </h1>
            <ul class="nav-bar collapsible-content" id="herramientas-content">
                <?php listarSubdirectorios("herramientas"); ?>
            </ul>
        </div>

        <div class="menu-section">
            <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('procesos')">
                ⚙️ Procesos Bizagi <span class="toggle-icon rotated" id="procesos-icon">▼</span>
            </h1>
            <ul class="nav-bar collapsible-content" id="procesos-content">
                <?php listarSubdirectorios("procesos", true); ?>
            </ul>
        </div>

        <div class="menu-section">
            <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('capacitaciones')">
                📚 Capacitaciones <span class="toggle-icon" id="capacitaciones-icon">▼</span>
            </h1>
            <ul class="nav-bar collapsible-content" id="capacitaciones-content">
                <?php listarSubdirectorios("capacitaciones", true); ?>
            </ul>
        </div>

        <div class="menu-section">
            <h1 class="biz-ex-title-process-jml collapsible" onclick="toggleSection('recursos')">
                🌐 Recursos SkyTel <span class="toggle-icon" id="recursos-icon">▼</span>
            </h1>
            <ul class="nav-bar collapsible-content" id="recursos-content">
                <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentaciones</div></a></li>
                <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Alineación...</div></a></li>
                <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Mapa de Procesos</div></a></li>
                <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Contact Center</div></a></li>
                <li><a href='https://sistemagestion.skytel.tech' target='_blank' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Sistema de Gestión</div></a></li>
            </ul>
        </div>

        <!-- ===============================
             SECCIÓN USUARIO
        ================================ -->
        <div class="user-section">
            <div class="user-info">
                <?php if (!empty($user['picture'])): ?>
                    <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Avatar" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar user-avatar-fallback"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                <?php endif; ?>
                <div class="user-details">
                    <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Usuario') ?></div>
                    <div class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">Cerrar Sesión</a>
        </div>
    </div>

    <!-- ===============================
         IFRAME PRINCIPAL
    ================================ -->
    <div id="iframe-container">
        <div class="placeholder" id="placeholder">
            <h3>🎯 Portal SkyTel</h3>
            <p>Selecciona cualquier herramienta, proceso o capacitación del menú lateral</p>
        </div>
        <iframe id="miIframe" title="Portal de Gestión"></iframe>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const iframe = document.getElementById('miIframe');
        const placeholder = document.getElementById('placeholder');

        // ===============================
        // Abrir enlaces en el iframe
        // ===============================
        document.querySelectorAll('#indice a').forEach(a => {
            a.addEventListener('click', e => {
                if (a.dataset.newTab === "true" || a.classList.contains('logout-btn')) return;

                e.preventDefault();
                iframe.src = a.href;
                iframe.style.display = 'block';
                placeholder.style.display = 'none';
            });
        });

        // ===============================
        // Colapsables
        // ===============================
        window.toggleSection = function(sectionName) {
            const content = document.getElementById(sectionName + '-content');
            const icon = document.getElementById(sectionName + '-icon');
            content.classList.toggle('expanded');
            icon.classList.toggle('rotated');
        }

        // ===============================
        // Cargar dashboard por defecto
        // ===============================
        window.loadDashboard = function() {
            iframe.src = 'dashboard.php';
            iframe.style.display = 'block';
            placeholder.style.display = 'none';
        }

        loadDashboard();

        // ===============================
        // Auto-logout por inactividad 30 min
        // ===============================
        let inactivityTimer;
        const INACTIVITY_TIME = 30 * 60 * 1000;
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if (confirm('Tu sesión expirará por inactividad.\n¿Deseas mantener la sesión activa?')) {
                    resetInactivityTimer();
                } else {
                    window.location.href = 'logout.php';
                }
            }, INACTIVITY_TIME);
        }
        ['mousedown','mousemove','keypress','scroll','touchstart','click'].forEach(ev => document.addEventListener(ev, resetInactivityTimer, true));
        resetInactivityTimer();
    });
</script>
</body>
</html>
