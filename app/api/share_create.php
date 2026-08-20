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
    $fileId = $input['file_id'] ?? '';
    $maxDownloads = (int)($input['max_downloads'] ?? 1);
    $expiresInHours = (int)($input['expires_in_hours'] ?? 24); // default 24h
    
    if (empty($fileId)) {
        throw new Exception("Missing file ID.");
    }
    
    if ($maxDownloads < 1) {
        $maxDownloads = 1; // Default minimum 1
    }
    
    if ($expiresInHours < 1) {
        $expiresInHours = 1;
    }
    
    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresInHours hours"));
    
    $db = \FMSS\Core\Database::getConnection();
    
    // Verify ownership
    $stmt = $db->prepare("SELECT * FROM files WHERE id = :id AND owner_id = :owner AND is_deleted = FALSE");
    $stmt->execute(['id' => $fileId, 'owner' => $user['id']]);
    $file = $stmt->fetch();
    
    if (!$file) {
        throw new Exception("File not found or access denied.");
    }
    
    // Generate unique Share ID
    $shareId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    $insert = $db->prepare("
        INSERT INTO shares (id, file_id, sender_id, max_downloads, expires_at)
        VALUES (:id, :file_id, :sender_id, :max, :expires_at)
    ");
    $insert->execute([
        'id' => $shareId,
        'file_id' => $fileId,
        'sender_id' => $user['id'],
        'max' => $maxDownloads,
        'expires_at' => $expiresAt
    ]);
    
    // Write to Ledger
    \FMSS\Services\LedgerService::recordAction('SHARE_LINK_CREATED', $user['id'], $file['file_hash'], ['share_id' => $shareId]);
    
    echo json_encode(['success' => true, 'share_id' => $shareId]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
