<?php
session_start();
header('Content-Type: application/json');

// Detectar ruta base automáticamente
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);

if (basename($basePath) === 'autenticacion') {
    $basePath = dirname($basePath);
}

$baseUrl = $protocolo . "://" . $host . $basePath;

if (isset($_SESSION['idusuario'])) {
    echo json_encode([
        'sesion' => true,
        'idusuario' => $_SESSION['idusuario'],
        'usuario' => $_SESSION['usuario'],
        'base_url' => $baseUrl
    ]);
} else {
    echo json_encode(['sesion' => false, 'base_url' => $baseUrl]);
}
