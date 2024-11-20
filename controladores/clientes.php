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
            case 'crearcliente':
                if (isset($data['cliente'])) {
                    crearCliente($data['dataCliente']);
                } else {
                    echo json_encode(["error" => "Datos del cliente no proporcionados"]);
                }
                break;
            
            case 'obtenerclientes':
                obtenerClientes();
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
    global $database;

    $nombre = $params['nombre'] != "" ? strtoupper($params['nombre']) : null;
    $razonsocial = $params['razonsocial'] != "" ? strtoupper($params['razonsocial']) : null;
    $ubicacion = $params['ubicacion'] != "" ? strtoupper($params['ubicacion']) : null;
    $telefono = $params['telefono'] != "" ? $params['telefono'] : null;

    try {
        $database->insert('clientes', [
            'nombre' => $nombre,
            'razonsocial' => $razonsocial,
            'ubicacion' => $ubicacion,
            'telefono' => $telefono
        ]);
        echo json_encode(["mensaje" => "Cliente creado con exito"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
    }
}

function obtenerClientes() {
    global $database;
    try {
        $clientes = $database->select('clientes', ['id','nombre','razonsocial','ubicacion','telefono']);
        if (count($clientes) > 0) {
            echo json_encode($clientes);
        } else {
            echo json_encode(["mensaje" => "No se encontraron clientes"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al obtener los clientes: " . $e->getMessage()]);
    }
}