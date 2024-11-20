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
                $resultado = guardarPedido($data['productos'], $data['cliente'], $data['observacion']);
                $response['lo que llega'] = $resultado;
                if($resultado != false) {
                    $response["mensaje"] = "Pedido guardado con éxito";
                } else {
                    $response["error"] = "Error al guardar el pedido";
                }
            break;

            case 'obtenerpedidos':
                $pedidos = obtenerPedidos();
                if ($pedidos != false) {
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


function guardarPedido($productos, $cliente, $observacion) {
    global $db;
    $r = false;
    $total = 0;
   
    try {
        foreach ($productos as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];

            $sql = "SELECT precioventa FROM productos WHERE id = ?";
            $precioResult = $db->GetRow($sql, [$idProducto]);

            if (!$precioResult) {
                throw new Exception("Producto no encontrado: $idProducto");
            }

            $precio = $precioResult['precioventa'];
            $subtotal = $cantidad * $precio;
            $total += $subtotal;
        }

        $db->StartTrans();

        $sqlPedido = "INSERT INTO pedidos (fecha, total, idcliente, observacion) VALUES (CURRENT_DATE, ?, ?, ?)";
        $db->Execute($sqlPedido, [$total, $cliente, $observacion]);

        $pedidoFinal = $db->Insert_ID();
        if (!$pedidoFinal) {
            throw new Exception("No se pudo guardar el pedido.");
        }

        foreach ($productos as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];

            $sqlDetalle = "INSERT INTO detallepedidos (idpedido, idproducto, cantidad, estado) VALUES (?, ?, ?, ?)";
            $db->Execute($sqlDetalle, [$pedidoFinal, $idProducto, $cantidad, 'inicial']);
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

    if ($fechaini && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaini)) {
        error_log("Fecha de inicio no válida: $fechaini");
        return "Error: Fecha de inicio no es válida.";
    }

    if ($fechafin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechafin)) {
        error_log("Fecha de fin no válida: $fechafin");
        return "Error: Fecha de fin no es válida.";
    }

    $ruta = isset($aForm['ruta']) && $aForm['ruta'] !== "elegir" ? (int)$aForm['ruta'] : null;
    $proveedor = isset($aForm['proveedor']) && $aForm['proveedor'] !== "elegir" ? $aForm['proveedor'] : null;

    try {
        $sql = "SELECT pod.nombre, SUM(dep.cantidad) AS cantidad, pod.costo, pod.proveedor " .
                "FROM productos pod " .
                "INNER JOIN detallepedidos dep ON pod.id = dep.idproducto " .
                "INNER JOIN pedidos ped ON dep.idpedido = ped.id " .
                "INNER JOIN clientes cl ON ped.idcliente = cl.id " .
                "WHERE ped.fecha BETWEEN ? AND ?";

        $params = [$fechaini, $fechafin];

        if ($ruta !== null) {
            $sql .= " AND cl.ruta = ?";
            $params[] = $ruta;
        }

        if ($proveedor !== null) {
            $sql .= " AND pod.proveedor = ?";
            $params[] = $proveedor;
        }

        $sql .= " GROUP BY pod.nombre, pod.costo, pod.proveedor";

        $result = $db->GetArray($sql, $params);

        return $result ? json_encode($result) : json_encode(["mensaje" => "No se encontraron resultados."]);

    } catch (Exception $e) {
        error_log("Error en verOrdenCompra: " . $e->getMessage());
        return json_encode(["error" => "Error al obtener la orden de compra."]);
    }
}