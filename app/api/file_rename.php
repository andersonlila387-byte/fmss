<?php
require_once __DIR__ . '/../../src/Core/SessionManager.php';
require_once __DIR__ . '/../../src/Core/Database.php';

header('Content-Type: application/json');

try {
    $user = \FMSS\Core\SessionManager::requireAuthentication();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $fileId = $input['id'] ?? '';
    $newName = trim($input['new_name'] ?? '');
    
    if (empty($fileId) || empty($newName)) {
        throw new Exception("Missing file ID or new name.");
    }
    
    $db = \FMSS\Core\Database::getConnection();
    
    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM files WHERE id = :id AND owner_id = :owner AND is_deleted = FALSE");
    $stmt->execute(['id' => $fileId, 'owner' => $user['id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception("File not found or access denied.");
    }
    
    // Update name
    $update = $db->prepare("UPDATE files SET original_name = :name WHERE id = :id");
    $update->execute(['name' => $newName, 'id' => $fileId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
