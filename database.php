<?php
require_once 'vendor/adodb/adodb-php/adodb.inc.php';

$dbType = 'pgsql';
$host = '127.0.0.1';
$database = 'distribucionesjn';
$username = 'postgres';
$password = 'postgres';

$db = ADONewConnection($dbType);

$db->Connect($host, $username, $password, $database);

if (!$db->Connect($host, $username, $password, $database)) {
    // Devuelve un error en JSON si la conexión falla
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error al conectar a la base de datos."]);
    exit;
}
