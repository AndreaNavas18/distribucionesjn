<?php
session_start();
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/helpers/permisos.php';

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
                error_log("Usuario: $usuarioInput, Clave: $claveInput");
                $sql = "SELECT id, usuario, clave FROM usuarios WHERE usuario='".$usuarioInput."'";
                $usuario = $db->GetRow($sql);
                error_log("query: ".$sql);
                if ($usuario && password_verify($claveInput, $usuario['clave'])) {
                    $_SESSION['idusuario'] = $usuario['id'];
                    $_SESSION['usuario'] = $usuario['usuario'];
                    cargarPermisosUsuario($usuario['id'], $db);
                    $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                    $response['redirect'] = "{$baseUrl}/vistas/homeview.php";
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
error_log("Respuesta que se enviará: " . print_r($response, true));
echo json_encode($response);