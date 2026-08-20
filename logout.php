<?php

require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Services/AuthService.php';
require_once __DIR__ . '/src/Core/SessionManager.php';

use FMSS\Services\AuthService;
use FMSS\Core\SessionManager;

$authService = new AuthService();
$authService->logout();
SessionManager::clearSessionCookie();

header("Location: login.php");
exit;