<?php

/**
 * FMSS Base Configuration
 */
return [
    'app' => [
        'name' => 'FMSS Vault',
        'env' => 'development', // 'development' or 'production'
        'base_url' => 'http://localhost/FMSS/public',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'dbname' => 'fmss_vault',
        'username' => 'root',    // Default XAMPP user
        'password' => '',        // Default XAMPP password is empty
        'charset' => 'utf8mb4',
    ],
    'telegram' => [
        'bot_token' => '',
        'admin_chat_id' => '',
    ]
];
