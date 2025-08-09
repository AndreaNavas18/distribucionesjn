<?php
session_start();
require_once __DIR__ . '/database.php';

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
                // echo password_hash("123456", PASSWORD_BCRYPT);
                $usuarioInput = $data['usuario'] ?? '';
                $claveInput = $data['clave'] ?? '';

                $sql = "SELECT id, usuario, clave FROM usuarios WHERE usuario='".$usuarioInput."'";
                $usuario = $db->GetRow($sql);

                if ($usuario && password_verify($claveInput, $usuario['clave'])) {
                    $_SESSION['idusuario'] = $usuario['id'];
                    $_SESSION['usuario'] = $usuario['usuario'];
                    $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                    $response['redirect'] = "{$baseUrl}/vistas/home.php";
                    $response['ok'] = true;
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