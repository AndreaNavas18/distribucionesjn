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

$mpdf = new Mpdf([
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 35,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10
]);

$css = "
    body { font-family: Arial, sans-serif; font-size: 12px;  }
    h1 { text-align: center; color: #00486c; }
    .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
    .tabla th, .tabla td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    .tabla th { background-color: #00486c; color: white; }
    .tabla tr:nth-child(even) { background-color: #f2f2f2; }
    .tabla tr:hover { background-color: #ddd; }
";
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

$logoPath = __DIR__ . '/../assets/images/logonombre.png';
$logoBase64 = base64_encode(file_get_contents($logoPath));
$logoHtml = '<div style="text-align: center;"><img src="data:image/png;base64,' . $logoBase64 . '" width="100px"></div>';

$mpdf->SetHTMLHeader($logoHtml);


$mpdf->WriteHTML("<h1>Orden de Compra</h1>");

$html = '<table class="tabla">
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
    if ($incluirCosto) $html .= "<td>" . (($orden['costo'] != 'null') ? $orden['costo'] : '') . "</td>";
    if ($incluirProveedor) $html .= "<td>" . (($orden['proveedor'] != 'null') ? $orden['proveedor'] : '') . "</td>";
    if ($incluirRuta) $html .= "<td>" . (($orden['ruta'] != 'null') ? $orden['ruta'] : '') . "</td>";
    if ($incluirObservacion) $html .= "<td>" . (($orden['observacion'] != 'null') ? $orden['observacion'] : '') . "</td>";
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
