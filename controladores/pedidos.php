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
                    $result = guardarPedido($aForm);
                    if ($result) {
                        $response["mensaje"] = "Pedido guardado con éxito";
                    } else {
                        $response["error"] = "Error al guardar el pedido";
                    }
                }
            break;

            case 'obtenerpedidos':
                $response["pedidos"] = obtenerPedidos();
            break;

            case 'verordencompra':
                if (isset($data['datosForm'])) {
                    $aForm = $data['datosForm'];
                    $response["orden"] = verOrdenCompra($aForm);
                }
            break;

            case 'verpedido':
                if (isset($data['id'])) {
                    $id = $data['id'];
                    $pedido = verPedido($id);
                    error_log("lo que llega " . print_r($pedido, true));
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
    $productosExistentes = [];
    $r = false;
    $productos = $aForm['productos'];
    $cliente = $aForm['cliente'];
    $observacion = $aForm['observacion'];
    $idPedidoEdita = $aForm['idPedido'];
    if ($idPedidoEdita) {
        $sqlActuales = "SELECT idproducto, cantidad, observacionproducto, preciosugerido, noorden ".
        "FROM detallepedidosfacturas ".
        "WHERE idpedido = " . intval($idPedidoEdita);
        $resActuales = $db->Execute($sqlActuales);

        while (!$resActuales->EOF) {
            $idProd = $resActuales->fields['idproducto'];
            $productosExistentes[$idProd] = [
                "cantidad" => (int)$resActuales->fields['cantidad'],
                "observacionproducto" => $resActuales->fields['observacionproducto'],
                "preciosugerido" => (float)$resActuales->fields['preciosugerido'],
                "noorden" => $resActuales->fields['noorden'] == 1 ? 1 : null
            ];
            $resActuales->MoveNext();
        }
        $whereEdita = "id=" . $idPedidoEdita;
    } else {
        $whereEdita = "id=0";
    }
    error_log("aForm: " . print_r($aForm, true));  
    try {
        $db->StartTrans();
        $sqlPedido = "SELECT id, idcliente, fecha, total, observacion FROM pedidos WHERE " . $whereEdita;
        $pedido = $db->Execute($sqlPedido);
        $registroPedido = [
            "idcliente" => $cliente,
            "total" => $aForm['total'],
            "observacion" => $observacion
        ];
        
        if ($pedido && $pedido->RecordCount() > 0) {
            $sqlUpdatePedido = $db->GetUpdateSQL($pedido, $registroPedido);
            $db->Execute($sqlUpdatePedido);
            $idPedido = $idPedidoEdita;
        } else {
            $registroPedido['fecha'] = date('Y-m-d');
            $sqlInsertPedido = $db->GetInsertSQL($pedido, $registroPedido);
            $db->Execute($sqlInsertPedido);
            $idPedido = $db->Insert_ID();
        }
        error_log("productos: " . print_r($productos, true));
        foreach ($productos as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];
            $observacionproducto = $producto['observacionproducto'];
            $sugerido = $producto['preciosugerido'];
            $noorden = $producto['noorden'];
            error_log("no orden : " . $noorden);

            if (isset($productosExistentes[$idProducto])) {
                // Comparamos para ver si hay cambios
                $actual = $productosExistentes[$idProducto];

                if ($actual['cantidad'] != $cantidad ||
                    $actual['observacionproducto'] !== $observacionproducto ||
                    $actual['preciosugerido'] != $sugerido || $actual['noorden'] != $noorden) {
                    
                    $sqlDetalle = "SELECT idpedido, idproducto, cantidad, observacionproducto, preciosugerido, noorden ".
                    "FROM detallepedidosfacturas ".
                    "WHERE idpedido = $idPedido AND idproducto = $idProducto";
                    $detalle = $db->Execute($sqlDetalle);
                    error_log("sql UPDATE Detalle: " . $sqlDetalle);

                    $registroDetalle = [
                        "cantidad" => $cantidad,
                        "observacionproducto" => $observacionproducto,
                        "preciosugerido" => $sugerido,
                        "noorden" => $noorden
                    ];

                    $sqlUpdate = $db->GetUpdateSQL($detalle, $registroDetalle);
                    if ($sqlUpdate) {
                        $db->Execute($sqlUpdate);
                    }
                }

                // Lo marcamos como procesado para luego saber cuáles eliminar
                unset($productosExistentes[$idProducto]);

            } else {
                // Producto nuevo -> INSERT
                $sqlInsertDummy = "SELECT idpedido, idproducto, cantidad, observacionproducto, preciosugerido, noorden ".
                "FROM detallepedidosfacturas WHERE 1=0";
                $dummy = $db->Execute($sqlInsertDummy);
                error_log("sql INSERT Detalle: " . $sqlInsertDummy);
                $registroInsert = [
                    "idpedido" => $idPedido,
                    "idproducto" => $idProducto,
                    "cantidad" => $cantidad,
                    "observacionproducto" => $observacionproducto,
                    "preciosugerido" => $sugerido,
                    "noorden" => $noorden
                ];

                $sqlInsert = $db->GetInsertSQL($dummy, $registroInsert);
                $db->Execute($sqlInsert);
            }
            error_log("Producto $idProducto actualizado");
            error_log("Producto $idProducto insertado");
            error_log("Producto $idProducto eliminado");
            
        }
        foreach ($productosExistentes as $idProductoEliminar => $datos) {
            $sqlDelete = "DELETE FROM detallepedidosfacturas WHERE idpedido = $idPedido AND idproducto = $idProductoEliminar";
            $db->Execute($sqlDelete);
        }
        $db->CompleteTrans();
        
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
        $sqlPedidos = "SELECT * FROM pedidos ORDER BY id DESC";
        $pedidos = $db->GetArray($sqlPedidos);

        if (count($pedidos) > 0) {
            foreach ($pedidos as &$pedido) {
                $sqlCliente = "SELECT nombre FROM clientes WHERE id = ?";
                $cliente = $db->GetOne($sqlCliente, [$pedido['idcliente']]);
                $pedido['cliente'] = $cliente;
            }

            return $pedidos;
        } else {
            return ["mensaje" => "No se encontraron pedidos."];
        }

    } catch (Exception $e) {
        error_log("Error en obtenerPedidos: " . $e->getMessage());
        return ["error" => "Error al obtener pedidos."];
    }
}

function verOrdenCompra($aForm) {
    global $db;
    error_log("aForm: " . print_r($aForm, true));

    $fechaini = isset($aForm['fechaInicio']) ? $aForm['fechaInicio'] : null;
    $fechafin = isset($aForm['fechaFin']) && $aForm['fechaFin'] !== "" ? $aForm['fechaFin'] : $fechaini;

    if (($fechaini && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaini)) || ($fechafin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechafin))) {
        return "Error: Fecha no válida.";
    }

    $proveedores = isset($aForm['proveedor']) && is_array($aForm['proveedor']) 
    ? array_filter(array_map('intval', $aForm['proveedor']), function($v) { return $v > 0; })
    : [];

    $rutas = isset($aForm['ruta']) && is_array($aForm['ruta'])
    ? array_filter(array_map('intval', $aForm['ruta']), function($v) { return $v > 0; })
    : [];

    $pedidosFiltrados = isset($aForm['pedidos']) && is_array($aForm['pedidos'])
    ? array_filter(array_map('intval', $aForm['pedidos']), function($v) { return $v > 0; })
    : [];

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

        $pedidosql = !empty($pedidosFiltrados)
        ? " AND ped.id IN (" . implode(",", $pedidosFiltrados) . ")"
        : "";

        $sql = "SELECT $selectruta pod.nombre, SUM(dep.cantidad) AS cantidad, pod.costo, pv.proveedor, ".
        "STRING_AGG(DISTINCT dep.observacionproducto, ' - ' ORDER BY dep.observacionproducto) AS observacion ".
        "FROM productos pod ".
        "INNER JOIN detallepedidosfacturas dep ON pod.id = dep.idproducto ".
        "INNER JOIN pedidos ped ON dep.idpedido = ped.id ".
        "LEFT JOIN clientes cl ON ped.idcliente = cl.id ".
        "LEFT JOIN proveedores pv ON pod.idproveedor = pv.id ".
        "WHERE ped.fecha BETWEEN '" . $fechaini . "' AND '" . $fechafin ."' ". $rutasql . $pvsql . $pedidosql .
        " AND (dep.noorden != 1 OR dep.noorden IS NULL) GROUP BY $selectruta pod.nombre, pod.costo, pv.proveedor ".
        "ORDER BY pv.proveedor $orderruta";
        error_log("SQL ORDEN: " . $sql);
        $result = $db->GetArray($sql);

        $sqlpedidos = "SELECT ped.id, ped.fecha, cl.nombre AS cliente, cl.ubicacion FROM pedidos ped ".
        "LEFT JOIN clientes cl ON ped.idcliente = cl.id ".
        "WHERE ped.fecha BETWEEN '$fechaini' AND '$fechafin' " . $rutasql .
        " GROUP BY ped.id, ped.fecha, cl.nombre, cl.ubicacion ".
        "ORDER BY ped.fecha DESC";
        $pedidos = $db->GetArray($sqlpedidos);
        error_log("SQL ORDEN Pedidos: " . $sqlpedidos);

        return [
            "orden" => $result ?? [],
            "pedidos" => $pedidos ?? []
        ];

    } catch (Exception $e) {
        error_log("Error en verOrdenCompra: " . $e->getMessage());
        return ["error" => "Error al obtener la orden de compra."];
    }
}

function verPedido($idPedido) {
    global $db;

    $sqlPedido = "SELECT pd.id, pd.idcliente, pd.observacion, cl.nombre as nombrecliente FROM pedidos pd ".
    "LEFT JOIN clientes cl ON cl.id=pd.idcliente ".
    "WHERE pd.id =" . $idPedido;
    $pedido = $db->GetRow($sqlPedido);

    $sqlDetalle = "SELECT dp.id, dp.idproducto, dp.cantidad, dp.observacionproducto, dp.estado, ".
    "dp.preciosugerido, pr.nombre, pr.precioventa, dp.faltante, dp.noorden FROM detallepedidosfacturas dp ".
    "LEFT JOIN productos pr ON dp.idproducto = pr.id ".
    "WHERE dp.idpedido =" . $idPedido;
    $detallepedido = $db->GetArray($sqlDetalle);

    error_log("SQL Pedido: " . $sqlPedido);

    return $pedido ? ["pedido" => $pedido, "detalle" => $detallepedido] : false;
}