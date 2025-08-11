<?php
require_once __DIR__ . '/vendor/adodb/adodb-php/adodb.inc.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

$dbType = 'pgsql';

$databaseUrl = $_ENV['DATABASE_URL'] ?? null;

if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    $host     = $parts['host'] ?? '127.0.0.1';
    $port     = $parts['port'] ?? '5432';
    $username = $parts['user'] ?? 'postgres';
    $password = $parts['pass'] ?? '';
    $database = ltrim($parts['path'] ?? '', '/');
} else {
    $host     = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port     = $_ENV['DB_PORT'] ?? '5432';
    $database = $_ENV['DB_NAME'] ?? 'pruebasdistribucionesjn';
    $username = $_ENV['DB_USER'] ?? 'postgres';
    $password = $_ENV['DB_PASS'] ?? 'postgres';
}

//ver en un log si hay error en la conexion
error_log("Conectando a la base de datos {$dbType} en {$host}:{$port}, base de datos: {$database}, usuario: {$username}", 0);

$db = ADONewConnection($dbType);

$hostWithPort = $host . ':' . $port;

if (!$db->Connect($hostWithPort, $username, $password, $database)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error al conectar a la base de datos."]);
    exit;
}
