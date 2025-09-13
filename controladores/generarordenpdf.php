<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database.php';

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
$incluirObservacion = $datos["incluirObservacion"] ?? false;
$incluirRuta = $datos["incluirRuta"] ?? false;
$incluirTotal = $datos["incluirTotal"] ?? false;

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
$html .= '<th>#</th>';
if ($incluirProducto) $html .= '<th>Producto</th>';
if ($incluirCantidad) $html .= '<th>Cantidad</th>';
if ($incluirCosto) $html .= '<th>Costo</th>';
if ($incluirProveedor) $html .= '<th>Proveedor</th>';
if ($incluirObservacion) $html .= '<th>Observación</th>';
if ($incluirRuta) $html .= '<th>Ruta</th>';

$html .= '</tr></thead><tbody>';
$totalOrden = 0;
$contador = 1;

foreach ($ordenes as $orden) {
    $html .= "<tr>";
    $html .= "<td>{$contador}</td>";
    if ($incluirProducto) $html .= "<td>{$orden['producto']}</td>";
    if ($incluirCantidad) $html .= "<td>{$orden['cantidad']}</td>";
    if ($incluirCosto) $html .= "<td>" . (($orden['costo'] != 'null') ? $orden['costo'] : '') . "</td>";
    if ($incluirProveedor) $html .= "<td>" . (($orden['proveedor'] != 'null') ? $orden['proveedor'] : '') . "</td>";
    if ($incluirObservacion) $html .= "<td>" . (($orden['observacion'] != 'null') ? $orden['observacion'] : '') . "</td>";
    if ($incluirRuta) $html .= "<td>" . (($orden['ruta'] != 'null') ? $orden['ruta'] : '') . "</td>";
    $html .= "</tr>";
    $contador++;
    $cantidad = is_numeric($orden['cantidad']) ? (float)$orden['cantidad'] : 0;
    $costo = is_numeric($orden['costo']) ? (float)$orden['costo'] : 0;
    $totalOrden += $cantidad * $costo;
}

if ($incluirTotal) {
    $html .= '<tr><td colspan="' . (
        ($contador + $incluirProducto + $incluirCantidad + $incluirCosto + $incluirProveedor + $incluirObservacion + $incluirRuta)
    ) . '" style="text-align: right; font-weight: bold;">TOTAL ORDEN: $' . number_format($totalOrden, 0, ',', '.') . '</td></tr>';
}
$html .= '</tbody></table>';

$mpdf->WriteHTML($html);

$pdfDir = __DIR__ . '/../public/pdfs';
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0777, true);
}

$oidUnico = uniqid();
$pdfFileName = "ordendecompra_" . date("YmdHis") . "_" . $oidUnico . ".pdf";
$pdfPath = $pdfDir . '/' . $pdfFileName;

$mpdf->Output($pdfPath, "F");

// Construir URL pública
$baseUrl = $_ENV['BASE_URL'] ?? '';
$pdfUrl = rtrim($baseUrl, '/') . '/public/pdfs/' . $pdfFileName;

echo json_encode([
    "success" => true,
    "pdfUrl" => $pdfUrl,
]);
exit;
?>
