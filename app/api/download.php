<?php
require_once __DIR__ . '/../../src/Core/SessionManager.php';
require_once __DIR__ . '/../../src/Core/Database.php';
require_once __DIR__ . '/../../src/Services/LedgerService.php';

try {
    $user = \FMSS\Core\SessionManager::requireAuthentication();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Invalid request method.");
    }
    
    $fileId = $_GET['id'] ?? '';
    if (empty($fileId)) {
        throw new Exception("Missing file ID.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM files WHERE id = :id AND owner_id = :owner AND is_deleted = FALSE");
    $stmt->execute(['id' => $fileId, 'owner' => $user['id']]);
    $file = $stmt->fetch();
    
    if (!$file) {
        throw new Exception("File not found or access denied.");
    }
    
    $filePath = __DIR__ . '/../../storage/encrypted/' . $file['stored_name'];
    if (!file_exists($filePath)) {
        throw new Exception("Ciphertext missing from storage.");
    }
    
    // Write to Ledger
    \FMSS\Services\LedgerService::recordAction('DOWNLOAD', $user['id'], $file['file_hash'], ['file_id' => $fileId]);
    
    // Return the file with metadata in headers
    header('Content-Type: application/octet-stream');
    header('X-FMSS-IV: ' . $file['iv']);
    header('X-FMSS-Key: ' . $file['encrypted_data_key']);
    header('X-FMSS-Hash: ' . $file['file_hash']);
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
