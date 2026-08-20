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
    
    // Read JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    $fileId = $input['id'] ?? '';
    
    if (empty($fileId)) {
        throw new Exception("Missing file ID.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    
    // Verify ownership
    $stmt = $db->prepare("SELECT * FROM files WHERE id = :id AND owner_id = :owner AND is_deleted = FALSE");
    $stmt->execute(['id' => $fileId, 'owner' => $user['id']]);
    $file = $stmt->fetch();
    
    if (!$file) {
        throw new Exception("File not found or access denied.");
    }
    
    // Soft delete
    $del = $db->prepare("UPDATE files SET is_deleted = TRUE WHERE id = :id");
    $del->execute(['id' => $fileId]);
    
    // Write to Ledger
    \FMSS\Services\LedgerService::recordAction('DELETE', $user['id'], $file['file_hash'], ['file_id' => $fileId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
