<?php
require_once '../init.php';

if (isset($_GET['funcion'])) {
    $funcion = $_GET['funcion'];

    if ($funcion === 'obtenerproductos') {
        obtenerProductos();
    } else {
        echo json_encode(["error" => "Función no válida"]);
    }
} else {
    echo json_encode(["error" => "No se especificó ninguna función"]);
}

function obtenerProductos() {
    global $db;

    try {
        $sql = "SELECT codigo, nombre, CASE WHEN costo > 0 AND precioventanew > 0 THEN precioventanew ELSE precioventa END AS precioventa, ".
        "costo FROM productos";
        $productos = $db->GetArray($sql);

        if (count($productos) > 0) {
            echo json_encode($productos);
        } else {
            echo json_encode(["mensaje" => "No se encontraron productos"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al obtener los productos: " . $e->getMessage()]);
    }
}