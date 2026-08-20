<?php

namespace FMSS\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    /**
     * Get the PDO database connection instance (Singleton)
     * 
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $configPath = __DIR__ . '/../../config/config.php';
            
            if (!file_exists($configPath)) {
                throw new Exception("Configuration file not found at: " . $configPath);
            }
            
            $config = require $configPath;
            $dbConfig = $config['db'];

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['dbname'],
                $dbConfig['charset']
            );

            // Critical security and stability options
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch arrays by default
                PDO::ATTR_EMULATE_PREPARES   => false,                  // True prepared statements (prevents SQL injection)
            ];

            try {
                self::$instance = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
            } catch (PDOException $e) {
                // In production, log $e->getMessage() to a file instead of displaying it
                throw new Exception("Database connection failed. Please check your credentials.");
            }
        }

        return self::$instance;
    }

    // Prevent cloning and unserialization to enforce the Singleton pattern
    private function __construct() {}
    private function __clone() {}
}