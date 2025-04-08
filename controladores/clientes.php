<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'crearcliente':
                if (isset($data['dataCliente'])) {
                    $aForm = $data['dataCliente'];
                    $resultado = crearCliente($aForm);
                    $response['lo que llega'] = $resultado;
                    if($resultado) {
                        $response["mensaje"] = "Cliente guardado con éxito";
                    } else {
                        $response["error"] = "Error al guardar el cliente";
                    }
                }
                break;
            
            case 'obtenerclientes':
                $clientes = obtenerClientes();
                if ($clientes) {
                    $response["clientes"] = $clientes;
                } else {
                    $response["error"] = "No se encontraron pedidos";
                }
                break;

            case 'vercliente':
                if (isset($data['id'])) {
                    $cliente = verCliente($data['id']);
                    error_log("lo que llega " . print_r($cliente, true));
                    if ($cliente) {
                        $response["cliente"] = $cliente;
                    } else {
                        $response["error"] = "No se encontró el cliente";
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

function crearCliente($params) {
    global $db;

    $nombre = $params['nombre'] != "" ? strtoupper($params['nombre']) : null;
    $razonsocial = $params['razonsocial'] != "" ? strtoupper($params['razonsocial']) : null;
    $ubicacion = $params['ubicacion'] != "" ? strtoupper($params['ubicacion']) : null;
    $telefono = $params['telefono'] != "" ? $params['telefono'] : null;
    $direccion = $params['direccion'] != "" ? strtoupper($params['direccion']) : null;
    $telefono2 = $params['telefono2'] != "" ? $params['telefono2'] : null;

    try {
        $sql = "INSERT INTO clientes (nombre, razonsocial, ubicacion, telefono, direccion, telefono2)
            VALUES (?, ?, ?, ?, ?, ?)";
        $result = $db->Execute($sql, [$nombre, $razonsocial, $ubicacion, $telefono, $direccion, $telefono2]);
        if ($result) {
            return json_encode(["mensaje" => "Cliente creado con éxito"]);
        } else {
            return json_encode(["error" => "No se pudo insertar el cliente"]);
        }
    } catch (PDOException $e) {
        return json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
    }
}

function obtenerClientes() {
    global $db;
    try {
        $sql = "SELECT id, nombre, razonsocial, ubicacion, telefono, telefono2, ruta FROM clientes";
        $result = $db->Execute($sql);

        if ($result) {
            $clientes = $result->GetArray();
            return json_encode($clientes);
        } else {
            return json_encode(["error" => "No se encontraron clientes"]);
        }
    } catch (PDOException $e) {
        return json_encode(["error" => "Error al obtener los clientes: " . $e->getMessage()]);
    }
}

function verCliente($idCliente) {
    global $db;
    $sqlCliente = "SELECT id, nombre, razonsocial, ubicacion, telefono, direccion, telefono2, ruta FROM clientes WHERE id=" . $idCliente;
    $result = $db->GetArray($sqlCliente);
    error_log(print_r($result[0], true));
    return $result;
}