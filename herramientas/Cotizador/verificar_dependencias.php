<?php
// verificar_dependencias.php - Verificar que el sistema tenga todo lo necesario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - SkyTel Cotizador</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .requirement { margin: 15px 0; padding: 10px; border-left: 4px solid #ddd; }
        .requirement.ok { border-left-color: #28a745; }
        .requirement.warning { border-left-color: #ffc107; }
        .requirement.error { border-left-color: #dc3545; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Verificación del Sistema SkyTel Cotizador</h1>
        
        <?php
        $errors = [];
        $warnings = [];
        $success = [];
        
        // Verificar PHP
        echo "<h2>📋 Información del Sistema</h2>";
        echo "<div class='info'>PHP Version: " . PHP_VERSION . "</div>";
        echo "<div class='info'>Sistema Operativo: " . PHP_OS . "</div>";
        echo "<div class='info'>Fecha actual: " . date('Y-m-d H:i:s') . "</div>";
        
        // Verificar versión de PHP
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            echo "<div class='requirement ok'>✅ PHP " . PHP_VERSION . " - Compatible</div>";
        } else {
            echo "<div class='requirement error'>❌ PHP " . PHP_VERSION . " - Se requiere PHP 7.4 o superior</div>";
            $errors[] = "Versión de PHP muy antigua";
        }
        
        // Verificar extensiones necesarias
        echo "<h2>🧩 Extensiones de PHP</h2>";
        
        $extensiones_requeridas = ['json', 'mbstring', 'xml', 'zip'];
        foreach ($extensiones_requeridas as $ext) {
            if (extension_loaded($ext)) {
                echo "<div class='requirement ok'>✅ Extensión '$ext' - Instalada</div>";
            } else {
                echo "<div class='requirement error'>❌ Extensión '$ext' - No encontrada</div>";
                $errors[] = "Extensión $ext no instalada";
            }
        }
        
        // Verificar Composer y dependencias
        echo "<h2>📦 Dependencias</h2>";
        
        $autoload_paths = [
            __DIR__ . '/../../vendor/autoload.php',
            __DIR__ . '/../../../vendor/autoload.php',
            __DIR__ . '/vendor/autoload.php'
        ];
        
        $autoload_found = false;
        foreach ($autoload_paths as $path) {
            if (file_exists($path)) {
                echo "<div class='requirement ok'>✅ Composer autoload encontrado en: <code>$path</code></div>";
                require_once $path;
                $autoload_found = true;
                break;
            }
        }
        
        if (!$autoload_found) {
            echo "<div class='requirement error'>❌ Composer autoload no encontrado</div>";
            echo "<div class='info'>Rutas verificadas:<br>";
            foreach ($autoload_paths as $path) {
                echo "• <code>$path</code><br>";
            }
            echo "</div>";
            $errors[] = "Composer no instalado";
        }
        
        // Verificar PhpSpreadsheet
        if ($autoload_found) {
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                echo "<div class='requirement ok'>✅ PhpSpreadsheet - Instalado y disponible</div>";
                $success[] = "PhpSpreadsheet funcional";
            } else {
                echo "<div class='requirement error'>❌ PhpSpreadsheet - No encontrado</div>";
                $warnings[] = "PhpSpreadsheet no disponible (se usará CSV como respaldo)";
            }
        }
        
        // Verificar archivos del sistema
        echo "<h2>📁 Archivos del Sistema</h2>";
        
        $archivos_requeridos = [
            'costos.json' => 'Base de datos de costos',
            'index.php' => 'Cotizador principal',
            'admin.php' => 'Panel de administración',
            'exportar.php' => 'Sistema de exportación'
        ];
        
        foreach ($archivos_requeridos as $archivo => $descripcion) {
            $path = __DIR__ . '/' . $archivo;
            if (file_exists($path)) {
                $size = filesize($path);
                $readable = is_readable($path);
                $writable = is_writable($path);
                
                echo "<div class='requirement ok'>✅ $archivo - $descripcion (" . number_format($size) . " bytes)";
                if ($archivo === 'costos.json') {
                    echo $writable ? " [Escribible]" : " [Solo lectura]";
                }
                echo "</div>";
            } else {
                echo "<div class='requirement error'>❌ $archivo - No encontrado</div>";
                $errors[] = "Archivo $archivo faltante";
            }
        }
        
        // Verificar permisos
        echo "<h2>🔐 Permisos</h2>";
        
        $costos_file = __DIR__ . '/costos.json';
        if (file_exists($costos_file)) {
            if (is_writable($costos_file)) {
                echo "<div class='requirement ok'>✅ costos.json es escribible</div>";
            } else {
                echo "<div class='requirement warning'>⚠️ costos.json es solo lectura</div>";
                $warnings[] = "No se pueden guardar cambios en costos.json";
            }
        }
        
        if (is_writable(__DIR__)) {
            echo "<div class='requirement ok'>✅ Directorio escribible</div>";
        } else {
            echo "<div class='requirement warning'>⚠️ Directorio de solo lectura</div>";
            $warnings[] = "No se pueden crear archivos temporales";
        }
        
        // Probar funcionalidades
        echo "<h2>🧪 Pruebas de Funcionalidad</h2>";
        
        // Probar JSON
        $test_data = ['test' => 'data', 'numero' => 123];
        $json_encoded = json_encode($test_data);
        $json_decoded = json_decode($json_encoded, true);
        
        if ($json_decoded && $json_decoded['test'] === 'data') {
            echo "<div class='requirement ok'>✅ Codificación/Decodificación JSON funcional</div>";
        } else {
            echo "<div class='requirement error'>❌ Problemas con JSON</div>";
            $errors[] = "Funcionalidad JSON no funciona";
        }
        
        // Probar lectura de costos.json
        if (file_exists($costos_file)) {
            $costos_content = file_get_contents($costos_file);
            $costos_data = json_decode($costos_content, true);
            
            if ($costos_data && isset($costos_data['costos'])) {
                $num_items = count($costos_data['costos']);
                echo "<div class='requirement ok'>✅ Base de datos de costos cargada ($num_items items)</div>";
            } else {
                echo "<div class='requirement error'>❌ Error al cargar base de datos de costos</div>";
                $errors[] = "costos.json corrupto o formato inválido";
            }
        }
        
        // Resumen final
        echo "<h2>📊 Resumen</h2>";
        
        if (empty($errors)) {
            if (empty($warnings)) {
                echo "<div class='success'><strong>🎉 ¡Todo perfecto!</strong><br>El sistema está completamente funcional y listo para usar.</div>";
            } else {
                echo "<div class='warning'><strong>⚠️ Sistema funcional con advertencias</strong><br>El sistema funcionará, pero hay algunas limitaciones.</div>";
            }
        } else {
            echo "<div class='error'><strong>❌ Problemas encontrados</strong><br>El sistema puede no funcionar correctamente.</div>";
        }
        
        if (!empty($errors)) {
            echo "<h3>Errores críticos:</h3><ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($warnings)) {
            echo "<h3>Advertencias:</h3><ul>";
            foreach ($warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
        }
        
        // Instrucciones de instalación
        if (!empty($errors) || !empty($warnings)) {
            echo "<h2>🔧 Instrucciones de Reparación</h2>";
            
            if (in_array("Composer no instalado", $errors)) {
                echo "<div class='info'>";
                echo "<h3>Para instalar Composer y PhpSpreadsheet:</h3>";
                echo "<ol>";
                echo "<li>Instalar Composer si no está instalado: <a href='https://getcomposer.org/download/' target='_blank'>https://getcomposer.org/download/</a></li>";
                echo "<li>En el directorio raíz del proyecto, ejecutar:</li>";
                echo "<code>composer require phpoffice/phpspreadsheet</code>";
                echo "<li>O si ya tienes un composer.json, ejecutar:</li>";
                echo "<code>composer install</code>";
                echo "</ol>";
                echo "</div>";
            }
            
            if (in_array("costos.json corrupto o formato inválido", $errors)) {
                echo "<div class='info'>";
                echo "<h3>Para reparar costos.json:</h3>";
                echo "<p>Crear un archivo costos.json con el siguiente contenido mínimo:</p>";
                echo "<pre><code>{
    \"costos\": []
}</code></pre>";
                echo "</div>";
            }
            
            if (!empty($warnings) && strpos(implode(' ', $warnings), 'solo lectura') !== false) {
                echo "<div class='info'>";
                echo "<h3>Para corregir permisos:</h3>";
                echo "<p>En sistemas Unix/Linux:</p>";
                echo "<code>chmod 664 costos.json</code><br>";
                echo "<code>chmod 755 " . __DIR__ . "</code>";
                echo "</div>";
            }
        }
        
        // Información adicional
        echo "<h2>ℹ️ Información Adicional</h2>";
        echo "<div class='info'>";
        echo "<p><strong>Fallback CSV:</strong> Si PhpSpreadsheet no está disponible, el sistema automáticamente generará archivos CSV como alternativa.</p>";
        echo "<p><strong>Ubicación:</strong> " . __DIR__ . "</p>";
        echo "<p><strong>URL de acceso:</strong> <a href='index.php'>Cotizador</a> | <a href='admin.php'>Administración</a></p>";
        echo "</div>";
        
        ?>
        
        <h2>🔄 Acciones</h2>
        <div style="margin: 20px 0;">
            <button onclick="window.location.reload()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                🔄 Verificar Nuevamente
            </button>
            <button onclick="testExport()" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                🧪 Probar Exportación
            </button>
            <a href="index.php" style="background: #17a2b8; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; display: inline-block;">
                🏠 Ir al Cotizador
            </a>
        </div>
        
        <script>
        function testExport() {
            // Crear datos de prueba
            const testData = {
                cliente: 'Cliente de Prueba',
                proyecto: 'Proyecto de Prueba',
                margen: '50',
                fecha: new Date().toLocaleDateString('es-ES'),
                hora: new Date().toLocaleTimeString('es-ES'),
                items: [
                    {
                        tipo_costo: 'Fijo',
                        recurrencia: 'Mensual',
                        categoria: 'Prueba',
                        tipo_prod: 'Test',
                        item: 'Item de prueba para verificar exportación',
                        costoUSD: 100,
                        cantidad: 1,
                        subtotal: 100,
                        precioVenta: 200
                    }
                ]
            };
            
            // Crear formulario y enviar
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'exportar.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'data';
            input.value = JSON.stringify(testData);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        </script>
        
    </div>
</body>
</html>