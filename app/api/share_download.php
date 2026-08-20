<?php
require_once __DIR__ . '/../../src/Core/Database.php';
require_once __DIR__ . '/../../src/Services/LedgerService.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Invalid request method.");
    }
    
    $shareId = $_GET['id'] ?? '';
    if (empty($shareId)) {
        throw new Exception("Missing Share ID.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    
    $stmt = $db->prepare("
        SELECT s.*, f.stored_name, f.original_name, f.iv, f.file_hash 
        FROM shares s 
        JOIN files f ON s.file_id = f.id 
        WHERE s.id = :id AND s.status = 'active'
    ");
    $stmt->execute(['id' => $shareId]);
    $share = $stmt->fetch();
    
    if (!$share) {
        throw new Exception("Share link not found, revoked, or already burned.");
    }
    
    if ($share['downloads_count'] >= $share['max_downloads']) {
        // Burn the link
        $burn = $db->prepare("UPDATE shares SET status = 'expired' WHERE id = :id");
        $burn->execute(['id' => $shareId]);
        throw new Exception("This link has reached its maximum download limit and has been burned.");
    }
    
    // Increment download count
    $inc = $db->prepare("UPDATE shares SET downloads_count = downloads_count + 1 WHERE id = :id");
    $inc->execute(['id' => $shareId]);
    
    // If it reached max after this, burn it
    if ($share['downloads_count'] + 1 >= $share['max_downloads']) {
        $burn = $db->prepare("UPDATE shares SET status = 'expired' WHERE id = :id");
        $burn->execute(['id' => $shareId]);
    }
    
    $filePath = __DIR__ . '/../../storage/encrypted/' . $share['stored_name'];
    if (!file_exists($filePath)) {
        throw new Exception("Ciphertext missing from storage.");
    }
    
    // Write to Ledger (using 'GUEST' as actor_id since it's an unauthenticated link download)
    \FMSS\Services\LedgerService::recordAction('SHARE_DOWNLOAD', 'GUEST', $share['file_hash'], ['share_id' => $shareId]);
    
    // Return the file with metadata in headers
    header('Content-Type: application/octet-stream');
    header('X-FMSS-IV: ' . $share['iv']);
    header('X-FMSS-Hash: ' . $share['file_hash']);
    header('X-FMSS-Name: ' . rawurlencode($share['original_name']));
    // Note: X-FMSS-Key is NOT returned here. The user must get it from the URL hash.
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
