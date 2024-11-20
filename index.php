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
    global $db;

    try {
        $sql = "SELECT codigo, nombre, precioventa, costo FROM productos";
        $productos = $db->GetArray($sql);

        if (count($productos) > 0) {
            echo json_encode($productos);
        } else {
            echo json_encode(["mensaje" => "No se encontraron productos"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al obtener los productos: " . $e->getMessage()]);
    }
}