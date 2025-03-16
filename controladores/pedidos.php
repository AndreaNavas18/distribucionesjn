<?php
require_once '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$response = array();
// echo json_encode(["debug" => $_POST]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['funcion'])) {
        $funcion = strtolower($data['funcion']);
        switch ($funcion) {
            case 'guardarpedido':
                if (isset($data['datosForm'])) {
                    $aForm = $data['datosForm'];
                    $resultado = guardarPedido($aForm);
                    $response['lo que llega'] = $resultado;
                    if($resultado) {
                        $response["mensaje"] = "Pedido guardado con éxito";
                    } else {
                        $response["error"] = "Error al guardar el pedido";
                    }
                }
            break;

            case 'obtenerpedidos':
                $pedidos = obtenerPedidos();
                if ($pedidos) {
                    $response["pedidos"] = $pedidos;
                } else {
                    $response["error"] = "No se encontraron pedidos";
                }
            break;

            case 'verordencompra':
                if (isset($data['datosForm'])) {
                    $aForm = $data['datosForm'];
                    $ordenCompra = verOrdenCompra($aForm);
                    if ($ordenCompra) {
                        $response["orden"] = $ordenCompra;
                    } else {
                        $response["error"] = "No se encontraron pedidos";
                    }
                }
            break;

            case 'verpedido':
                if (isset($data['id'])) {
                    $id = $data['id'];
                    $pedido = verPedido($id);
                    error_log("lo que llega pedido " . print_r($pedido, true));
                    if ($pedido) {
                        $response["pedido"] = $pedido['pedido'];
                        $response["detalle"] = $pedido['detalle'];
                    } else {
                        $response["error"] = "No se encontró el pedido";
                    }
                } else {
                    $response["error"] = "No se especificó el ID del pedido";
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

function  guardarPedido($aForm) {
    global $db;
    $r = false;
    $productos = $aForm['productos'];
    $cliente = $aForm['cliente'];
    $observacion = $aForm['observacion'];
    $idPedidoEdita = $aForm['idPedido'];
    error_log("aForm: " . json_encode($aForm));
   
    try {
        foreach ($productos as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];
            $preciofinal = $producto['preciofinal'];
            $observacionproducto = $producto['observacionproducto'];
            $sugerido = $producto['preciosugerido'];

            $db->StartTrans();
            if ($idPedidoEdita) {
                $whereEdita = " AND id=" . $idPedidoEdita;
            } else {
                $whereEdita = "";
            }

            $sqlPedido = "SELECT id, idcliente, fecha, total, observacion FROM pedidos WHERE id=0" . $whereEdita;
            $pedido = $db->Execute($sqlPedido);
            error_log("sqlPedido: " . $sqlPedido);
            $registroPedido = [
                "idcliente" => $cliente,
                "total" => $preciofinal,
                "observacion" => $observacion
            ];
            
            if ($pedido->RecordCount() > 0) {
                error_log("Pedido ya existe");
                $sqlUpdatePedido = $db->GetUpdateSQL($pedido, $registroPedido);
                $db->Execute($sqlUpdatePedido);
                $idPedido = $idPedidoEdita;
            } else {
                error_log("Pedido no existe");
                $registroPedido['fecha'] = date('Y-m-d');
                $sqlInsertPedido = $db->GetInsertSQL($pedido, $registroPedido);
                error_log("sqlInsertPedido: " . $sqlInsertPedido);
                $db->Execute($sqlInsertPedido);
                $idPedido = $db->Insert_ID();
            }

            error_log("idPedido: " . $idPedido);

            $sqlDetalle = "SELECT idpedido, idproducto, cantidad, observacionproducto, preciosugerido FROM detallepedidosfacturas WHERE idpedido=" . $idPedido;
            error_log("sqlDetalle: " . $sqlDetalle);
            $detalle = $db->Execute($sqlDetalle);
            $registroDetalle = [
                "idpedido" => $idPedido,
                "idproducto" => $idProducto,
                "cantidad" => $cantidad,
                "observacionproducto" => $observacionproducto,
                "preciosugerido" => $sugerido
            ];

            if ($detalle->RecordCount() > 0) {
                $sqlUpdateDetalle = $db->GetUpdateSQL($detalle, $registroDetalle);
                $db->Execute($sqlUpdateDetalle);
            } else {
                $sqlInsertDetalle = $db->GetInsertSQL($detalle, $registroDetalle);
                $db->Execute($sqlInsertDetalle);
            }
            
            $db->CompleteTrans();
        }
        
        $r = true;

    } catch (Exception $e) {
        $db->FailTrans();
        $db->CompleteTrans();

        error_log("Error en guardarPedido: " . $e->getMessage());
        $r = false;
    }

    return $r;
}

function obtenerPedidos() {
    global $db;

    try {
        $sqlPedidos = "SELECT * FROM pedidos ORDER BY id ASC";
        $pedidos = $db->GetArray($sqlPedidos);

        if (count($pedidos) > 0) {
            foreach ($pedidos as &$pedido) {
                $sqlCliente = "SELECT nombre FROM clientes WHERE id = ?";
                $cliente = $db->GetOne($sqlCliente, [$pedido['idcliente']]);
                $pedido['cliente'] = $cliente;
            }

            return json_encode($pedidos);
        } else {
            return json_encode(["mensaje" => "No se encontraron pedidos."]);
        }

    } catch (Exception $e) {
        error_log("Error en obtenerPedidos: " . $e->getMessage());
        return json_encode(["error" => "Error al obtener pedidos."]);
    }
}

function verOrdenCompra($aForm) {
    global $db;

    $fechaini = isset($aForm['fechaInicio']) ? $aForm['fechaInicio'] : null;
    $fechafin = isset($aForm['fechaFin']) && $aForm['fechaFin'] !== "" ? $aForm['fechaFin'] : $fechaini;
    error_log("fechaini: " . $fechaini . " fechafin: " . $fechafin);
    error_log("aForm: " . json_encode($aForm));

    if (($fechaini && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaini)) || ($fechafin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechafin))) {
        return "Error: Fecha no válida.";
    }

    $rutas = isset($aForm['ruta']) && is_array($aForm['ruta']) ? array_map('intval', $aForm['ruta']) : [];
    $proveedores = isset($aForm['proveedor']) && is_array($aForm['proveedor']) ? array_map('intval', $aForm['proveedor']) : [];

    try {
        if (!empty($rutas)) {
            $rutasql = " AND cl.ruta IN (" . implode(",", $rutas) . ")";
            $orderruta = " , cl.ruta";
            $selectruta = "cl.ruta, ";
        } else {
            $rutasql = "";
            $orderruta = "";
            $selectruta = "";
        }

        if (!empty($proveedores)) {
            $pvsql = " AND pv.id IN (" . implode(",", $proveedores) . ")";
        } else {
            $pvsql = "";
        }

        $sql = "SELECT $selectruta pod.nombre, SUM(dep.cantidad) AS cantidad, pod.costo, pv.proveedor, ".
            "STRING_AGG(DISTINCT dep.observacionproducto, ' - ' ORDER BY dep.observacionproducto) AS observacion ".
            "FROM productos pod ".
            "INNER JOIN detallepedidosfacturas dep ON pod.id = dep.idproducto ".
            "INNER JOIN pedidos ped ON dep.idpedido = ped.id ".
            "LEFT JOIN clientes cl ON ped.idcliente = cl.id ".
            "LEFT JOIN proveedores pv ON pod.idproveedor = pv.id ".
            "WHERE ped.fecha BETWEEN '" . $fechaini . "' AND '" . $fechafin ."' ". $rutasql . $pvsql .
            " GROUP BY $selectruta pod.nombre, pod.costo, pv.proveedor ".
            "ORDER BY pod.nombre $orderruta";
        error_log("SQL: " . $sql);
        $result = $db->GetArray($sql);

        return $result ? json_encode($result) : json_encode(["mensaje" => "No se encontraron resultados."]);

    } catch (Exception $e) {
        error_log("Error en verOrdenCompra: " . $e->getMessage());
        return json_encode(["error" => "Error al obtener la orden de compra."]);
    }
}

function verPedido($idPedido) {
    global $db;

    $sqlPedido = "SELECT id, idcliente, observacion FROM pedidos WHERE id =" . $idPedido;
    $pedido = $db->GetRow($sqlPedido);

    $sqlDetalle = "SELECT dp.idproducto, dp.cantidad, dp.observacionproducto, dp.estado, dp.preciosugerido, pr.nombre, pr.precioventa FROM detallepedidosfacturas dp ".
    "LEFT JOIN productos pr ON dp.idproducto = pr.id ".
    "WHERE dp.idpedido =" . $idPedido;
    $detallepedido = $db->GetArray($sqlDetalle);

    return $pedido ? ["pedido" => $pedido, "detalle" => $detallepedido] : false;
}