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
    $r = false;
    $productos = $aForm['productos'];
    $cliente = $aForm['cliente'];
    $observacion = $aForm['observacion'];
    $idPedidoEdita = $aForm['idPedido'];
    if ($idPedidoEdita) {
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
        
        if ($pedido->RecordCount() > 0) {
            $sqlUpdatePedido = $db->GetUpdateSQL($pedido, $registroPedido);
            $db->Execute($sqlUpdatePedido);
            $idPedido = $idPedidoEdita;
        } else {
            $registroPedido['fecha'] = date('Y-m-d');
            $sqlInsertPedido = $db->GetInsertSQL($pedido, $registroPedido);
            $db->Execute($sqlInsertPedido);
            $idPedido = $db->Insert_ID();
        }
        
        foreach ($productos as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];
            $observacionproducto = $producto['observacionproducto'];
            $sugerido = $producto['preciosugerido'];

            $sqlDetalle = "SELECT idpedido, idproducto, cantidad, observacionproducto, preciosugerido ".
            " FROM detallepedidosfacturas WHERE idpedido=" . $idPedido . " AND idproducto=" . $idProducto;
            $detalle = $db->Execute($sqlDetalle);
            $registroDetalle = [
                "idpedido" => $idPedido,
                "idproducto" => $idProducto,
                "cantidad" => $cantidad,
                "observacionproducto" => $observacionproducto,
                "preciosugerido" => $sugerido
            ];

            if ($detalle->RecordCount() > 0) {
                $sqlDetalle2 = $db->GetUpdateSQL($detalle, $registroDetalle);
            } else {
                $sqlDetalle2 = $db->GetInsertSQL($detalle, $registroDetalle);
            }
            if ($sqlDetalle2) {
                error_log("SQL Detalle: " . $sqlDetalle2);
                $db->Execute($sqlDetalle2);
            }
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

    // $rutas = isset($aForm['ruta']) && is_array($aForm['ruta']) ? array_map('intval', $aForm['ruta']) : [];
    // $proveedores = isset($aForm['proveedor']) && is_array($aForm['proveedor']) ? array_map('intval', $aForm['proveedor']) : [];

    $proveedores = isset($aForm['proveedor']) && is_array($aForm['proveedor']) 
    ? array_filter(array_map('intval', $aForm['proveedor']), function($v) { return $v > 0; })
    : [];

    $rutas = isset($aForm['ruta']) && is_array($aForm['ruta'])
    ? array_filter(array_map('intval', $aForm['ruta']), function($v) { return $v > 0; })
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

        return $result ?? ["mensaje" => "No se encontraron resultados."];

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

    $sqlDetalle = "SELECT dp.id, dp.idproducto, dp.cantidad, dp.observacionproducto, dp.estado, dp.preciosugerido, pr.nombre, pr.precioventa, dp.faltante FROM detallepedidosfacturas dp ".
    "LEFT JOIN productos pr ON dp.idproducto = pr.id ".
    "WHERE dp.idpedido =" . $idPedido;
    $detallepedido = $db->GetArray($sqlDetalle);

    error_log("SQL Pedido: " . $sqlPedido);

    return $pedido ? ["pedido" => $pedido, "detalle" => $detallepedido] : false;
}