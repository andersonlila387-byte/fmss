<?php
require_once __DIR__ . '/src/Core/Database.php';

try {
    $db = \FMSS\Core\Database::getConnection();
    
    // 1. Update Users Table
    try {
        $db->exec("ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0");
        echo "Added 'failed_login_attempts' to users.<br>";
    } catch (PDOException $e) {
        echo "Column 'failed_login_attempts' might already exist.<br>";
    }
    
    try {
        $db->exec("ALTER TABLE users ADD COLUMN vault_pin_hash VARCHAR(255) NULL");
        echo "Added 'vault_pin_hash' to users.<br>";
    } catch (PDOException $e) {
        echo "Column 'vault_pin_hash' might already exist.<br>";
    }

    // 2. Update Shares Table
    // Instead of dropping the whole table which might delete existing shares, let's just add the missing columns
    try {
        $db->exec("ALTER TABLE shares ADD COLUMN max_downloads INT DEFAULT 1");
        echo "Added 'max_downloads' to shares.<br>";
    } catch (PDOException $e) {
        echo "Column 'max_downloads' might already exist.<br>";
    }
    
    try {
        $db->exec("ALTER TABLE shares ADD COLUMN downloads_count INT DEFAULT 0");
        echo "Added 'downloads_count' to shares.<br>";
    } catch (PDOException $e) {
        echo "Column 'downloads_count' might already exist.<br>";
    }

    try {
        $db->exec("ALTER TABLE shares ADD COLUMN status ENUM('active', 'revoked', 'expired') DEFAULT 'active'");
        echo "Added 'status' to shares.<br>";
    } catch (PDOException $e) {
        echo "Column 'status' might already exist.<br>";
    }

    try {
        $db->exec("ALTER TABLE shares ADD COLUMN expires_at TIMESTAMP NULL");
        echo "Added 'expires_at' to shares.<br>";
    } catch (PDOException $e) {
        echo "Column 'expires_at' might already exist.<br>";
    }

    // Drop old recipient_id column that breaks the new public sharing flow
    try {
        $db->exec("ALTER TABLE shares DROP FOREIGN KEY shares_ibfk_3");
        echo "Dropped old foreign key shares_ibfk_3.<br>";
    } catch (Exception $e) {
        // Ignore if already dropped
    }
    
    try {
        $db->exec("ALTER TABLE shares DROP COLUMN recipient_id");
        echo "Dropped deprecated 'recipient_id' column from shares.<br>";
    } catch (Exception $e) {
        // Ignore if already dropped
    }

    echo "Database update script completed fully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
