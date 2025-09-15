<!-- <?php
// index.php - Página principal con autenticación
//require_once 'config.php';
//require_once 'utils.php';

// Verificar autenticación (redirige a login si no está autenticado)
//requireAuth();

// Log de acceso al dashboard
//Utils::logToFile("User accessed dashboard: " . $_SESSION['user']['email'], 'INFO');
// ?>
-->
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

    <title>Procesos SkyTel</title>

    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            height: 100vh;
        }
        header {
            width: 100%;
            padding: 10px;
            background-color: #f4f4f4;
            border-bottom: 1px solid #ccc;
            box-sizing: border-box;
        }
        #content {
            display: flex;
            flex: 1;
        }
        #indice {
            width: 300px;
            /* border-right: 1px solid #ccc; */
            padding: 5px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        #iframe-container {
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .placeholder {
            font-size: 24px;
            text-align: center;
            margin-top: 20%;
        }   
        #iframe-container .placeholder {
            display: none; /* Oculta el placeholder inicialmente */
        }
        #iframe-container #miIframe:loaded + .placeholder {
            display: block; /* Muestra el placeholder si el iframe no carga */
        }
      </style>
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

        <h1 class="biz-ex-title-process-jml">Videos:</h1>
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
  
        <h1 class="biz-ex-title-process-jml">Recursos Compartidos SkyTel:</h1>
        <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentaciones</div></a></li>
        <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Alineacion...</div></a></li>
        <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Mapa de Procesos</div></a></li>
        <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Contact Center</div></a></li>
        <li><a href='https://sistemagestion.skytel.tech' target='_blank' data-new-tab='true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Sistema de Gestion</div></a></li>


        <!-- <h1 class="biz-ex-title-process-jml">Ayuda Memoria:</h1> -->
        <!-- <li><a href='https://docs.google.com/presentation/d/11dTQCMk80yQFCJAR7RSSSNqE-TYukXfZmNjOTJy3g2o/edit?usp=sharing' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Transformacion</div></a></li> -->
        <!-- <li><a href='https://docs.google.com/presentation/d/1jQZMtX5CJsDozaEDyojRRZdXvOrPKN9v/edit?usp=sharing&ouid=101540677606614220156&rtpof=true&sd=true' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Presentacion Procesos</div></a></li> -->

        </ul> 
            <!-- Sección de usuario al final del menú -->
            <div class="user-section">
                <div class="user-info">
                    <?php if (isset($_SESSION['user']['picture']) && !empty($_SESSION['user']['picture'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="Avatar" class="user-avatar" style="background: none;">
                    <?php else: ?>
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario') ?></div>
                        <div class="user-email"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17,8L15.59,6.59L13.17,9.01L13.17,2L11.17,2L11.17,9.01L8.75,6.59L7.34,8L12.17,12.83L17,8Z"/>
                        <path d="M19,15V18C19,19.1 18.1,20 17,20H7C5.9,20 5,19.1 5,18V15H3V18C3,20.21 4.79,22 7,22H17C19.21,22 21,20.21 21,18V15H19Z"/>
                    </svg>
                    Cerrar Sesión
                </a>
            </div>
        </div>

        <div style="width:20px; border-right: 1px solid #ccc;">
            <img id="menu-contract" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-contract.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-hide" alt="">
            <img id="menu-expand" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-expand.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-show" alt="">
        </div>
        <div id="iframe-container">
            <div class="placeholder" id="placeholder">JML (Este texto se ocultará una vez que el iframe cargue)</div>
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
    });
</script>

</body>
</html>
