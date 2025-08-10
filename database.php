<?php
require_once 'vendor/adodb/adodb-php/adodb.inc.php';

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

$dbType   = 'pgsql';
$host     = getenv('DB_HOST');
$database = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

$db = ADONewConnection($dbType);

$db->Connect($host, $username, $password, $database);

if (!$db->Connect($host, $username, $password, $database)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error al conectar a la base de datos."]);
    exit;
}
