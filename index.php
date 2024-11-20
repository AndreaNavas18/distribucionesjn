<?php
require_once './database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if (isset($_GET['funcion'])) {
    $funcion = $_GET['funcion'];

    if ($funcion === 'obtenerproductos') {
        obtenerProductos();
    } else {
        echo json_encode(["error" => "Función no válida"]);
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna función"]);
}

function obtenerProductos() {
    global $database;
    $productos = $database->select('productos', ['codigo','nombre','precioventa','costo']);
    echo json_encode($productos);
}