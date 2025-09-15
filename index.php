<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay usuario logueado → mandar a login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
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

    <title>Transformación Digital - SkyTel</title>

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
 <h1>Bienvenido <?php echo htmlspecialchars($_SESSION['usuario']); ?></h1>
    <nav>
        <a href="logout.php">Cerrar sesión</a>
</nav>

    <div id="content">
        <div id="indice">
        <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="location.reload()">
            <i class="biz-ex-logo-img"></i>
        </a>
        <!-- Cramos el apartado del menu para Las herramientas -->
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

        <!-- Cramos el apartado del menu para Procesos -->
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
  
        <!-- Cramos el apartado del menu para Video, Cursos -->
         <h1 class="biz-ex-title-process-jml">Videos, Cursos:</h1>
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


        </ul> 

        <!-- Botón de Logout -->
        <div style="margin-top:20px; text-align:center;">
            <a href="logout.php" class="biz-ex-navigate">
                <div class="truncate-text biz-ex-menu">🚪 Cerrar Sesión</div>
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