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
    $shareId = $input['share_id'] ?? '';
    
    if (empty($shareId)) {
        throw new Exception("Missing share ID.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    
    // Verify ownership
    $stmt = $db->prepare("SELECT s.*, f.file_hash FROM shares s JOIN files f ON s.file_id = f.id WHERE s.id = :id AND s.sender_id = :owner");
    $stmt->execute(['id' => $shareId, 'owner' => $user['id']]);
    $share = $stmt->fetch();
    
    if (!$share) {
        throw new Exception("Share not found or access denied.");
    }
    
    $update = $db->prepare("UPDATE shares SET status = 'revoked' WHERE id = :id");
    $update->execute(['id' => $shareId]);
    
    // Write to Ledger
    \FMSS\Services\LedgerService::recordAction('SHARE_REVOKED', $user['id'], $share['file_hash'], ['share_id' => $shareId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
