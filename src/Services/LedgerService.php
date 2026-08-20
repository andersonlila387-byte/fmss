<?php
namespace FMSS\Services;

use FMSS\Core\Database;
use PDO;

class LedgerService {
    
    /**
     * Record an action in the tamper-evident ledger.
     */
    public static function recordAction(string $action, ?string $actorId, ?string $targetHash, ?array $payload = null): bool {
        $db = Database::getConnection();
        
        // 1. Get the most recent block's hash and index
        $stmt = $db->query("SELECT block_index, this_hash FROM ledger ORDER BY block_index DESC LIMIT 1");
        $lastBlock = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newIndex = 0;
        $previousHash = str_repeat('0', 64); // Genesis previous hash
        
        if ($lastBlock) {
            $newIndex = $lastBlock['block_index'] + 1;
            $previousHash = $lastBlock['this_hash'];
        }
        
        $payloadJson = $payload ? json_encode($payload) : null;
        
        // 2. Compute the new block's hash
        // Hash must be deterministic. We'll hash: index + action + actor_id + target_hash + payload + previous_hash
        $dataToHash = $newIndex . $action . ($actorId ?? '') . ($targetHash ?? '') . ($payloadJson ?? '') . $previousHash;
        $thisHash = hash('sha256', $dataToHash);
        
        // 3. Insert into ledger
        $insert = $db->prepare("
            INSERT INTO ledger (block_index, action, actor_id, target_hash, payload, previous_hash, this_hash)
            VALUES (:index, :action, :actor, :target, :payload, :prev, :this_hash)
        ");
        
        return $insert->execute([
            'index' => $newIndex,
            'action' => $action,
            'actor' => $actorId,
            'target' => $targetHash,
            'payload' => $payloadJson,
            'prev' => $previousHash,
            'this_hash' => $thisHash
        ]);
    }
}
