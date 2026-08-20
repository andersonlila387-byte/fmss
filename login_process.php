<?php

require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Services/TelegramService.php';
require_once __DIR__ . '/src/Services/AuthService.php';
require_once __DIR__ . '/src/Core/SessionManager.php';

use FMSS\Services\AuthService;
use FMSS\Core\SessionManager;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['email']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit;
}

$authService = new AuthService();

try {
    $result = $authService->login($input);
    
    // Set the secure HttpOnly session cookie
    if ($result['success'] && !empty($result['token'])) {
        SessionManager::setSessionCookie($result['token']);
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}