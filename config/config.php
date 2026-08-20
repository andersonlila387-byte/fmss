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
        'bot_token' => '8654398823:AAFy12O-i1p1Qe6HEqcnRzrzFsUFGq-wAOM',
        'admin_chat_id' => '1958091339',
    ]
];