<?php
// crear archivo: check-logs.php
echo "<h1>Últimos Logs de Error</h1>";
echo "<pre>";

// Mostrar últimas líneas del log de PHP
if (file_exists('/var/log/apache2/error.log')) {
    echo "=== Apache Error Log ===\n";
    echo shell_exec('tail -20 /var/log/apache2/error.log');
} elseif (file_exists('/var/log/nginx/error.log')) {
    echo "=== Nginx Error Log ===\n";
    echo shell_exec('tail -20 /var/log/nginx/error.log');
} else {
    echo "No se encontraron logs de error del servidor\n";
}

// Mostrar log de PHP si está configurado
if (ini_get('log_errors') && ini_get('error_log')) {
    echo "\n=== PHP Error Log ===\n";
    echo shell_exec('tail -20 ' . ini_get('error_log'));
}

echo "</pre>";
?>