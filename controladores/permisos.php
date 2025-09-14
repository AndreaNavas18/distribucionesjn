<?php
require_once '../init.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);

        switch ($funcion) {
            case 'obtenerroles':
                $roles = obtenerRoles();
                if ($roles) {
                    $response['roles'] = $roles;
                } else {
                    $response['error'] = "No se encontraron roles";
                }
                break;

            case 'obtenerusuarios':
                $usuarios = obtenerUsuariosPermisos();
                if ($usuarios) {
                    $response['usuarios'] = $usuarios;
                } else {
                    $response['error'] = "No se encontraron usuarios";
                }
                break;

            case 'obtenerpermisos':
                $permisos = obtenerPermisos();
                if ($permisos) {
                    $response['permisos'] = $permisos;
                } else {
                    $response['error'] = "No se encontraron permisos";
                }
                break;

            case 'permisosrol':
                if (isset($data['idRol'])) {
                    $todos = obtenerPermisos();
                    $asignados = permisosRol($data['idRol']);
                    $response['permisos'] = $todos;
                    $response['asignados'] = $asignados;
                } else {
                    $response['error'] = "Falta el idRol";
                }
                break;

            case 'permisosusuario':
                if (isset($data['idUsuario'])) {
                    $todos = obtenerPermisos();
                    $asignados = permisosUsuario($data['idUsuario']);
                    $response['permisos'] = $todos;
                    $response['asignados'] = $asignados;
                } else {
                    $response['error'] = "Falta el idUsuario";
                }
                break;

            case 'guardarpermisosrol':
                if (isset($data['idRol'])) {
                    $ok = guardarPermisosRol($data['idRol'], $data['permisos'] ?? []);
                    $response['success'] = $ok;
                } else {
                    $response['error'] = "Falta el idRol";
                }
                break;

            case 'guardarpermisosusuario':
                if (isset($data['idUsuario'])) {
                    $ok = guardarPermisosUsuario($data['idUsuario'], $data['permisos'] ?? []);
                    $response['success'] = $ok;
                } else {
                    $response['error'] = "Falta el idUsuario";
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
    $response["error"] = "Método no permitido";
}

echo json_encode($response);

// ================= FUNCIONES =================

function obtenerRoles() {
    global $db;
    $sql = "SELECT id, nombre FROM roles ORDER BY nombre";
    $res = $db->Execute($sql);
    return $res ? $res->GetArray() : [];
}

function obtenerUsuariosPermisos() {
    global $db;
    $sql = "SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre FROM usuarios ORDER BY nombres";
    $res = $db->Execute($sql);
    return $res ? $res->GetArray() : [];
}

function obtenerPermisos() {
    global $db;
    $sql = "SELECT id, nombre FROM permisos ORDER BY nombre";
    $res = $db->Execute($sql);
    return $res ? $res->GetArray() : [];
}

function permisosRol($idRol) {
    global $db;
    $sql = "SELECT idpermiso FROM rolpermiso WHERE idrol = ?";
    $res = $db->Execute($sql, [$idRol]);
    return $res ? array_map('intval', array_column($res->GetArray(), 'idpermiso')) : [];
}

function permisosUsuario($idUsuario) {
    global $db;
    $sql = "SELECT idpermiso FROM usuariopermiso WHERE idusuario = ?";
    $res = $db->Execute($sql, [$idUsuario]);
    return $res ? array_map('intval', array_column($res->GetArray(), 'idpermiso')) : [];
}

function guardarPermisosRol($idRol, $permisos) {
    global $db;
    try {
        $db->StartTrans();
        $db->Execute("DELETE FROM rolpermiso WHERE idrol = ?", [$idRol]);
        foreach ($permisos as $idPermiso) {
            $db->Execute("INSERT INTO rolpermiso (idrol, idpermiso) VALUES (?, ?)", [$idRol, $idPermiso]);
        }
        $db->CompleteTrans();
        return true;
    } catch (Exception $e) {
        $db->FailTrans();
        $db->CompleteTrans();
        return false;
    }
}

function guardarPermisosUsuario($idUsuario, $permisos) {
    global $db;
    try {
        $db->StartTrans();
        $db->Execute("DELETE FROM usuariopermiso WHERE idusuario = ?", [$idUsuario]);
        foreach ($permisos as $idPermiso) {
            $db->Execute("INSERT INTO usuariopermiso (idusuario, idpermiso) VALUES (?, ?)", [$idUsuario, $idPermiso]);
        }
        $db->CompleteTrans();
        return true;
    } catch (Exception $e) {
        $db->FailTrans();
        $db->CompleteTrans();
        return false;
    }
}
