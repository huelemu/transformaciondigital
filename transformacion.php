<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

echo "Bienvenido, " . $_SESSION['user_name'];
?>
<a href="logout.php">Cerrar sesi�n</a>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/css/jquery/jquery.ui.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/bizagi-font.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/app.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/theme.css" type="text/css" />
    <link rel="stylesheet" href="libs/css/google-opensans.css"/>
    
    <script src="libs/js/app/jquery.min.js"></script>

    <title>TDI - SkyTel</title>

   
</head>
<body>

    <div id="content">
        <div id="indice">
        <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="location.reload()">
            <i class="biz-ex-logo-img"></i>
        </a><h1 class="biz-ex-title-process-jml">Herramientas:</h1>
        <ul class="nav-bar">
        <?php
            $directorio0 = "herramientas";
            $subdirectorios0 = [];

            // Abrir el directorio y recoger todos los subdirectorios en un array
            if (is_dir($directorio0)) {
                if ($dh = opendir($directorio0)) {
                    while (($subdirectorio0 = readdir($dh)) !== false) {
                        if ($subdirectorio0 != "." && $subdirectorio0 != "..") {
                            $subdirectorios0[] = $subdirectorio0;
                        }
                    }
                    closedir($dh);
                }
            }

            // Ordenar alfabéticamente el array de subdirectorios
            sort($subdirectorios0);

            // Generar la lista ordenada
            foreach ($subdirectorios0 as $subdirectorio0) {
                // Construimos la ruta completa al archivo index.html
                $ruta_index = "$directorio0/$subdirectorio0/index.html";
                echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio0</div></a></li>";
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
        <h1 class="biz-ex-title-process-jml">Capacitaciones:</h1>
        <ul class="nav-bar">
        <?php
            $directorio1 = "capacitaciones";
            $subdirectorios1 = [];

            // Abrir el directorio y recoger todos los subdirectorios en un array
            if (is_dir($directorio1)) {
                if ($dh = opendir($directorio1)) {
                    while (($subdirectorio1 = readdir($dh)) !== false) {
                        if ($subdirectorio1 != "." && $subdirectorio1 != "..") {
                            $subdirectorios1[] = $subdirectorio1;
                        }
                    }
                    closedir($dh);
                }
            }

            // Ordenar alfabéticamente el array de subdirectorios
            sort($subdirectorios1);

            // Generar la lista ordenada
            foreach ($subdirectorios1 as $subdirectorio1) {
                // Construimos la ruta completa al archivo index.html
                $ruta_index = "$directorio1/$subdirectorio1/index.html";
                echo "<li><a href='$ruta_index' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>$subdirectorio1</div></a></li>";
            }
        ?>
        <!-- <h1 class="biz-ex-title-process-jml">Oficina de Proyectos:</h1> -->
        <!-- <li><a href='https://app.powerbi.com/view?r=eyJrIjoiMjcxYTNhYjktZTQ5OC00Y2MyLWJkOTgtNDRhNTcyZTg4ZTE1IiwidCI6IjFmNTNjYTlkLTg1YzItNDcwYS1iYTFiLTY5YzExNTcwZTI0NyIsImMiOjR9' class='biz-ex-navigate'><div class='truncate-text biz-ex-menu'>Tablero PMO</div></a></li> -->

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
