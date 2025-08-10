<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../helpers/permisos.php';

if (!isset($_SESSION['idusuario'])) {
    $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $parts = explode('/', trim($baseUrl, '/'));
    if (end($parts) === 'vistas') {
        array_pop($parts);
    }
    $baseUrl = '/' . implode('/', $parts);
    header("Location: {$baseUrl}/loginview.php");
    exit;
}