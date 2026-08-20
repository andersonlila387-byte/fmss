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
    $id = $input['id'] ?? '';
    
    if (empty($id)) {
        throw new Exception("Missing ID.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    
    // Check ownership
    $stmt = $db->prepare("SELECT * FROM logins WHERE id = :id AND owner_id = :owner");
    $stmt->execute(['id' => $id, 'owner' => $user['id']]);
    $login = $stmt->fetch();
    
    if (!$login) {
        throw new Exception("Credential not found or access denied.");
    }
    
    $del = $db->prepare("DELETE FROM logins WHERE id = :id AND owner_id = :owner");
    $del->execute(['id' => $id, 'owner' => $user['id']]);
    
    // Write to Ledger
    $payloadHash = hash('sha256', $login['encrypted_payload']);
    \FMSS\Services\LedgerService::recordAction('LOGIN_DELETED', $user['id'], $payloadHash, ['login_id' => $id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
