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
    $totalPedido = 0;
    //Toca recorrer cambios
    foreach($cambios as $detalle) {
        $id = $detalle['iddetalle'];
        $empacada = $detalle['cantidadempacada'];
        $observacion = $detalle['observacion'];
        $idproducto = isset($detalle['idproducto']) ? $detalle['idproducto'] : null;
        $cantidadDB = $detalle['cantidad'];
        $precio = $detalle['precio'];

        error_log("cantidadDB : " . $cantidadDB);
        error_log("precio : " . $precio);

        $precio = preg_replace('/[^\d]/u', '', $precio);
        $precio = intval($precio);
        error_log("Precio convertido: " . $precio);

        if ($id) {
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
        } elseif ($idproducto) {
            error_log("Insertando nuevo detalle para el producto ID: " . $idproducto);
            $faltante = ($empacada == 0) ? $cantidadDB : (($empacada > 0 && isset($cantidadDB) && $empacada < $cantidadDB) ? $cantidadDB - $empacada : "");
            $registroInsert = array(
                'idpedido' => $idpedido,
                'idproducto' => $idproducto,
                'cantidad' => $cantidadDB,
                'faltante' => $faltante,
                'observacionproducto' => $observacion,
            );
            $sqlInsertDummy = "SELECT idpedido, idproducto, cantidad, faltante, observacionproducto FROM detallepedidosfacturas WHERE 1=0";
            $dummy = $db->Execute($sqlInsertDummy);
            error_log("SQL Insert Dummy: " . $sqlInsertDummy);
            $sqlInsert = $db->GetInsertSQL($dummy, $registroInsert);
            error_log("SQL Insert: " . $sqlInsert);
            if ($sqlInsert) {
                $db->StartTrans();
                $db->Execute($sqlInsert);
                $db->CompleteTrans();
                if ($db->ErrorMsg()) {
                    $error .= "Error al insertar el detalle: " . $db->ErrorMsg() . "\n";
                }
            }

            // Actualizar el estado del pedido y hacer el calculo del total de pedido
            $sqlp = "SELECT id, estado, total FROM pedidos WHERE id=" . $idpedido;
            $resultsqlp = $db->Execute($sqlp);
            error_log("SQL Pedido para actualizar: " . $sqlp);
            if ($resultsqlp && $resultsqlp->RecordCount() > 0) {
                if ($cantidadDB && $precio) {
                    $totalPedido = $resultsqlp->fields['total'] + $precio;
                    error_log("precio total " . $totalPedido);
                    $regPedido = array(
                        'estado' => 1,
                        'total' => $totalPedido
                    );
                    $sqlUp = "UPDATE pedidos SET ESTADO=1, TOTAL=". $totalPedido ." WHERE id=" . $idpedido . " RETURNING id";
                    error_log("SQL Pedido Update: " . $sqlUp);
                    $rr = $db->Execute($sqlUp);
                    error_log("Resultado de la actualización del pedido: " . $rr->fields['id']);
                    error_log($db->ErrorMsg());


                    // $sqlPedidoUpdate = $db->GetUpdateSQL($resultsqlp, $regPedido);
                    // error_log("SQL Pedido Update: " . $sqlPedidoUpdate);
                    // if ($sqlPedidoUpdate) {
                    //     error_log("Actualizando pedido con ID: " . $idpedido);
                    //     $db->StartTrans();
                    //     $db->Execute($sqlPedidoUpdate);
                    //     $db->CompleteTrans();
                    //     if ($db->ErrorMsg()) {
                    //         $error .= "Error al actualizar el pedido: " . $db->ErrorMsg() . "\n";
                    //     }
                    // }
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
