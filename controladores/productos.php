<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$response = array();
// echo json_encode(["debug" => $_POST]);
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'obtenerproductos':
                $response['productos'] = obtenerProductos();
            break;

            case 'buscarproductos':
                if (isset($data['query'])) {
                    $aForm = $data['query'];
                    $response['productos'] = buscarProductos($aForm);
                }
            break;

            case 'obtenerproveedores':
                $response['proveedores'] = obtenerProveedores();
            break;

            case 'verproducto':
                if (isset($data['idproducto'])) {
                    $aForm = $data['idproducto'];
                    $response['producto'] = verProducto($aForm);
                }
            break;

            case 'editarproducto':
                if (isset($data['producto'])) {
                    error_log("editarproducto: " . print_r($data['producto'], true));
                    $aForm = $data['producto'];
                    $response['producto'] = editarProducto($aForm);
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
        return ["error" => "Error al obtener productos: " . $e->getMessage()];
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
        return $result->GetArray();
    } else {
        return ["mensaje" => "No se encontraron proveedores"];
    }
}

function verProducto($idProducto) {
    global $db;
    $sqlProducto = "SELECT id, nombre, precioventa, costo FROM productos WHERE id=" . $idProducto;
    $result = $db->GetArray($sqlProducto);
    if (count($result) > 0) {
        error_log("result 00 " . print_r($result[0], true));
        return $result[0];
    } else {
        return ["mensaje" => "No se encontró el producto"];
    }
}

function editarProducto($aForm) {
    global $db;

    $valores = json_decode($aForm, true);
    $query = "SELECT id, nombre, precioventa, costo FROM productos WHERE id=" . $valores['id'];
    $result = $db->Execute($query);
    $registro = array(
        'id' => $valores['id'],
        'nombre' => $db->addQ($valores['nombre']),
        'precioventa' => $valores['precioventa'],
        'costo' => $valores['costo']
    );
    if ($result && $result->RecordCount() > 0) {
        $sqlUpdate = $db->GetUpdateSQL($result, $registro);
    }
    $db->StartTrans();
    if (isset($sqlUpdate) && $sqlUpdate !== false) {
        error_log("sqlUpdate: " . $sqlUpdate);
        $executeUpdate = $db->Execute($sqlUpdate);
        if ($executeUpdate) {
            $db->CompleteTrans();
            return ["mensaje" => "Producto editado con éxito"];
        } else {
            $db->FailTrans();
            return ["error" => "Error al editar el producto"];
        }
    } else {
        return ["mensaje" => "No se encontraron cambios para editar"];
    }
    $db->CompleteTrans();
}