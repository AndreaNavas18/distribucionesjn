<?php
require __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;
header("Content-Type: application/json");

$inputJSON = file_get_contents("php://input");
$datos = json_decode($inputJSON, true);

if (!$datos || !isset($datos["ordenes"])) {
    echo json_encode(["success" => false, "error" => "Datos no recibidos"]);
    exit;
}

$ordenes = $datos['ordenes'];
$incluirProducto = $datos["incluirProducto"] ?? false;
$incluirCantidad = $datos["incluirCantidad"] ?? false;
$incluirCosto = $datos["incluirCosto"] ?? false;
$incluirProveedor = $datos["incluirProveedor"] ?? false;
$incluirRuta = $datos["incluirRuta"] ?? false;
$incluirObservacion = $datos["incluirObservacion"] ?? false;

$mpdf = new Mpdf();
$mpdf->WriteHTML("<h1>Orden de Compra</h1>");

$html = '<table border="1" width="100%" style="border-collapse: collapse;">
    <thead><tr>';

if ($incluirProducto) $html .= '<th>Producto</th>';
if ($incluirCantidad) $html .= '<th>Cantidad</th>';
if ($incluirCosto) $html .= '<th>Costo</th>';
if ($incluirProveedor) $html .= '<th>Proveedor</th>';
if ($incluirRuta) $html .= '<th>Ruta</th>';
if ($incluirObservacion) $html .= '<th>Observación</th>';

$html .= '</tr></thead><tbody>';

foreach ($ordenes as $orden) {
    $html .= "<tr>";
    if ($incluirProducto) $html .= "<td>{$orden['producto']}</td>";
    if ($incluirCantidad) $html .= "<td>{$orden['cantidad']}</td>";
    if ($incluirCosto) $html .= "<td>{$orden['costo']}</td>";
    if ($incluirProveedor) $html .= "<td>{$orden['proveedor']}</td>";
    if ($incluirRuta) $html .= "<td>{$orden['ruta']}</td>";
    if ($incluirObservacion) $html .= "<td>{$orden['observacion']}</td>";
    $html .= "</tr>";
}

$html .= '</tbody></table>';

$mpdf->WriteHTML($html);
$pdfDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "pdfs";
$pdfFileName = $pdfDir . DIRECTORY_SEPARATOR . "ordendecompra_" . date("YmdHis") . ".pdf";
$mpdf->Output($pdfFileName, "F");

echo json_encode([
    "success" => true, 
    "pdfUrl" => "/distribucionesjn/pdfs/" . basename($pdfFileName)
]);
exit;
?>
