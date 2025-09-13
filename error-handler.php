<?php
// error-handler.php - Manejo centralizado de errores
require_once 'config.php';

function getErrorMessage($error_code) {
    $messages = [
        'no_code' => [
            'title' => 'Error de Autenticación',
            'message' => 'No se recibió el código de autorización de Google.',
            'type' => 'error'
        ],
        'auth_failed' => [
            'title' => 'Error de Autenticación',
            'message' => 'Hubo un problema al autenticar con Google. Por favor, inténtalo nuevamente.',
            'type' => 'error'
        ],
        'domain_not_allowed' => [
            'title' => 'Acceso Denegado',
            'message' => 'Tu dominio de correo electrónico no está autorizado para acceder a este sistema.',
            'type' => 'warning'
        ],
        'logged_out' => [
            'title' => 'Sesión Cerrada',
            'message' => 'Has cerrado sesión exitosamente.',
            'type' => 'success'
        ],
        'session_expired' => [
            'title' => 'Sesión Expirada',
            'message' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.',
            'type' => 'info'
        ]
    ];
    
    return isset($messages[$error_code]) ? $messages[$error_code] : null;
}

function renderAlert($error_info, $domain = '') {
    if (!$error_info) return '';
    
    $icon_map = [
        'error' => '❌',
        'warning' => '⚠️',
        'success' => '✅',
        'info' => 'ℹ️'
    ];
    
    $color_map = [
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'success' => '#28a745',
        'info' => '#17a2b8'
    ];
    
    $icon = $icon_map[$error_info['type']] ?? 'ℹ️';
    $color = $color_map[$error_info['type']] ?? '#17a2b8';
    
    $message = $error_info['message'];
    if ($error_info['type'] === 'warning' && $domain) {
        $message .= "<br><small>Dominio detectado: <strong>$domain</strong></small>";
    }
    
    return "
    <div class='alert alert-{$error_info['type']}' style='
        background: " . ($error_info['type'] === 'error' ? '#f8d7da' : 
                       ($error_info['type'] === 'warning' ? '#fff3cd' : 
                       ($error_info['type'] === 'success' ? '#d4edda' : '#d1ecf1'))) . ";
        border: 1px solid $color;
        color: " . ($error_info['type'] === 'warning' ? '#856404' : 
                   ($error_info['type'] === 'success' ? '#155724' : 
                   ($error_info['type'] === 'error' ? '#721c24' : '#0c5460'))) . ";
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        animation: slideIn 0.3s ease-out;
    '>
        <div style='display: flex; align-items: center; gap: 10px;'>
            <span style='font-size: 18px;'>$icon</span>
            <div>
                <strong>{$error_info['title']}</strong><br>
                $message
            </div>
        </div>
    </div>";
}
?>