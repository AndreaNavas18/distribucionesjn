<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/vendor/autoload.php';

$appEnv = getenv('APP_ENV') ?: 'local';

if ($appEnv === 'local') {
    $dotenvPath = __DIR__ . '/.env';
    if (file_exists($dotenvPath)) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}

$baseUrl = getenv('BASE_URL') ?: 'http://localhost/distribucionesjn';
define('BASE_URL', rtrim($baseUrl, '/'));

require_once __DIR__ . '/database.php';