<?php
// index.php - Página principal con autenticación
require_once 'config.php';
require_once 'utils.php';

// Verificar autenticación (redirige a login si no está autenticado)
requireAuth();

// Log de acceso al dashboard
Utils::logToFile("User accessed dashboard: " . $_SESSION['user']['email'], 'INFO');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/css/jquery/jquery.ui.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/bizagi-font.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/app.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/portal-styles.css" type="text/css" />
    <link href="libs/css/google-opensans.css" rel="stylesheet">
    <script src="libs/js/app/jquery.min.js"></script>

    <title>Transformación Digital - SkyTel</title>
</head>
<body>
    <div id="content">
        <div id="indice">
            <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="location.reload()">
                <i class="biz-ex-logo-img"></i>
            </a>

            <h1 class="biz-ex-title-process-jml">Herramientas:</h1>
            <ul class="nav-bar">
            <?php
                $directorio = "herramientas";
                $subdirectorios = [];

                // Abrir el directorio y recoger todos los subdirectorios en un array
                if (is_dir($directorio)) {
                    if ($dh = opendir($directorio)) {
                        while (($subdirectorio = readdir($dh)) !== false) {
                            if ($subdirectorio != "." && $subdirectorio != "..") {
                                $subdirectorios[] = $subdirectorio;
                            }
                        }
                        closedir($dh);
                    }
                }

                // Ordenar alfabéticamente el array de subdirectorios
                sort($subdirectorios);

                // Generar la lista ordenada
                foreach ($subdirectorios as $subdirectorio) {
                    // Construimos la ruta completa al archivo index.html
                    $ruta_index = "$directorio/$subdirectorio/index.html";
                    echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                }
            ?>
            </ul>

            <h1 class="biz-ex-title-process-jml">Procesos:</h1>
            <ul class="nav-bar">
            <?php
                $directorio = "procesos";
                $subdirectorios = [];

                // Abrir el directorio y recoger todos los subdirectorios en un array
                if (is_dir($directorio)) {
                    if ($dh = opendir($directorio)) {
                        while (($subdirectorio = readdir($dh)) !== false) {
                            if ($subdirectorio != "." && $subdirectorio != "..") {
                                $subdirectorios[] = $subdirectorio;
                            }
                        }
                        closedir($dh);
                    }
                }

                // Ordenar alfabéticamente el array de subdirectorios
                sort($subdirectorios);

                // Generar la lista ordenada
                foreach ($subdirectorios as $subdirectorio) {
                    // Construimos la ruta completa al archivo index.html
                    $ruta_index = "$directorio/$subdirectorio/index.html";
                    echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                }
            ?>
            </ul>

            <h1 class="biz-ex-title-process-jml">Video - Cursos:</h1>
            <ul class="nav-bar">
            <?php
                $directorio = "capacitaciones";
                $subdirectorios = [];

                // Abrir el directorio y recoger todos los subdirectorios en un array
                if (is_dir($directorio)) {
                    if ($dh = opendir($directorio)) {
                        while (($subdirectorio = readdir($dh)) !== false) {
                            if ($subdirectorio != "." && $subdirectorio != "..") {
                                $subdirectorios[] = $subdirectorio;
                            }
                        }
                        closedir($dh);
                    }
                }

                // Ordenar alfabéticamente el array de subdirectorios
                sort($subdirectorios);

                // Generar la lista ordenada
                foreach ($subdirectorios as $subdirectorio) {
                    // Construimos la ruta completa al archivo index.html
                    $ruta_index = "$directorio/$subdirectorio/index.html";
                    echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                }
            ?>
            </ul>

            <h1 class="biz-ex-title-process-jml">Recursos Compartidos SkyTel:</h1>
            <ul class="nav-bar">
                <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentaciones</div></a></li>
                <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Alineacion...</div></a></li>
                <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Mapa de Procesos</div></a></li>
                <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Contact Center</div></a></li>
                <li><a href='https://sistemagestion.skytel.tech' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Sistema de Gestion</div></a></li>
            </ul>

            <!-- Sección de usuario al final del menú -->
            <div class="user-section">
                <div class="user-info">
                    <?php if (isset($_SESSION['user']['picture']) && !empty($_SESSION['user']['picture'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="Avatar" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar user-avatar-fallback">
                            <?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario') ?></div>
                        <div class="user-email"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                    Cerrar Sesión
                </a>
            </div>
        </div>

        <div style="width:20px; border-right: 1px solid #ccc;">
            <img id="menu-contract" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-contract.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-hide" alt="">
            <img id="menu-expand" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-expand.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-show" alt="">
        </div>

        <div id="iframe-container">
            <div class="placeholder" id="placeholder">Portal de Transformación Digital SkyTel</div>
            <iframe id="miIframe" title="Portal de Gestion" width="1140" height="541.25" src="https://pgoyn.skytel.com.ar/" frameborder="0" allowFullScreen="true"></iframe>
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
                if (enlace.getAttribute('data-new-tab') === 'true') {
                    // Si el enlace tiene el atributo data-new-tab, no hacemos nada para permitir la apertura en una nueva pestaña
                    return;
                }

                // Ignorar clics en el botón de logout
                if (enlace.classList.contains('logout-btn')) {
                    return;
                }

                event.preventDefault();
                const url = enlace.href;
                iframe.src = url;
                iframe.style.display = 'block';
                placeholder.style.display = 'none';
            });
        });

        $('#menu-contract').on("click", function() {
            $('#menu-contract').removeClass("biz-ex-menu-toggle-hide").addClass("biz-ex-menu-toggle-show");
            $('#indice').hide();
            $('#menu-expand').removeClass("biz-ex-menu-toggle-show").addClass("biz-ex-menu-toggle-hide");
        });

        $('#menu-expand').on("click", function() {           
            $('#menu-expand').removeClass("biz-ex-menu-toggle-hide").addClass("biz-ex-menu-toggle-show");
            $('#indice').show();
            $('#menu-contract').removeClass("biz-ex-menu-toggle-show").addClass("biz-ex-menu-toggle-hide");
        });

        // Auto-logout por inactividad
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
                time = setTimeout(logout, <?= SESSION_LIFETIME * 1000 ?>);
            }
        };

        inactivityTime();
    });
    </script>
</body>
</html>