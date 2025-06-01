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
        'margin_top' => 50,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_bottom' => 20
    ]);

    $encabezadoHTML = '
    <div class="header">
        <table class="header-table" style="width:100%;">
        <tr>
            <td style="width: 30%;">
            <img class="imgLogo" src="../assets/images/logo.png" alt="Logo de la empresa">
            </td>
            <td style="width: 40%; text-align: center;" class="empresa-info">
            <h1>Distribuciones JN</h1>
            <h4>¡Todo lo que necesitas a tu alcance!</h4>
            <p>NIT 94942292</p>
            <p>3163466573</p>
            <p>jn_distri1@gmail.com</p>
            <p>CALI, VALLE DEL CAUCA</p>
            </td>
            <td style="width: 30%; text-align: right;" class="factura-numero">
            <h3>REMISIÓN DE VENTA N°' . $pedido["id"] . '</h3>
            <p>Fecha: ' . date("d/m/Y") . '</p>
            <p>Hora: ' . date("H:i:s A") . '</p>
            </td>
        </tr>
        </table>
    </div>
    ';

    //Insertar CSS y HTML
    $mpdf->SetHTMLHeader($encabezadoHTML);
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($htmlFactura, \Mpdf\HTMLParserMode::HTML_BODY);
    $pdfDir = __DIR__ . '/../pdfs';
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    $dia = date('d');
    $mes = $meses[(int)date('m')];
    $anio = date('Y');
    $fechaHoy = "{$dia}{$mes}{$anio}";
    $nombreCliente = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pedido['cliente_nombre']);
    $pdfFileName = "{$fechaHoy}_{$nombreCliente}.pdf";
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
    error_log("SQL pedido por ID: " . $sql . " ID: " . $idPedido);
    return $db->GetRow($sql, [$idPedido]);
}

function obtenerDetallesPedido($idPedido) {
    global $db;
    $sql = "SELECT dp.*, pr.nombre AS producto_nombre, 
                   CASE WHEN dp.preciosugerido > 0 THEN dp.preciosugerido ELSE pr.precioventa END AS precio_factura,
                   (dp.cantidad - COALESCE(dp.faltante, 0)) AS cantidad_facturada
            FROM detallepedidosfacturas dp
            LEFT JOIN productos pr ON dp.idproducto = pr.id
            WHERE dp.idpedido = ? AND (dp.cantidad - COALESCE(dp.faltante, 0)) > 0";
    error_log("SQL Detalles: " . $sql);
    return $db->GetAll($sql, [$idPedido]);
}
?>
