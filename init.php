<?php
require_once __DIR__ . '/config.php';

$appOrigin = $_ENV['APP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: {$appOrigin}");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
