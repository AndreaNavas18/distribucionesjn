<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// echo json_encode(["debug" => $_POST]);
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'obtenerproductos':
                $productos = obtenerProductos();
                if ($productos) {
                    $response["productos"] = $productos;
                } else {
                    $response["error"] = "No se encontraron productos";
                }
            break;

            case 'buscarproductos':
                if (isset($data['query'])) {
                    $aForm = $data['query'];
                    $resultado = buscarProductos($aForm);
                    $response['lo que llega'] = $resultado;
                    if($resultado) {
                        $response["mensaje"] = "Éxito";
                    } else {
                        $response["error"] = "Error";
                    }
                }
            break;

            case 'obtenerproveedores':
                $proveedores = obtenerProveedores();
                if ($proveedores) {
                    $response["proveedores"] = $proveedores;
                } else {
                    $response["error"] = "No se encontraron proveedores";
                }
            break;

            case 'crearproducto':
                if (isset($data['dataProducto'])) {
                    $aForm = $data['dataProducto'];
                    $resultado = crearProducto($aForm);
                    $response['lo que llega'] = $resultado;
                    if($resultado) {
                        $response["mensaje"] = "Producto guardado con éxito";
                    } else {
                        $response["error"] = "Error al guardar el producto";
                    }
                }
                break;

            default:
                $response["error"] = "Función no válida o no especificada";
            break;
        }
    } else {
        $response["error"] = "No se especificó ninguna función";
    }
} else {
    $response["error"] = "No se especificó ninguna función";
}

echo json_encode($response);

function obtenerProductos() {
    global $db;

    try {
        $sql = "SELECT id, nombre, precioventa, costo FROM productos";
        $productos = $db->GetArray($sql);

        if (count($productos) > 0) {
            return $productos;
        } else {
            return ["mensaje" => "No se encontraron productos"];
        }
    } catch (Exception $e) {
        return ["error" => "Error al obtener los productos: " . $e->getMessage()];
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
            return $productos;
        } else {
            return ["mensaje" => "No se encontraron productos"];
        }
    } catch (Exception $e) {
        return ["error" => "Error al buscar productos: " . $e->getMessage()];
    }
}

function obtenerProveedores() {
    global $db;

    $sql = "SELECT id, proveedor FROM proveedores";
    $result = $db->Execute($sql);
    if ($result) {
        $proveedores = $result->GetArray();
        return $proveedores;
    } else {
        return ["mensaje" => "No se encontraron proveedores"];
    }
}