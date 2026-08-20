<?php
if (!isset($user) || !isset($user['id'])) {
    // Prevent direct access or access without user context
    return;
}

try {
    require_once __DIR__ . '/../../src/Core/Database.php';
    $db = \FMSS\Core\Database::getConnection();
    $uid = $user['id'];
    
    $metrics = [
        'files'        => (int)$db->query("SELECT COUNT(*) FROM files WHERE owner_id = '$uid' AND is_deleted = FALSE")->fetchColumn(),
        'credentials'  => (int)$db->query("SELECT COUNT(*) FROM logins WHERE owner_id = '$uid'")->fetchColumn(),
        'secureNotes'  => (int)$db->query("SELECT COUNT(*) FROM secure_notes WHERE owner_id = '$uid'")->fetchColumn(),
        'cards'        => (int)$db->query("SELECT COUNT(*) FROM cards WHERE owner_id = '$uid'")->fetchColumn(),
        'shares'       => (int)$db->query("SELECT COUNT(*) FROM shares WHERE sender_id = '$uid'")->fetchColumn(),
        'ledgerBlocks' => (int)$db->query("SELECT COUNT(*) FROM ledger")->fetchColumn(),
        'storageBytes' => (float)$db->query("SELECT SUM(size) FROM files WHERE owner_id = '$uid' AND is_deleted = FALSE")->fetchColumn(),
        'storageTotal' => 10,
        'weak'         => 0,
        'reused'       => 0,
        'breached'     => 0,
    ];
    $metrics['storageUsed'] = round($metrics['storageBytes'] / (1024 * 1024 * 1024), 4); // GB
    $storagePct = $metrics['storageTotal'] > 0 ? round(($metrics['storageUsed'] / $metrics['storageTotal']) * 100, 1) : 0;
} catch (Exception $e) {
    $metrics = ['files'=>0, 'credentials'=>0, 'secureNotes'=>0, 'cards'=>0, 'shares'=>0, 'ledgerBlocks'=>1, 'storageUsed'=>0, 'storageTotal'=>10, 'weak'=>0, 'reused'=>0, 'breached'=>0];
    $storagePct = 0;
}
