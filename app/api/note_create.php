<?php
require_once __DIR__ . '/../../src/Core/SessionManager.php';
require_once __DIR__ . '/../../src/Core/Database.php';
require_once __DIR__ . '/../../src/Services/LedgerService.php';

header('Content-Type: application/json');

try {
    $user = \FMSS\Core\SessionManager::requireAuthentication();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $title = trim($input['title'] ?? '');
    $encryptedPayload = $input['encrypted_payload'] ?? '';
    $wrappedDataKey = $input['wrapped_data_key'] ?? '';
    $iv = $input['iv'] ?? '';
    
    if (empty($title) || empty($encryptedPayload) || empty($wrappedDataKey) || empty($iv)) {
        throw new Exception("Missing required fields.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    
    $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    $stmt = $db->prepare("
        INSERT INTO secure_notes (id, owner_id, title, encrypted_payload, encrypted_data_key, iv)
        VALUES (:id, :owner_id, :title, :payload, :data_key, :iv)
    ");
    $stmt->execute([
        'id' => $id,
        'owner_id' => $user['id'],
        'title' => $title,
        'payload' => $encryptedPayload,
        'data_key' => $wrappedDataKey,
        'iv' => $iv
    ]);
    
    $payloadHash = hash('sha256', $encryptedPayload);
    \FMSS\Services\LedgerService::recordAction('NOTE_CREATED', $user['id'], $payloadHash, ['note_id' => $id, 'title' => $title]);
    
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
