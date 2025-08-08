<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'sesion' => true,
        'usuario_id' => $_SESSION['usuario_id'],
        'rol' => $_SESSION['rol']
    ]);
} else {
    echo json_encode(['sesion' => false]);
}
