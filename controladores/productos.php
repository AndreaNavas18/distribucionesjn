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

            case 'obtenerproveedores':
                obtenerProveedores();
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
    global $db;

    try {
        $sql = "SELECT id, nombre, precioventa, costo FROM productos";
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

function buscarProductos($query) {
    global $db;

    try {
        if (empty($query)) {
            $sql = "SELECT id, nombre FROM productos";
            $productos = $db->GetArray($sql);
        } else {
            $query = strtoupper($query);
            $sql = "SELECT id, nombre FROM productos WHERE UPPER(nombre) LIKE ?";
            $productos = $db->GetArray($sql, ["%$query%"]);
        }

        if (count($productos) > 0) {
            echo json_encode($productos);
        } else {
            echo json_encode(["mensaje" => "No se encontraron productos"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al buscar productos: " . $e->getMessage()]);
    }
}

function obtenerProveedores() {
    global $db;

    $sql = "SELECT id, proveedor FROM proveedores";
    $result = $db->Execute($sql);
    if ($result) {
        $proveedores = $result->GetArray();
        echo json_encode($proveedores);
    } else {
        echo json_encode(["mensaje" => "No se encontraron proveedores"]);
    }
}