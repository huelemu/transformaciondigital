<?php
// admin.php - Panel de administración básico
require_once 'config.php';
require_once 'utils.php';

requireAuth();

// Solo usuarios de skytel.tech pueden acceder al admin
if ($_SESSION['user']['domain'] !== 'skytel.tech') {
    http_response_code(403);
    die('Acceso denegado: Se requiere dominio skytel.tech para administración');
}

// Obtener estadísticas básicas
function getStats() {
    $stats = [
        'total_logs' => 0,
        'today_logs' => 0,
        'active_sessions' => 1, // Al menos la actual
        'log_files' => []
    ];
    
    $log_dir = 'logs';
    if (is_dir($log_dir)) {
        $files = glob($log_dir . '/app_*.log');
        $stats['log_files'] = array_map('basename', $files);
        
        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $stats['total_logs'] += count($lines);
            
            if (basename($file) === 'app_' . date('Y-m-d') . '.log') {
                $stats['today_logs'] = count($lines);
            }
        }
    }
    
    return $stats;
}

$stats = getStats();

// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'clear_logs':
            if (Utils::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $cleared = 0;
                $log_files = glob('logs/app_*.log');
                foreach ($log_files as $file) {
                    if (unlink($file)) $cleared++;
                }
                Utils::logToFile("Admin cleared $cleared log files", 'ADMIN');
                $success_message = "Se eliminaron $cleared archivos de log";
            }
            break;
            
        case 'download_logs':
            if (Utils::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $date = $_POST['log_date'] ?? date('Y-m-d');
                $log_file = "logs/app_$date.log";
                
                if (file_exists($log_file)) {
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename="' . basename($log_file) . '"');
                    readfile($log_file);
                    exit();
                }
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Portal SkyTel</title>
    <link rel="stylesheet" href="libs/css/app.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .admin-section h2 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .log-files {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
        }
        
        .log-file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .back-link {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-container">
            <h1>
                🛠️ Panel de Administración
                <span style="font-size: 0.6em; font-weight: normal;">
                    <?= htmlspecialchars($_SESSION['user']['name']) ?>
                </span>
            </h1>
        </div>
    </div>
    
    <a href="index.php" class="btn btn-secondary back-link">← Volver al Portal</a>
    
    <div class="admin-container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_logs']) ?></div>
                <div class="stat-label">Total de Logs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['today_logs']) ?></div>
                <div class="stat-label">Logs de Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($stats['log_files']) ?></div>
                <div class="stat-label">Archivos de Log</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['active_sessions'] ?></div>
                <div class="stat-label">Sesiones Activas</div>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>📋 Gestión de Logs</h2>
            
            <div class="log-files">
                <?php if (empty($stats['log_files'])): ?>
                    <p style="text-align: center; color: #6c757d;">No hay archivos de log disponibles</p>
                <?php else: ?>
                    <?php foreach ($stats['log_files'] as $log_file): ?>
                        <div class="log-file-item">
                            <span>
                                📄 <?= htmlspecialchars($log_file) ?>
                                <small style="color: #6c757d;">
                                    (<?= number_format(filesize("logs/$log_file") / 1024, 1) ?> KB)
                                </small>
                            </span>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="download_logs">
                                <input type="hidden" name="log_date" value="<?= str_replace(['app_', '.log'], '', $log_file) ?>">
                                <input type="hidden" name="csrf_token" value="<?= Utils::generateCSRFToken() ?>">
                                <button type="submit" class="btn btn-primary">Descargar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form method="post" onsubmit="return confirm('¿Estás seguro de eliminar todos los logs?')" style="margin-top: 20px;">
                <input type="hidden" name="action" value="clear_logs">
                <input type="hidden" name="csrf_token" value="<?= Utils::generateCSRFToken() ?>">
                <button type="submit" class="btn btn-danger">🗑️ Limpiar Todos los Logs</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>ℹ️ Información del Sistema</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <strong>Versión de PHP:</strong><br>
                    <?= PHP_VERSION ?>
                </div>
                <div>
                    <strong>Servidor:</strong><br>
                    <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido' ?>
                </div>
                <div>
                    <strong>Memoria PHP:</strong><br>
                    <?= ini_get('memory_limit') ?>
                </div>
                <div>
                    <strong>Tiempo máximo:</strong><br>
                    <?= ini_get('max_execution_time') ?>s
                </div>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>👥 Información de Sesión</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 10px; font-weight: bold;">Usuario:</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($_SESSION['user']['name']) ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 10px; font-weight: bold;">Email:</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($_SESSION['user']['email']) ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 10px; font-weight: bold;">Dominio:</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($_SESSION['user']['domain']) ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 10px; font-weight: bold;">Login:</td>
                    <td style="padding: 10px;"><?= Utils::formatDate($_SESSION['user']['login_time']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; font-weight: bold;">IP:</td>
                    <td style="padding: 10px;"><?= $_SERVER['REMOTE_ADDR'] ?? 'Desconocida' ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>