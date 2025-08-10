<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../helpers/permisos.php';

if (!isset($_SESSION['idusuario'])) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname($scriptName);

    if (basename($basePath) === 'vistas') {
        $basePath = dirname($basePath);
    }

    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }

    $location = $basePath . '/loginview.php';

    error_log("Redirigiendo a $location");
    header("Location: $location");
    exit;
}