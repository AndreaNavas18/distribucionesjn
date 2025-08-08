<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($_GET['funcion'])) {
    $funcion = $_GET['funcion'];

    if ($funcion === 'registrar') {
        registrarUsuario($data['dataUsuario']);
    } else {
        echo json_encode(["error" => "Función no válida"]);
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna función"]);
}

function registrarUsuario($dataUsuario) {
    global $db;
    $nombre     = trim($dataUsuario['nombre'] ?? '');
    $apellido   = trim($dataUsuario['apellido'] ?? '');
    $cedula     = trim($dataUsuario['cedula'] ?? '');
    $usuario    = trim($dataUsuario['usuario'] ?? '');
    $clave      = $dataUsuario['clave'] ?? '';
    $confirmar  = $dataUsuario['confirmar'] ?? '';

    if ($clave !== $confirmar) {
        echo json_encode(['ok' => false, 'msg' => 'Las contraseñas no coinciden']);
        exit;
    }
    
    if (!$nombre || !$apellido || !$cedula || !$usuario || !$clave) {
        echo json_encode(['ok' => false, 'msg' => 'Todos los campos son obligatorios']);
        exit;
    }
    
    $sqlCheck = "SELECT id FROM usuarios WHERE usuario = ? OR cedula = ?";
    $rs = $db->Execute($sqlCheck, [$usuario, $cedula]);
    
    if ($rs && !$rs->EOF) {
        echo json_encode(['ok' => false, 'msg' => 'El usuario o la cédula ya existen']);
        exit;
    }
    
    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
    
    $sqlInsertU ="SELECT * FROM usuarios WHERE usuario=" . $db->qstr($usuario);
    $rsU = $db->Execute($sqlInsertU);
    if ($rsU && $rsU->RecordCount() == 0) {
        $datos = array(
            'nombre' => $nombre,
            'apellido' => $apellido,
            'cedula' => $cedula,
            'usuario' => $usuario,
            'clave' => $claveHash
        );
        $cmdSQL = $db->GetInsertSQL($sqlInsertU, $datos);
        if ($cmdSQL && $cmdSQL != false) {
            $db->StartTrans();
            $insertado = $db->Execute($cmdSQL);
            if ($insertado) {
                $db->CompleteTrans();
                echo json_encode(['ok' => true, 'msg' => 'Usuario registrado correctamente']);
            } else {
                $db->RollbackTrans();
                echo json_encode(['ok' => false, 'msg' => 'Error al registrar usuario']);
            }
        }
    } else {
        echo json_encode(['ok' => false, 'msg' => 'El usuario ya existe']);
    }
}




