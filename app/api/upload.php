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
    
    $originalName = $_POST['originalName'] ?? '';
    $mimeType = $_POST['mimeType'] ?? 'application/octet-stream';
    $size = $_POST['size'] ?? 0;
    $fileHash = $_POST['fileHash'] ?? '';
    $iv = $_POST['iv'] ?? '';
    $wrappedDataKey = $_POST['wrappedDataKey'] ?? '';
    
    if (empty($originalName) || empty($fileHash) || empty($iv) || empty($wrappedDataKey)) {
        throw new Exception("Missing cryptographic metadata.");
    }
    
    if (!isset($_FILES['ciphertext']) || $_FILES['ciphertext']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Ciphertext upload failed.");
    }
    
    // Generate UUID for file
    $fileId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    $storedName = $fileId . '.enc';
    $uploadPath = __DIR__ . '/../../storage/encrypted/' . $storedName;
    
    if (!move_uploaded_file($_FILES['ciphertext']['tmp_name'], $uploadPath)) {
        throw new Exception("Failed to move uploaded file to encrypted storage.");
    }
    
    // Insert into DB
    $db = \FMSS\Core\Database::getConnection();
    $stmt = $db->prepare("
        INSERT INTO files (id, owner_id, original_name, stored_name, mime_type, size, file_hash, encrypted_data_key, iv)
        VALUES (:id, :owner, :orig, :stored, :mime, :size, :hash, :key, :iv)
    ");
    
    $stmt->execute([
        'id' => $fileId,
        'owner' => $user['id'],
        'orig' => $originalName,
        'stored' => $storedName,
        'mime' => $mimeType,
        'size' => $size,
        'hash' => $fileHash,
        'key' => $wrappedDataKey,
        'iv' => $iv
    ]);
    
    // Write to Ledger
    \FMSS\Services\LedgerService::recordAction('UPLOAD', $user['id'], $fileHash, ['file_id' => $fileId]);
    
    echo json_encode(['success' => true, 'file_id' => $fileId]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
