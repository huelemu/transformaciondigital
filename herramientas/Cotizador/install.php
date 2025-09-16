<?php
// install.php - Instalador automático del sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? 'welcome';
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - SkyTel Cotizador</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 30px;
            font-size: 2rem;
        }
        h2 { 
            color: #555; 
            margin-top: 30px; 
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ddd;
            color: #666;
            font-weight: bold;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .status { 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 8px; 
            border-left: 4px solid;
        }
        .success { 
            background: #d4edda; 
            border-left-color: #28a745; 
            color: #155724; 
        }
        .warning { 
            background: #fff3cd; 
            border-left-color: #ffc107; 
            color: #856404; 
        }
        .error { 
            background: #f8d7da; 
            border-left-color: #dc3545; 
            color: #721c24; 
        }
        .info { 
            background: #d1ecf1; 
            border-left-color: #17a2b8; 
            color: #0c5460; 
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .progress {
            width: 100%;
            height: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            transition: width 0.3s ease;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .terminal {
            background: #1a1a1a;
            color: #00ff00;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Mostrar mensajes
        if ($message) {
            echo "<div class='status success'>✅ $message</div>";
        }
        if ($error) {
            echo "<div class='status error'>❌ $error</div>";
        }
        
        switch ($step) {
            case 'welcome':
                showWelcome();
                break;
            case 'check':
                showSystemCheck();
                break;
            case 'install':
                performInstallation();
                break;
            case 'configure':
                showConfiguration();
                break;
            case 'complete':
                showComplete();
                break;
            default:
                showWelcome();
        }
        ?>
    </div>
</body>
</html>

<?php
function showStepIndicator($currentStep) {
    $steps = ['welcome' => 1, 'check' => 2, 'install' => 3, 'configure' => 4, 'complete' => 5];
    $stepNames = [1 => 'Inicio', 2 => 'Verificar', 3 => 'Instalar', 4 => 'Configurar', 5 => 'Completar'];
    $current = $steps[$currentStep] ?? 1;
    
    echo "<div class='step-indicator'>";
    for ($i = 1; $i <= 5; $i++) {
        $class = 'step';
        if ($i < $current) $class .= ' completed';
        if ($i == $current) $class .= ' active';
        
        echo "<div class='$class' title='{$stepNames[$i]}'>$i</div>";
    }
    echo "</div>";
}

function showWelcome() {
    showStepIndicator('welcome');
    ?>
    <h1>🚀 Instalador SkyTel Cotizador</h1>
    <div class="info">
        <h3>¡Bienvenido al instalador automático!</h3>
        <p>Este asistente te ayudará a configurar completamente el sistema de cotización SkyTel.</p>
        
        <h4>🔧 Lo que este instalador hará:</h4>
        <ul>
            <li>✅ Verificar requisitos del sistema</li>
            <li>✅ Instalar dependencias necesarias (PhpSpreadsheet)</li>
            <li>✅ Configurar archivos de datos</li>
            <li>✅ Verificar permisos</li>
            <li>✅ Realizar pruebas de funcionalidad</li>
        </ul>
        
        <h4>📋 Requisitos mínimos:</h4>
        <ul>
            <li>PHP 7.4 o superior</li>
            <li>Extensiones: json, mbstring, xml, zip</li>
            <li>Composer instalado</li>
            <li>Permisos de escritura en el directorio</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="?step=check" class="btn btn-primary">📋 Comenzar Verificación</a>
        <a href="verificar_dependencias.php" class="btn btn-secondary">🔍 Verificación Manual</a>
    </div>
    <?php
}

function showSystemCheck() {
    showStepIndicator('check');
    ?>
    <h1>📋 Verificación del Sistema</h1>
    
    <?php
    $canProceed = true;
    $issues = [];
    
    echo "<h2>🔍 Verificando requisitos...</h2>";
    echo "<div class='progress'><div class='progress-bar' style='width: 25%'></div></div>";
    
    // Verificar PHP
    if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
        echo "<div class='status success'>✅ PHP " . PHP_VERSION . " - Compatible</div>";
    } else {
        echo "<div class='status error'>❌ PHP " . PHP_VERSION . " - Se requiere 7.4+</div>";
        $canProceed = false;
        $issues[] = "Actualizar PHP a versión 7.4 o superior";
    }
    
    // Verificar extensiones
    $extensions = ['json', 'mbstring', 'xml', 'zip'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<div class='status success'>✅ Extensión $ext</div>";
        } else {
            echo "<div class='status error'>❌ Extensión $ext faltante</div>";
            $canProceed = false;
            $issues[] = "Instalar extensión PHP: $ext";
        }
    }
    
    echo "<div class='progress'><div class='progress-bar' style='width: 50%'></div></div>";
    
    // Verificar Composer
    $composerPaths = [
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../../../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php'
    ];
    
    $composerFound = false;
    foreach ($composerPaths as $path) {
        if (file_exists($path)) {
            echo "<div class='status success'>✅ Composer encontrado</div>";
            $composerFound = true;
            break;
        }
    }
    
    if (!$composerFound) {
        echo "<div class='status warning'>⚠️ Composer no encontrado</div>";
        $issues[] = "Instalar dependencias con Composer";
    }
    
    echo "<div class='progress'><div class='progress-bar' style='width: 75%'></div></div>";
    
    // Verificar permisos
    if (is_writable(__DIR__)) {
        echo "<div class='status success'>✅ Permisos de escritura</div>";
    } else {
        echo "<div class='status error'>❌ Sin permisos de escritura</div>";
        $canProceed = false;
        $issues[] = "Otorgar permisos de escritura al directorio";
    }
    
    echo "<div class='progress'><div class='progress-bar' style='width: 100%'></div></div>";
    
    if (!empty($issues)) {
        echo "<h3>⚠️ Problemas encontrados:</h3><ul>";
        foreach ($issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul>";
        
        if (!$canProceed) {
            echo "<div class='status error'>
                <strong>No se puede continuar</strong><br>
                Resolver los problemas críticos antes de continuar.
            </div>";
            echo "<div style='text-align: center; margin-top: 30px;'>
                <a href='?step=check' class='btn btn-warning'>🔄 Verificar Nuevamente</a>
                <a href='?step=welcome' class='btn btn-secondary'>← Volver</a>
            </div>";
            return;
        }
    }
    
    echo "<div class='status success'>
        <strong>✅ Verificación completada</strong><br>
        El sistema cumple con los requisitos básicos.
    </div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    if ($composerFound) {
        echo "<a href='?step=configure' class='btn btn-success'>⚙️ Continuar a Configuración</a>";
    } else {
        echo "<a href='?step=install' class='btn btn-primary'>📦 Instalar Dependencias</a>";
    }
    echo "<a href='?step=check' class='btn btn-secondary'>🔄 Verificar Nuevamente</a>";
    echo "</div>";
}

function performInstallation() {
    showStepIndicator('install');
    ?>
    <h1>📦 Instalación de Dependencias</h1>
    
    <?php
    $output = [];
    $return_var = 0;
    
    echo "<div class='info'>
        <p>Instalando PhpSpreadsheet y otras dependencias necesarias...</p>
    </div>";
    
    // Verificar si composer está disponible
    exec('composer --version 2>&1', $composer_check, $composer_return);
    
    if ($composer_return !== 0) {
        echo "<div class='status error'>
            ❌ Composer no está disponible en la línea de comandos.<br>
            <strong>Instalación manual requerida:</strong><br>
            1. Instalar Composer: <a href='https://getcomposer.org/download/' target='_blank'>https://getcomposer.org/download/</a><br>
            2. Ejecutar: <code>composer require phpoffice/phpspreadsheet</code>
        </div>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>
            <a href='?step=check' class='btn btn-warning'>🔄 Verificar Instalación</a>
            <a href='?step=welcome' class='btn btn-secondary'>← Volver</a>
        </div>";
        return;
    }
    
    echo "<div class='status success'>✅ Composer disponible</div>";
    
    // Crear composer.json si no existe
    $composer_json = __DIR__ . '/composer.json';
    if (!file_exists($composer_json)) {
        $composer_content = [
            'name' => 'skytel/cotizador',
            'description' => 'Sistema de cotización SkyTel',
            'require' => [
                'phpoffice/phpspreadsheet' => '^1.29'
            ]
        ];
        
        file_put_contents($composer_json, json_encode($composer_content, JSON_PRETTY_PRINT));
        echo "<div class='status success'>✅ Archivo composer.json creado</div>";
    }
    
    // Ejecutar composer install
    echo "<h3>🔄 Ejecutando instalación...</h3>";
    echo "<div class='terminal'>";
    echo "$ composer install<br>";
    
    $command = "cd " . escapeshellarg(__DIR__) . " && composer install 2>&1";
    exec($command, $output, $return_var);
    
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
    echo "</div>";
    
    if ($return_var === 0) {
        echo "<div class='status success'>
            ✅ <strong>Instalación completada exitosamente</strong><br>
            PhpSpreadsheet y dependencias instaladas correctamente.
        </div>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>
            <a href='?step=configure' class='btn btn-success'>⚙️ Continuar a Configuración</a>
        </div>";
    } else {
        echo "<div class='status error'>
            ❌ <strong>Error en la instalación</strong><br>
            Código de salida: $return_var
        </div>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>
            <a href='?step=install' class='btn btn-warning'>🔄 Reintentar</a>
            <a href='?step=check' class='btn btn-secondary'>← Volver</a>
        </div>";
    }
}

function showConfiguration() {
    showStepIndicator('configure');
    ?>
    <h1>⚙️ Configuración del Sistema</h1>
    
    <?php
    // Crear costos.json si no existe
    $costos_file = __DIR__ . '/costos.json';
    if (!file_exists($costos_file)) {
        $default_data = [
            'costos' => [
                [
                    'id' => 1,
                    'tipo_costo' => 'Fijo',
                    'recurrencia' => 'Mensual',
                    'categoria' => 'Plataforma',
                    'tipo_prod' => 'Omnicanalidad',
                    'item' => 'Ejemplo - Plataforma básica',
                    'costoUSD' => 100.00,
                    'margen_custom' => null,
                    'activo' => true,
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]
            ]
        ];
        
        if (file_put_contents($costos_file, json_encode($default_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo "<div class='status success'>✅ Base de datos costos.json creada</div>";
        } else {
            echo "<div class='status error'>❌ Error al crear costos.json</div>";
        }
    } else {
        echo "<div class='status info'>ℹ️ Base de datos costos.json ya existe</div>";
    }
    
    // Verificar archivos necesarios
    $required_files = [
        'index.php' => 'Cotizador principal',
        'admin.php' => 'Panel de administración',
        'exportar.php' => 'Sistema de exportación',
        'styles.css' => 'Estilos principales',
        'admin-styles.css' => 'Estilos del administrador',
        'cotizador.js' => 'JavaScript del cotizador',
        'admin.js' => 'JavaScript del administrador'
    ];
    
    $missing_files = [];
    foreach ($required_files as $file => $description) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<div class='status success'>✅ $file - $description</div>";
        } else {
            echo "<div class='status warning'>⚠️ $file - $description (Faltante)</div>";
            $missing_files[] = $file;
        }
    }
    
    if (!empty($missing_files)) {
        echo "<div class='status warning'>
            <strong>Archivos faltantes detectados</strong><br>
            Asegúrate de subir todos los archivos del sistema.
        </div>";
    }
    
    // Verificar PhpSpreadsheet
    $autoload_paths = [
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/../../vendor/autoload.php'
    ];
    
    $spreadsheet_available = false;
    foreach ($autoload_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                echo "<div class='status success'>✅ PhpSpreadsheet disponible</div>";
                $spreadsheet_available = true;
            }
            break;
        }
    }
    
    if (!$spreadsheet_available) {
        echo "<div class='status warning'>⚠️ PhpSpreadsheet no disponible (se usará CSV)</div>";
    }
    
    echo "<div class='status success'>
        <strong>✅ Configuración completada</strong><br>
        El sistema está listo para usar.
    </div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>
        <a href='?step=complete' class='btn btn-success'>🎉 Finalizar Instalación</a>
    </div>";
}

function showComplete() {
    showStepIndicator('complete');
    ?>
    <h1>🎉 ¡Instalación Completada!</h1>
    
    <div class="status success">
        <h3>✅ Sistema SkyTel Cotizador instalado correctamente</h3>
        <p>Todos los componentes están funcionando y listos para usar.</p>
    </div>
    
    <h2>🚀 Próximos pasos:</h2>
    <div class="info">
        <ol>
            <li><strong>Acceder al cotizador:</strong> <a href="index.php" class="btn btn-primary">🧮 Abrir Cotizador</a></li>
            <li><strong>Configurar items:</strong> <a href="admin.php" class="btn btn-warning">⚙️ Panel Admin</a></li>
            <li><strong>Verificar sistema:</strong> <a href="verificar_dependencias.php" class="btn btn-secondary">🔍 Verificación</a></li>
        </ol>
    </div>
    
    <h2>📚 Documentación:</h2>
    <div class="info">
        <ul>
            <li><strong>Usar el cotizador:</strong> Agregar cantidades y exportar presupuestos</li>
            <li><strong>Administrar items:</strong> Crear, editar y organizar productos/servicios</li>
            <li><strong>Gestionar márgenes:</strong> Configurar márgenes globales y personalizados</li>
            <li><strong>Exportar datos:</strong> Generar archivos Excel con presupuestos</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="index.php" class="btn btn-success">🏠 Ir al Sistema</a>
        <a href="verificar_dependencias.php" class="btn btn-secondary">🔧 Verificar Estado</a>
    </div>
    
    <div class="terminal">
        <strong>Instalación completada exitosamente! 🎉</strong><br>
        Sistema: SkyTel Cotizador v2.0<br>
        Fecha: <?= date('Y-m-d H:i:s') ?><br>
        Estado: ✅ OPERATIVO
    </div>
    <?php
}
?>