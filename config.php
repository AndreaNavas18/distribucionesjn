<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/database.php';

$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/distribucionesjn';
define('BASE_URL', rtrim($baseUrl, '/'));
