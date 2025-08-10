<?php
require_once '../init.php';

$response = array();
// echo json_encode(["debug" => $_POST]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'crearusuario':
                if (isset($data['dataUsuario'])) {
                    $aForm = $data['dataUsuario'];
                    $resultado = crearUsuario($aForm, $data['idUsuario']);
                    $response['lo que llega'] = $resultado;
                    if($resultado) {
                        $response["mensaje"] = "Usuario guardado con éxito";
                    } else {
                        $response["error"] = "Error al guardar el usuario";
                    }
                }
                break;
            case 'verusuario':
                if (isset($data['id'])) {
                    $usuario = verUsuario($data['id']);
                    if ($usuario) {
                        $response["usuario"] = $usuario;
                    } else {
                        $response["error"] = "No se encontró el usuario";
                    }
                }
                break;
            case 'obtenerusuarios':
                $usuarios = obtenerUsuarios();
                if ($usuarios) {
                    $response["usuarios"] = $usuarios;
                } else {
                    $response["error"] = "No se encontraron usuarios";
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

function crearUsuario($params, $id)
{
    global $db;
    error_log("crearUsuario: " . json_encode($params));
    $r = false;
    $idUsuario = $id ?? 0;
    $nombres = $params['nombres'] ?? '';
    $apellidos = $params['apellidos'] ?? '';
    $cedula = $params['cedula'] ?? '';
    $usuario = $params['usuario'] ?? '';
    $rol = $params['rol'] ?? '';
    $claveInput = $params['clave'];
    $claveHash = password_hash($claveInput, PASSWORD_BCRYPT);

    $sql= "SELECT * FROM usuarios WHERE id=" . $idUsuario;
    $resU = $db->Execute($sql);
    $dataU = array(
        "nombres" => $nombres,
        "apellidos" => $apellidos,
        "cedula" => $cedula,
        "usuario" => $usuario,
        "clave" => $claveHash,
    );
    try {
        $db->StartTrans();
        if ($resU && $resU->RecordCount() == 0) {
            $dataU["activo"] = 1;
            $cmdsql = $db->GetInsertSQL($resU, $dataU) . ' RETURNING id';
        } else {
            $cmdsql = $db->GetUpdateSQL($resU, $dataU);
        }

        if (isset($cmdsql) && $cmdsql !== false) {
            $resIns = $db->Execute($cmdsql);
            if ($idUsuario == 0) {
                $idUsuario = $resIns->fields['id'];
            }
        }

        if ($idUsuario) {
            $sql1 = "SELECT * FROM usuariorol WHERE idusuario = " . $idUsuario;
            $resUR = $db->Execute($sql1);
            $dataUR = array(
                "idrol" => $rol
            );

            if ($resUR && $resUR->RecordCount() == 0) {
                $dataUR["idusuario"] = $idUsuario;
                $cmdsql1 = $db->GetInsertSQL($resUR, $dataUR);
            } else {
                $cmdsql1 = $db->GetUpdateSQL($resUR, $dataUR);
            }

            if (isset($cmdsql1) && $cmdsql1 !== false) {
                $db->Execute($cmdsql1);
            }
        }
        $db->CompleteTrans();
        $r = true;
    } catch (Exception $e) {
        $db->FailTrans();
        $db->CompleteTrans();
        $r = false;
    }

    return $r;
}

function verUsuario($id)
{
    global $db;
    $sqlUsuario = "SELECT u.*, ur.idrol as rol FROM usuarios AS u ".
    "INNER JOIN usuariorol AS ur ON (u.id=ur.idusuario) ".
    "WHERE u.id=" . $id;
    $result = $db->GetArray($sqlUsuario);
    return $result;
}

function obtenerUsuarios() {
    global $db;
    try {
        $sql = "SELECT u.id, u.usuario, CONCAT(u.nombres, ' ', u.apellidos) AS nombrecompleto, r.nombre AS rol FROM usuarios AS u ".
        "INNER JOIN usuariorol AS ur ON (u.id=ur.idusuario) ".
        "INNER JOIN roles AS r ON (ur.idrol=r.id) ".
        "ORDER BY u.nombres";
        $result = $db->Execute($sql);
        error_log("query: " . $sql);
        if ($result) {
            $usuarios = $result->GetArray();
            return json_encode($usuarios);
        } else {
            return json_encode(["error" => "No se encontraron usuarios"]);
        }
    } catch (PDOException $e) {
        return json_encode(["error" => "Error al obtener los usuarios: " . $e->getMessage()]);
    }
}