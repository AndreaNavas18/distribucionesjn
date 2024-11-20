<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// echo json_encode(["debug" => $_POST]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'obtenerproductos':
                obtenerProductos();
            break;

            case 'buscarproductos':
                buscarProductos($data['query']);
            break;

            default:
                echo json_encode(["error" => "Función no válida o no especificada"]);
                break;
            }
    } else {
        echo json_encode(["error" => "No se especificó ninguna función"]);
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna función"]);
}

function obtenerProductos() {
    global $database;
    try {
        $productos = $database->select('productos', ['id','nombre','precioventa','costo']);
        if (count($productos) > 0) {
            echo json_encode($productos);
        } else {
            echo json_encode(["mensaje" => "No se encontraron productos"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al obtener los productos: " . $e->getMessage()]);
    }
}

function buscarProductos($query) {
    global $database;

    if (empty($query)) {
        $productos = $database->select('productos', ['id', 'nombre']);
    } else {
        $query = strtoupper($query);
        $productos = $database->select('productos', ['id','nombre'], ['nombre[~]' => $query]);
    }

    if (count($productos) > 0) {
        echo json_encode($productos);
    } else {
        echo json_encode(["mensaje" => "No se encontraron productos"]);
    }
}