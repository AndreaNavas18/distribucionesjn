<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database.php';
use Mpdf\Mpdf;

header("Content-Type: application/json");

$inputJSON = file_get_contents("php://input");
$datos = json_decode($inputJSON, true);

if (!$datos || !isset($datos["ids"])) {
    echo json_encode(["success" => false, "error" => "IdDs no recibidos"]);
    exit;
}

$ids = $datos["ids"];
$pdfUrls = [];

foreach ($ids as $idPedido) {
    $pedido = obtenerPedidoPorId($idPedido);
    $detalles = obtenerDetallesPedido($idPedido);
    if (!$pedido) continue;
    if (!$detalles || count($detalles) === 0) continue;

    $css = file_get_contents(__DIR__ . '/../css/facturapdf.css');

    ob_start();
    include __DIR__ . '/../plantillas/factura.php';
    $htmlFactura = ob_get_clean();

    $mpdf = new Mpdf([
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 20,
        'margin_header' => 10,
        'margin_footer' => 10
    ]);

    //Insertar CSS y HTML
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($htmlFactura, \Mpdf\HTMLParserMode::HTML_BODY);

    $pdfDir = __DIR__ . '/../pdfs';
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }
    $pdfFileName = "factura_{$pedido['id']}.pdf";
    $pdfPath = $pdfDir . '/' . $pdfFileName;

    try {
        $mpdf->Output($pdfPath, "F");
        $pdfUrls[] = "/distribucionesjn/pdfs/" . $pdfFileName;
    } catch (\Mpdf\MpdfException $e) {
        error_log("Error generando PDF para pedido $idPedido: " . $e->getMessage());
    }
}

echo json_encode([
    "success" => true,
    "pdfUrls" => $pdfUrls
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;

function obtenerPedidoPorId($idPedido) {
    global $db;
    $sql = "SELECT p.*, c.nombre AS cliente_nombre, c.razonsocial, c.cedula, c.ubicacion, c.direccion, c.telefono, c.telefono2, c.ruta 
            FROM pedidos p
            LEFT JOIN clientes c ON p.idcliente = c.id
            WHERE p.id = ?";
    return $db->GetRow($sql, [$idPedido]);
}

function obtenerDetallesPedido($idPedido) {
    global $db;
    $sql = "SELECT dp.*, pr.nombre AS producto_nombre, 
                   CASE WHEN dp.preciosugerido > 0 THEN dp.preciosugerido ELSE pr.precioventa END AS precio_factura,
                   (dp.cantidad - COALESCE(dp.faltante, 0)) AS cantidad_facturada
            FROM detallepedidosfacturas dp
            LEFT JOIN productos pr ON dp.idproducto = pr.id
            WHERE dp.idpedido = ?";
    return $db->GetAll($sql, [$idPedido]);
}
?>
