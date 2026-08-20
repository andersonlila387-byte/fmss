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

if (empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email is required']);
    exit;
}

try {
    $authService = new AuthService();
    $result = $authService->forgotPassword($input['email']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}