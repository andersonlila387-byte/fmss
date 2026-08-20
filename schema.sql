-- FMSS Initial Database Schema
-- Ensure you are using MySQL 8.0+ or MariaDB 10.5+

CREATE DATABASE IF NOT EXISTS fmss_vault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fmss_vault;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY, -- UUID
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    telegram_chat_id VARCHAR(255) NULL,
    telegram_verified BOOLEAN DEFAULT FALSE,
    telegram_verification_code VARCHAR(10) NULL,
    telegram_verification_expires_at TIMESTAMP NULL,
    password_hash VARCHAR(255) NOT NULL, -- Argon2id hash
    public_key TEXT NOT NULL,            -- Ed25519 public key
    encrypted_private_key TEXT NOT NULL, -- Wrapped with Master Key
    vault_pin_hash VARCHAR(255) NULL,    -- Argon2id hash for 4-digit PIN
    failed_login_attempts INT DEFAULT 0, -- Track failed attempts for lockout
    status ENUM('active', 'suspended', 'pending', 'locked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: password_resets (For Telegram OTP Flow)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL, -- Hashed OTP to prevent leakage if DB is dumped
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: files
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS files (
    id CHAR(36) PRIMARY KEY, -- UUID
    owner_id CHAR(36) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT UNSIGNED NOT NULL,
    file_hash VARCHAR(64) NOT NULL,       -- SHA-256 hash (64 hex characters)
    encrypted_data_key TEXT NOT NULL,
    iv VARCHAR(255) NOT NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: logins (Password Manager)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS logins (
    id CHAR(36) PRIMARY KEY, -- UUID
    owner_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,          -- Plaintext identifier (e.g., 'Google Account') or could be encrypted too
    encrypted_payload TEXT NOT NULL,      -- Encrypted JSON containing: username, password, url, notes
    encrypted_data_key TEXT NOT NULL,     -- The AES key used to encrypt the payload, wrapped by User's Master Key
    iv VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: shares
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS shares (
    id CHAR(36) PRIMARY KEY, -- UUID
    file_id CHAR(36) NOT NULL,
    sender_id CHAR(36) NOT NULL,
    max_downloads INT DEFAULT 1,
    downloads_count INT DEFAULT 0,
    status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: ledger (The Hash Chain)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS ledger (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_index BIGINT UNSIGNED NOT NULL UNIQUE,
    action VARCHAR(50) NOT NULL,          -- e.g., 'UPLOAD', 'SHARE', 'DOWNLOAD'
    actor_id CHAR(36) NULL,               -- Can be NULL for system actions
    target_hash VARCHAR(64) NULL,         -- The SHA-256 of the file involved
    payload JSON NULL,                    -- Extra context (e.g., recipient ID)
    previous_hash VARCHAR(64) NOT NULL,   -- Hash of the previous block
    this_hash VARCHAR(64) NOT NULL,       -- Hash of this entire block's contents
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: sessions
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS sessions (
    id CHAR(36) PRIMARY KEY, -- UUID Session ID
    user_id CHAR(36) NOT NULL,
    token_hash VARCHAR(128) NOT NULL,
    ip VARCHAR(45) NULL,                  -- Supports IPv6 length
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: secure_notes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS secure_notes (
    id CHAR(36) PRIMARY KEY, -- UUID
    owner_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    encrypted_payload TEXT NOT NULL,      -- Encrypted JSON or text
    encrypted_data_key TEXT NOT NULL,     -- The AES key used to encrypt the payload
    iv VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: cards
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS cards (
    id CHAR(36) PRIMARY KEY, -- UUID
    owner_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    encrypted_payload TEXT NOT NULL,      -- Encrypted JSON containing: card_number, expiry, cvv, pin, notes
    encrypted_data_key TEXT NOT NULL,     -- The AES key used to encrypt the payload
    iv VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;