<?php
require __DIR__ . '/../vendor/autoload.php';
require_once 'database.php';
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
    // Obtener datos de la base de datos para este pedido
    $pedido = obtenerPedidoPorId($idPedido);
    if (!$pedido) continue; // Si no existe, saltar al siguiente

    //Cargar CSS
    $css = file_get_contents(__DIR__ . '/../css/facturapdf.css');

    //Cargar HTML de la plantilla y pasarlo a string
    ob_start();
    include __DIR__ . '/../plantillas/factura.php'; // en este archivo usarás $pedido
    $htmlFactura = ob_get_clean();

    // Inicializar mPDF
    $mpdf = new Mpdf([
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 35,
        'margin_bottom' => 20,
        'margin_header' => 10,
        'margin_footer' => 10
    ]);

    //Insertar CSS y HTML
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($htmlFactura, \Mpdf\HTMLParserMode::HTML_BODY);

    // 👉 Guardar PDF con nombre único
    $pdfDir = __DIR__ . '/../pdfs';
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }
    $pdfFileName = "factura_{$pedido['id']}.pdf";
    $pdfPath = $pdfDir . '/' . $pdfFileName;

    $mpdf->Output($pdfPath, "F");

    //Añadir URL al array de PDFs generados
    $pdfUrls[] = "/distribucionesjn/pdfs/" . $pdfFileName;
}

//Devolver las URLs al frontend
echo json_encode([
    "success" => true,
    "pdfUrls" => $pdfUrls
]);
exit;

function obtenerPedidoPorId($id) {
    $conexion = new mysqli("localhost", "usuario", "contraseña", "base_de_datos");
    $stmt = $conexion->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $pedido = $resultado->fetch_assoc();
    $stmt->close();
    $conexion->close();
    return $pedido;
}
?>
