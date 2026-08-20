<?php

require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Services/TelegramService.php';
require_once __DIR__ . '/src/Services/AuthService.php';

use FMSS\Services\AuthService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['user_id']) || empty($input['code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'user_id and code are required']);
    exit;
}

$authService = new AuthService();

try {
    $authService->verifyTelegramCode($input['user_id'], $input['code']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Telegram verification successful. Account is now active.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}