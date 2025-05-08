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
            case 'guardarprefactura':
                if(isset($data['idpedido']) && isset($data['cambios'])) {
                    $result = guardarPrefactura($data['idpedido'], $data['cambios']);
                    if ($result) {
                        $response["mensaje"] = $result;
                    } else {
                        $response["error"] = "Error al guardar la prefactura";
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

function guardarPrefactura ($idpedido, $cambios)
{
    global $db;
    error_log("ID Pedido: " . $idpedido);
    error_log("cambios " . print_r($cambios, true));
    $mensaje = "";
    $error = "";
    //Toca recorrer cambios
    foreach($cambios as $detalle) {
        $id = $detalle['iddetalle'];
        $empacada = $detalle['cantidadempacada'];
        $observacion = $detalle['observacion'];

        $sqlDetalle1 = "SELECT id, idpedido, cantidad, faltante, observacionproducto FROM detallepedidosfacturas WHERE id=" . $id;
        error_log("SQL Detalle 1: " . $sqlDetalle1);
        $executeDetalle1 = $db->Execute($sqlDetalle1);
        if ($executeDetalle1 && $executeDetalle1->RecordCount() > 0) {
            $cantidad = $executeDetalle1->fields['cantidad'];
            if ($empacada == 0) {
                $faltante = $cantidad;
            } elseif ($empacada > 0 && $empacada < $cantidad) {
                $faltante = $cantidad - $empacada;
            } else {
                $faltante = "";
            }
            $registro = array(
                'faltante' => $faltante,
                'observacionproducto' => $observacion,
            );
            $sqlDetalle2 = $db->GetUpdateSQL($executeDetalle1, $registro);
            error_log("SQL Detalle 2: " . $sqlDetalle2);
            $sqlPedido = "SELECT id, estado FROM pedidos WHERE id=" . $idpedido;
            error_log("SQL Pedido: " . $sqlPedido);
            $executePedido = $db->Execute($sqlPedido);
            if ($executePedido && $executePedido->RecordCount() > 0) {
                $reg = array(
                    'estado' => 1
                );
                $sqlPedido2 = $db->GetUpdateSQL($executePedido, $reg);
            }
            if ($sqlDetalle2) {
                $db->StartTrans();
                $db->Execute($sqlDetalle2);
                $db->Execute($sqlPedido2);
                $db->CompleteTrans();
                if ($db->ErrorMsg()) {
                    error_log("Error al guardar el detalle: " . $db->ErrorMsg());
                    $error .= "Error al guardar el detalle: " . $db->ErrorMsg() . "\n";
                } else {
                    $error .= "";
                }
            }
        }
    }
    if ($error) {
        $mensaje = "Se encontraron errores al guardar la prefactura: " . $error;
    } else {
        $mensaje = "Prefactura guardada con éxito";
    }
    error_log("Mensaje: " . $mensaje);
    return $mensaje;
}
