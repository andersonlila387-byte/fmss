<?php

namespace FMSS\Services;

use FMSS\Core\Database;
use Exception;

class AuthService {
    
    /**
     * Registers a new user and sends a Telegram OTP.
     */
    public function register(array $data): array {
        $db = Database::getConnection();
        
        $db->beginTransaction();
        
        try {
        // Generate zero-dependency UUIDv4
        $id = $this->generateUuid();
        
        // Hash the password using Argon2id
        $passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
        
        // Generate 6-digit OTP and set expiry (15 mins)
        $verificationCode = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $stmt = $db->prepare("
            INSERT INTO users (id, username, email, telegram_chat_id, telegram_verification_code, telegram_verification_expires_at, password_hash, public_key, encrypted_private_key, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $id, 
            $data['username'], 
            $data['email'], 
            $data['telegram_chat_id'], 
            $verificationCode, 
            $expiresAt, 
            $passwordHash, 
            $data['public_key'], 
            $data['encrypted_private_key']
        ]);

        // Send OTP via Telegram
        $telegram = new TelegramService();
        $message = "Welcome to FMSS Vault!\n\nYour verification code is: <b>{$verificationCode}</b>\n\nThis code expires in 15 minutes.";
            $sent = $telegram->sendMessage($data['telegram_chat_id'], $message);
            
            if (!$sent) {
                throw new Exception("Failed to send Telegram message. Please ensure you have opened the bot and clicked 'Start'.");
            }

        // Notify Admin of new registration
        $adminMessage = "🚨 <b>New User Registration</b>\n\n";
        $adminMessage .= "<b>Username:</b> " . htmlspecialchars($data['username']) . "\n";
        $adminMessage .= "<b>Email:</b> " . htmlspecialchars($data['email']) . "\n";
        $adminMessage .= "<b>Status:</b> Pending Verification";
        $telegram->sendAdminNotification($adminMessage);

            $db->commit();

        return [
            'success' => true, 
            'user_id' => $id, 
            'message' => 'Registration successful. Please check Telegram for your verification code.'
        ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Verifies the submitted Telegram OTP.
     */
    public function verifyTelegramCode(string $userId, string $code): bool {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id, telegram_verification_code, telegram_verification_expires_at FROM users WHERE id = ? AND status = 'pending'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User not found, or account is already active.");
        }

        if ($user['telegram_verification_code'] !== $code) {
            throw new Exception("Invalid verification code.");
        }

        if (strtotime($user['telegram_verification_expires_at']) < time()) {
            throw new Exception("Verification code has expired. Please request a new one.");
        }

        // Code is valid. Activate user.
        $updateStmt = $db->prepare("UPDATE users SET telegram_verified = 1, status = 'active', telegram_verification_code = NULL, telegram_verification_expires_at = NULL WHERE id = ?");
        $updateStmt->execute([$userId]);

        return true;
    }

    /**
     * Resends the Telegram OTP.
     */
    public function resendTelegramCode(string $userId): bool {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id, telegram_chat_id, status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User not found.");
        }
        if ($user['status'] !== 'pending') {
            throw new Exception("Account is already active.");
        }

        $verificationCode = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $updateStmt = $db->prepare("UPDATE users SET telegram_verification_code = ?, telegram_verification_expires_at = ? WHERE id = ?");
        $updateStmt->execute([$verificationCode, $expiresAt, $userId]);

        $telegram = new TelegramService();
        $message = "🔄 <b>FMSS Vault</b>\n\nYour new verification code is: <b>{$verificationCode}</b>\n\nThis code expires in 15 minutes.";
        return $telegram->sendMessage($user['telegram_chat_id'], $message);
    }

    /**
     * Authenticates a user and establishes a session.
     */
    public function login(array $data): array {
        $db = Database::getConnection();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required.");
        }

        $stmt = $db->prepare("SELECT id, username, password_hash, status, telegram_chat_id, failed_login_attempts FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Invalid email or password.");
        }

        if ($user['status'] === 'locked') {
            throw new Exception("Account is locked due to too many failed attempts. Please contact support.");
        }

        if (!password_verify($password, $user['password_hash'])) {
            $fails = $user['failed_login_attempts'] + 1;
            if ($fails >= 5) {
                $stmt = $db->prepare("UPDATE users SET failed_login_attempts = ?, status = 'locked' WHERE id = ?");
                $stmt->execute([$fails, $user['id']]);
                throw new Exception("Account is locked due to too many failed attempts. Please contact support.");
            } else {
                $stmt = $db->prepare("UPDATE users SET failed_login_attempts = ? WHERE id = ?");
                $stmt->execute([$fails, $user['id']]);
                throw new Exception("Invalid email or password.");
            }
        }

        // Reset failed attempts on successful login
        if ($user['failed_login_attempts'] > 0) {
            $stmt = $db->prepare("UPDATE users SET failed_login_attempts = 0 WHERE id = ?");
            $stmt->execute([$user['id']]);
        }

        if ($user['status'] !== 'active') {
            throw new Exception("Account is not active. Please verify your Telegram account.");
        }

        // Generate session
        $sessionId = $this->generateUuid();
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $stmt = $db->prepare("INSERT INTO sessions (id, user_id, token_hash, ip, user_agent, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sessionId, $user['id'], $tokenHash, $ip, $userAgent, $expiresAt]);

        // Send Security Alert to User via Telegram
        $telegram = new TelegramService();
        $time = date('Y-m-d H:i:s T');
        
        $message = "🚨 <b>New Login Detected</b>\n\n";
        $message .= "A new login to your FMSS Vault was just detected.\n\n";
        $message .= "<b>Time:</b> {$time}\n";
        $message .= "<b>IP Address:</b> {$ip}\n\n";
        $message .= "If this was you, you can safely ignore this message.\n\n";
        $message .= "⚠️ <b>If you did not authorize this login:</b>\n";
        $message .= "Please log in immediately, change your master password, and review your connected devices.";
        $telegram->sendMessage($user['telegram_chat_id'], $message);

        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token
        ];
    }

    /**
     * Logs out the current user by destroying the session in the database.
     */
    public function logout(): void {
        $token = $_COOKIE['fmss_session'] ?? null;
        
        if ($token) {
            $tokenHash = hash('sha256', $token);
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM sessions WHERE token_hash = ?");
            $stmt->execute([$tokenHash]);
        }
    }

    /**
     * Initiates the forgot password flow by sending an OTP to Telegram.
     */
    public function forgotPassword(string $email): array {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id, telegram_chat_id, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // To prevent email enumeration, return success even if not found
            return ['success' => true, 'message' => 'If an account exists, a recovery code has been sent.'];
        }
        
        if ($user['status'] !== 'active') {
             throw new Exception("Account is not active.");
        }
        
        // Generate 6 digit OTP
        $otp = (string) random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, otp_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $otpHash, $expiresAt]);
        
        $telegram = new TelegramService();
        $message = "🔐 <b>Password Reset Request</b>\n\nYour password reset code is: <b>{$otp}</b>\n\nThis code expires in 15 minutes. If you did not request this, please ignore this message.";
        $telegram->sendMessage($user['telegram_chat_id'], $message);
        
        return ['success' => true, 'message' => 'If an account exists, a recovery code has been sent.'];
    }

    /**
     * Resets the user's password using the Telegram OTP.
     */
    public function resetPassword(string $email, string $otp, string $newPassword): array {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("Invalid recovery code or email.");
        }
        
        $stmt = $db->prepare("SELECT id, otp_hash, expires_at FROM password_resets WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        $reset = $stmt->fetch();
        
        if (!$reset || !password_verify($otp, $reset['otp_hash'])) {
            throw new Exception("Invalid recovery code.");
        }
        
        if (strtotime($reset['expires_at']) < time()) {
            throw new Exception("Recovery code has expired.");
        }
        
        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        // Note: For a true zero-knowledge architecture, changing the master password here
        // without re-encrypting the private key means old data becomes unreadable.
        // This is a known trade-off handled at the application logic layer.
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $user['id']]);
        
        // Delete all resets for this user
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        
        return ['success' => true, 'message' => 'Password reset successfully.'];
    }

    /**
     * Helper to generate a v4 UUID
     */
    private function generateUuid(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}