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
                $ordenCompra = verOrdenCompra();
                if ($ordenCompra != false) {
                    $response["orden"] = $ordenCompra;
                } else {
                    $response["error"] = "No se encontraron pedidos";
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
    global $database;
    $r = false;
    $total = 0;
    foreach ($productos as $producto) {
        $idProducto = $producto['id'];
        $cantidad = $producto['cantidad'];
        $precio = $database->select("productos", ["id", "precioventa"], ["id" => $idProducto]);
        $subtotal = $cantidad * $precio[0]['precioventa'];
        $total += $subtotal;
    }

    try {
        $database->pdo->beginTransaction();

        $database->insert("pedidos", [
            "fecha" => date("Y-m-d"),
            "total" => $total,
            "idcliente" => $cliente,
            "observacion" => $observacion
        ]);

        $pedidoFinal = $database->id();

        if (!$pedidoFinal) {
            $database->pdo->rollBack();
            return false;
        }
    
        foreach ($productos as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];

            $database->insert("detallepedidos", [
                "idpedido" => $pedidoFinal,
                "idproducto" => $idProducto,
                "cantidad" => $cantidad,
                "estado" => 'inicial'
            ]);

            $detalleFinal = $database->id();
            if (!$detalleFinal) {
                $database->pdo->rollBack();
                $r = false;
                echo json_encode(["error" => "Error al guardar el detalle del pedido " . $detalleFinal]);
                return false;
            }
        }
        $database->pdo->commit();
        $r = true;
    } catch (\Throwable $th) {
        $database->pdo->rollBack();
        return false;
    }

    return $r;
}

function obtenerPedidos(){
    global $database;
    $pedidos = $database->select("pedidos", "*", ["ORDER" => ["id" => "ASC"]]);

    if (count($pedidos) > 0) {
        foreach ($pedidos as $key => $pedido) {
            $cliente = $database->select("clientes", ['nombre'], ["id" => $pedido["idcliente"]]);
            $pedidos[$key]["cliente"] = $cliente[0]["nombre"];
        }
        return json_encode($pedidos);
    } else {
        return false;
    }
}

function verOrdenCompra() {
    global $database;
    $pedidos = $database->select("pedidos", "*");
    return 'hola';
    

}