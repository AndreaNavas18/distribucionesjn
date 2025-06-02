<?php
require_once 'vendor/adodb/adodb-php/adodb.inc.php';

$dbType = 'pgsql';
$host = '127.0.0.1';
//base de datos original
// $database = 'distribucionesjn';
//base de datos de pruebas
$database = 'pruebasdistribucionesjn';
$username = 'postgres';
$password = 'postgres';

$db = ADONewConnection($dbType);

$db->Connect($host, $username, $password, $database);

if (!$db->Connect($host, $username, $password, $database)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error al conectar a la base de datos."]);
    exit;
}
