<?php
// check-permissions.php - Verificar y diagnosticar permisos
require_once 'config.php';
require_once 'utils.php';

echo "<h2>🔍 Diagnóstico de Permisos del Sistema</h2>";

// 1. Información del directorio actual
echo "<h3>📁 Información del Directorio</h3>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Usuario del servidor: " . get_current_user() . "<br>";
echo "UID del proceso: " . getmyuid() . "<br>";
echo "GID del proceso: " . getmygid() . "<br>";

// 2. Verificar permisos del directorio principal
echo "<h3>🔐 Permisos del Directorio Principal</h3>";
echo "Directorio escribible: " . (is_writable(__DIR__) ? '✅ SÍ' : '❌ NO') . "<br>";
echo "Permisos: " . substr(sprintf('%o', fileperms(__DIR__)), -4) . "<br>";

// 3. Verificar directorio logs
echo "<h3>📋 Estado del Directorio Logs</h3>";
$logs_dir = __DIR__ . '/logs';
echo "Directorio logs existe: " . (is_dir($logs_dir) ? '✅ SÍ' : '❌ NO') . "<br>";

if (is_dir($logs_dir)) {
    echo "Directorio logs escribible: " . (is_writable($logs_dir) ? '✅ SÍ' : '❌ NO') . "<br>";
    echo "Permisos logs: " . substr(sprintf('%o', fileperms($logs_dir)), -4) . "<br>";
} else {
    echo "Intentando crear directorio logs...<br>";
    if (@mkdir($logs_dir, 0755, true)) {
        echo "✅ Directorio logs creado exitosamente<br>";
    } else {
        echo "❌ No se pudo crear el directorio logs<br>";
    }
}

// 4. Test de logging
echo "<h3>🧪 Test de Logging</h3>";
$test_result = Utils::testLogging();
echo "<pre>";
print_r($test_result);
echo "</pre>";

// 5. Intentar crear archivo de prueba
echo "<h3>📝 Test de Escritura</h3>";
$test_file = __DIR__ . '/test_write.txt';
if (@file_put_contents($test_file, 'Test content')) {
    echo "✅ Puede escribir archivos en el directorio principal<br>";
    @unlink($test_file); // Limpiar
} else {
    echo "❌ No puede escribir archivos en el directorio principal<br>";
}

// 6. Información de PHP
echo "<h3>⚙️ Configuración de PHP</h3>";
echo "Versión PHP: " . PHP_VERSION . "<br>";
echo "Usuario PHP: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Desconocido') . "<br>";
echo "Directorio temporal: " . sys_get_temp_dir() . "<br>";
echo "Directorio temporal escribible: " . (is_writable(sys_get_temp_dir()) ? '✅ SÍ' : '❌ NO') . "<br>";

// 7. Soluciones sugeridas
echo "<h3>🛠️ Soluciones Sugeridas</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

if (!is_dir($logs_dir)) {
    echo "<strong>1. Crear directorio logs manualmente:</strong><br>";
    echo "<code>mkdir " . $logs_dir . "</code><br>";
    echo "<code>chmod 755 " . $logs_dir . "</code><br><br>";
}

if (is_dir($logs_dir) && !is_writable($logs_dir)) {
    echo "<strong>2. Dar permisos al directorio logs:</strong><br>";
    echo "<code>chmod 755 " . $logs_dir . "</code><br>";
    echo "<code>chown www-data:www-data " . $logs_dir . "</code><br><br>";
}

echo "<strong>3. Alternativa - Cambiar propietario de todo el directorio:</strong><br>";
echo "<code>chown -R www-data:www-data " . __DIR__ . "</code><br><br>";

echo "<strong>4. Si nada funciona, el sistema usará error_log() automáticamente</strong><br>";
echo "</div>";

// 8. Test final
echo "<h3>🎯 Test Final de Logging</h3>";
Utils::logToFile("Test desde check-permissions.php", 'TEST');
Utils::simpleLog("Test de simple log desde check-permissions.php", 'TEST');
echo "✅ Tests de logging ejecutados (revisa los logs del servidor si no aparecen archivos)<br>";

echo "<hr>";
echo "<p><strong>Nota:</strong> Si ves errores, ejecuta los comandos sugeridos en tu servidor o contacta al administrador del hosting.</p>";
?>