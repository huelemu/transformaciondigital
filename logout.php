<?php
// logout.php - Cerrar sesión
require_once 'config.php';

// Destruir toda la información de la sesión
session_destroy();

// Redirigir al login
header('Location: login.php?logged_out=1');
exit();
?>

<?php
// index.php - Página principal (actualizada con autenticación)
require_once 'config.php';

// Verificar autenticación
requireAuth();

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

    <title>Procesos SkyTel</title>

    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }
        
        .user-email {
            font-size: 12px;
            opacity: 0.8;
            margin: 0;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s ease;
            margin-left: 15px;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        #content {
            display: flex;
            flex: 1;
        }
        
        #indice {
            width: 300px;
            padding: 5px;
            box-sizing: border-box;
            overflow-y: auto;
            border-right: 1px solid #e0e0e0;
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
            color: #666;
        }
        
        #iframe-container .placeholder {
            display: none;
        }
        
        .welcome-message {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
        }
        
        .welcome-message h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        
        .welcome-message p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <i class="biz-ex-logo-img" style="font-size: 24px;"></i>
            <h1 class="header-title">Portal de Procesos SkyTel</h1>
        </div>
        
        <div class="user-info">
            <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Avatar" class="user-avatar">
            <div class="user-details">
                <p class="user-name"><?php echo htmlspecialchars($user['name']); ?></p>
                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>

    <div id="content">
        <div id="indice">
            <div class="welcome-message">
                <h3>¡Bienvenido!</h3>
                <p>Acceso autorizado para dominio <?php echo htmlspecialchars($user['domain']); ?></p>
            </div>
            
            <a href="#" class="biz-ex-navigate biz-ex-logo-navigate" onclick="location.reload()">
                <i class="biz-ex-logo-img"></i>
            </a>
            
            <h1 class="biz-ex-title-process-jml">Procesos:</h1>
            <ul class="nav-bar">
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
        </div>

        <div style="width:20px; border-right: 1px solid #ccc;">
            <img id="menu-contract" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-contract.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-hide" alt="">
            <img id="menu-expand" style="float:right; margin-left:2px; margin-right:2px;" src="libs/img/bzg-panel-expand.svg" class="biz-ex-svg-icon biz-ex-svg-toggle biz-ex-menu-visible-toggle biz-ex-menu-toggle-show" alt="">
        </div>
        
        <div id="iframe-container">
            <div class="placeholder" id="placeholder">Selecciona un proceso o recurso del menú lateral</div>
            <iframe id="miIframe" title="Portal de Gestion" width="1140" height="541.25" src="https://pgoyn.skytel.com.ar/" frameborder="0" allowFullScreen="true"></iframe>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const enlaces = document.querySelectorAll('#indice a');
        const iframe = document.getElementById('miIframe');
        const placeholder = document.getElementById('placeholder');
      
        enlaces.forEach(enlace => {
            enlace.addEventListener('click', (event) => {
                if (enlace.getAttribute('data-new-tab') === 'true') {
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

    <!-- Dialogflow Messenger -->
    <link rel="stylesheet" href="https://www.gstatic.com/dialogflow-console/fast/df-messenger/prod/v1/themes/df-messenger-default.css">
    <script src="https://www.gstatic.com/dialogflow-console/fast/df-messenger/prod/v1/df-messenger.js"></script>
    <df-messenger
      location="us-central1"
      project-id="itauuy"
      agent-id="c3ceb5e2-6c8a-42d8-a615-fa315ebe01d6"
      language-code="es"
      max-query-length="-1">
      <df-messenger-chat-bubble chat-title="Lazy"></df-messenger-chat-bubble>
    </df-messenger>
    <style>
      df-messenger {
        z-index: 999;
        position: fixed;
        --df-messenger-font-color: #000;
        --df-messenger-font-family: Google Sans;
        --df-messenger-chat-background: #f3f6fc;
        --df-messenger-chat-window-height: 700px;
        --df-messenger-chat-window-width: 400px;
        --df-messenger-message-user-background: #d3e3fd;
        --df-messenger-message-bot-background: #fff;
        bottom: 16px;
        right: 16px;
      }
    </style>
</body>
</html>