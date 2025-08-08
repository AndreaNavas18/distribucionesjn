<?php
session_start();
require_once "./database.php";
require_once "./vendor/adodb/adodb.inc.php";

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
            case 'login':
                $usuario = $data['usuario'] ?? '';
                $clave = $data['clave'] ?? '';

                $db = ADONewConnection('postgres');
                $db->Connect('localhost', 'usuario', 'clave', 'nombre_bd');

                $sql = "SELECT id, clave, rol FROM usuarios WHERE usuario = $1";
                $usuario = $db->GetRow($sql, [$usuario]);

                if ($usuario && password_verify($clave, $usuario['clave'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['rol'] = $usuario['rol'];
                    $response['ok'] = true;
                    $response['usuario'] = $usuario['usuario'];
                    $response['rol'] = $usuario['rol'];
                } else {
                    $response['ok'] = false;
                    $response['mensaje'] = 'Credenciales inválidas';
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