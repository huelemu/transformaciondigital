<?php
// api-endpoint.php - Endpoint para operaciones AJAX (opcional)
require_once 'config.php';
require_once 'middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'check_session':
        echo json_encode([
            'authenticated' => isAuthenticated(),
            'user' => isAuthenticated() ? $_SESSION['user'] : null
        ]);
        break;
        
    case 'extend_session':
        if (isAuthenticated()) {
            $_SESSION['user']['login_time'] = time();
            SecurityMiddleware::logActivity('session_extended');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Not authenticated']);
        }
        break;
        
    case 'log_activity':
        if (isAuthenticated()) {
            $activity = $_POST['activity'] ?? '';
            $details = $_POST['details'] ?? '';
            SecurityMiddleware::logActivity($activity, $details);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Not authenticated']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}