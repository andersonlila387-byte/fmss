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

if (empty($input['email']) || empty($input['otp']) || empty($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email, Recovery Code, and New Password are required.']);
    exit;
}

try {
    $authService = new AuthService();
    $result = $authService->resetPassword($input['email'], $input['otp'], $input['new_password']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}