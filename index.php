<?php
// index.php - Página principal con autenticación
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

    <title>Portal SkyTel - Procesos y Herramientas</title>
</head>
<body>
    <!-- Header con información del usuario -->
    <div class="user-header">
        <div class="user-info">
            <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Avatar" class="user-avatar">
            <div class="user-details">
                <h3>
                    <?= htmlspecialchars($user['name']) ?>
                    <?php if ($is_admin): ?>
                        <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                </h3>
                <p><?= htmlspecialchars($user['email']) ?> | <?= htmlspecialchars($user_domain) ?></p>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div id="content">
        <!-- Panel izquierdo con navegación -->
        <div id="indice">
            <!-- Área de navegación scrolleable -->
            <div class="navigation-area">
                <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="location.reload()">
                    <i class="biz-ex-logo-img"></i>
                    <div class="portal-title">Portal SkyTel</div>
                </a>

                <h1 class="biz-ex-title-process-jml">Procesos:</h1>
                <ul class="nav-bar">
                <?php
                    $directorio = "procesos";
                    $subdirectorios = [];

                    // Recoger subdirectorios
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

                    // Ordenar alfabéticamente
                    sort($subdirectorios);

                    // Generar la lista ordenada
                    foreach ($subdirectorios as $subdirectorio) {
                        $ruta_index = "$directorio/$subdirectorio/index.html";
                        echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio</div></a></li>";
                    }
                ?>
                </ul>

                <h1 class="biz-ex-title-process-jml">Recursos Compartidos SkyTel:</h1>
                <ul class="nav-bar">
                    <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentaciones</div></a></li>
                    <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Alineación...</div></a></li>
                    <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Recursos Técnicos</div></a></li>
                    <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Contact Center</div></a></li>
                    <li><a href='https://sistemagestion.skytel.tech' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Sistema de Gestión</div></a></li>
                </ul>
            </div>

            <!-- Botones en la parte inferior -->
            <div class="bottom-actions">
                <a href="herramientas/" class="tools-access-btn">
                    🛠️ Herramientas de Transformación
                </a>
                <?php if ($is_admin): ?>
                    <a href="admin.php" class="admin-access-btn">
                        ⚙️ Panel de Administración
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Controles de visibilidad -->
        <div class="visibility-controls">
            <img id="menu-contract" src="libs/img/bzg-panel-contract.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-hide" alt="">
            <img id="menu-expand" src="libs/img/bzg-panel-expand.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-show" alt="">
        </div>

        <!-- Área del iframe -->
        <div id="iframe-container">
            <div class="placeholder" id="placeholder">
                Portal de Transformación Digital SkyTel<br>
                <small>Selecciona un proceso o herramienta del menú para comenzar</small>
            </div>
            <iframe id="miIframe" title="Portal de Gestión" width="1140" height="541.25" frameborder="0" allowFullScreen="true"></iframe>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Manejo de enlaces
        const enlaces = document.querySelectorAll('#indice a');
        const iframe = document.getElementById('miIframe');
        const placeholder = document.getElementById('placeholder');
      
        enlaces.forEach(enlace => {
            enlace.addEventListener('click', (event) => {
                if (enlace.getAttribute('data-new-tab') === 'true') {
                    return; // Permitir apertura en nueva pestaña
                }

                if (enlace.href && !enlace.href.includes('#') && !enlace.href.includes('herramientas/') && !enlace.href.includes('admin.php')) {
                    event.preventDefault();
                    iframe.src = enlace.href;
                    iframe.style.display = 'block';
                    placeholder.style.display = 'none';
                }
            });
        });

        // Controles de visibilidad del menú
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

        // Verificar estado de sesión periódicamente
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
        }, 300000); // Cada 5 minutos
    });
    </script>
</body>
</html>