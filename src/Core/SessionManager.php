<?php

namespace FMSS\Core;

require_once __DIR__ . '/Database.php';

use FMSS\Core\Database;
use Exception;

class SessionManager {
    private const COOKIE_NAME = 'fmss_session';

    /**
     * Sets a secure HttpOnly cookie for the authenticated session
     */
    public static function setSessionCookie(string $token): void {
        $expires = time() + (24 * 60 * 60); // 24 hours
        
        setcookie(
            self::COOKIE_NAME,
            $token,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '', 
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Clears the session cookie (used for Logout)
     */
    public static function clearSessionCookie(): void {
        setcookie(self::COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Verifies the session and returns the logged-in user's data.
     * Returns null if no valid session exists.
     */
    public static function getAuthenticatedUser(): ?array {
        $token = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (!$token) return null;

        $tokenHash = hash('sha256', $token);
        
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT u.id, u.username, u.email, u.public_key, u.encrypted_private_key 
                FROM sessions s
                JOIN users u ON s.user_id = u.id
                WHERE s.token_hash = ? AND s.expires_at > ? AND u.status = 'active'
            ");
            
            // Pass PHP's current time to match the exact expiry logic
            $stmt->execute([$tokenHash, date('Y-m-d H:i:s')]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Protects a page by requiring authentication.
     * Redirects to the login page if unauthenticated.
     */
    public static function requireAuthentication(): array {
        $user = self::getAuthenticatedUser();
        
        if (!$user) {
            self::clearSessionCookie();
            header("Location: login.php");
            exit;
        }
        
        return $user;
    }
}