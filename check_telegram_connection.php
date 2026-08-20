<?php

require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Services/TelegramService.php';

use FMSS\Services\TelegramService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token is required']);
    exit;
}

try {
    $telegram = new TelegramService();
    $chatId = $telegram->checkForConnection($token);
    
    if ($chatId) {
        echo json_encode(['success' => true, 'connected' => true, 'chat_id' => $chatId]);
    } else {
        echo json_encode(['success' => true, 'connected' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}