<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'crearcliente':
                if (isset($data['dataCliente'])) {
                    crearCliente($data['dataCliente']);
                } else {
                    echo json_encode(["error" => "Datos del cliente no proporcionados"]);
                }
                break;
            
            case 'obtenerclientes':
                obtenerClientes();
                break;

            case 'vercliente':
                if (isset($data['id'])) {
                    $cliente = verCliente($data['id']);
                    error_log("lo que llega " . print_r($cliente, true));
                    // if ($cliente) {
                    //     $response["cliente"] = $cliente;
                    // } else {
                    //     $response["error"] = "No se encontró el cliente";
                    // }
                }
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
            echo json_encode(["mensaje" => "Cliente creado con éxito"]);
        } else {
            echo json_encode(["error" => "No se pudo insertar el cliente"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
    }
}

function obtenerClientes() {
    global $db;
    try {
        $sql = "SELECT id, nombre, razonsocial, ubicacion, telefono, telefono2, ruta FROM clientes";
        $result = $db->Execute($sql);

        if ($result) {
            $clientes = $result->GetArray();
            echo json_encode($clientes);
        } else {
            echo json_encode(["error" => "No se encontraron clientes"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al obtener los clientes: " . $e->getMessage()]);
    }
}

function verCliente($idCliente) {
    global $db;
    $sqlCliente = "SELECT id, nombre, razonsocial, ubicacion, telefono, direccion, telefono2, ruta FROM clientes WHERE id=" . $idCliente;
    $result = $db->GetArray($sqlCliente);
    error_log(print_r($result[0], true));
    return $result;
}