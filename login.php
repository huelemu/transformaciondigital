<?php
// Verificar autenticación
require_once 'config.php';
requireAuth();
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
    <title>Procesos SkyTel</title>
    <style>
        /* 🔹 Mejora la estructura del menú lateral */
        #indice {
            width: 250px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            height: 100vh;
            overflow-y: auto;
            padding: 10px;
        }
        #indice h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 8px;
            padding-left: 8px;
            color: #333;
            text-transform: uppercase;
        }
        #indice ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        #indice li {
            margin-bottom: 5px;
        }
        #indice a {
            display: block;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        #indice a:hover {
            background: #e6f0ff;
            color: #0056b3;
        }
        .user-section {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #007bff;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .user-details {
            font-size: 12px;
        }
        .logout-btn {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            color: #c00;
            text-decoration: none;
        }
        .logout-btn:hover {
            text-decoration: underline;
        }
        /* 🔹 Contenedor principal */
        #content {
            display: flex;
            height: 100vh;
        }
        #iframe-container {
            flex: 1;
            padding: 10px;
        }
        #miIframe {
            width: 100%;
            height: calc(100vh - 20px);
            border: none;
        }
    </style>
</head>
<body>
    <div id="content">
        <!-- 🔹 Menú lateral -->
        <div id="indice">
            <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="location.reload()">
                <i class="biz-ex-logo-img"></i>
            </a>

            <h1>Herramientas</h1>
            <ul>
                <?php
                $directorio = "herramientas";
                $subdirectorios = [];
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
                sort($subdirectorios);
                foreach ($subdirectorios as $subdirectorio) {
                    $ruta_index = "$directorio/$subdirectorio/index.html";
                    echo "<li><a href='$ruta_index' class='biz-ex-navigate'>$subdirectorio</a></li>";
                }
                ?>
            </ul>
                
                <h1>Procesos</h1>
                <ul>
                <?php
                $directorio = "procesos";
                $subdirectorios = [];
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
                sort($subdirectorios);
                foreach ($subdirectorios as $subdirectorio) {
                    $ruta_index = "$directorio/$subdirectorio/index.html";
                    echo "<li><a href='$ruta_index' class='biz-ex-navigate'>$subdirectorio</a></li>";
                }
                ?>
            </ul>

            <h1>Capacitaciones</h1>
            <ul>
                <?php
                $directorio_capacitaciones = "capacitaciones";
                if (is_dir($directorio_capacitaciones)) {
                    $subdirectorios_cap = [];
                    if ($dh = opendir($directorio_capacitaciones)) {
                        while (($subdirectorio = readdir($dh)) !== false) {
                            if ($subdirectorio != "." && $subdirectorio != "..") {
                                $subdirectorios_cap[] = $subdirectorio;
                            }
                        }
                        closedir($dh);
                    }
                    sort($subdirectorios_cap);
                    foreach ($subdirectorios_cap as $subdirectorio) {
                        $ruta_index = "$directorio_capacitaciones/$subdirectorio/index.html";
                        echo "<li><a href='$ruta_index' class='biz-ex-navigate'>$subdirectorio</a></li>";
                    }
                } else {
                    echo "<li><a href='#'>Capacitaciones (próximamente)</a></li>";
                }
                ?>
            </ul>

            <h1>Recursos Compartidos</h1>
            <ul>
                <li><a href='https://sites.google.com/skytel.tech/gws/multimedia?authuser=0' target='_blank' data-new-tab='true'>Presentaciones</a></li>
                <li><a href='https://docs.google.com/spreadsheets/d/1Q5wFyJzWCCa-pXd2-4Ij6th8qyEGAr9Crnam8HvCjYQ/edit?gid=0#gid=0'>Alineacion...</a></li>
                <li><a href='https://docs.google.com/spreadsheets/d/1sfQt0OiVdjXrblLBhWgSL0CmLk_MzaVKON6xh6nGbNk/edit?gid=1681975588#gid=1681975588'>Mapa de Procesos</a></li>
                <li><a href='https://skytel.atlassian.net/servicedesk/customer/portal/24/article/1067614280' target='_blank' data-new-tab='true'>Contact Center</a></li>
                <li><a href='https://sistemagestion.skytel.tech' target='_blank' data-new-tab='true'>Sistema de Gestion</a></li>
            </ul>

            <!-- 🔹 Usuario -->
            <div class="user-section">
                <div class="user-info">
                    <?php if (isset($_SESSION['user']['picture']) && !empty($_SESSION['user']['picture'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="Avatar" class="user-avatar" style="background: none;">
                    <?php else: ?>
                        <div class="user-avatar"><?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario') ?></div>
                        <div class="user-email"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">Cerrar Sesión</a>
            </div>
        </div>

        <!-- 🔹 Contenido principal -->
        <div id="iframe-container">
            <div class="placeholder" id="placeholder">JML (Este texto se ocultará una vez que el iframe cargue)</div>
            <iframe id="miIframe" title="Portal de Gestion" src="https://pgoyn.skytel.com.ar/" allowFullScreen></iframe>
        </div>
    </div>
</body>
</html>
