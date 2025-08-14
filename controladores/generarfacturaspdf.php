<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database.php';

use GuzzleHttp\Client;
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

    $mpdf->SetHTMLHeader($encabezadoHTML);
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($htmlFactura, \Mpdf\HTMLParserMode::HTML_BODY);

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
    $oidUnico = uniqid();
    $pdfFileName = "{$fechaHoy}_{$nombreCliente}_{$oidUnico}.pdf";

    // Detectar entorno (local o producción)
    $isProduction = ($_ENV['APP_ENV'] ?? 'local') === 'production';

    if ($isProduction) {
        // Guardar temporalmente
        $tempPath = sys_get_temp_dir() . '/' . $pdfFileName;
        $mpdf->Output($tempPath, "F");

        // Subir a Supabase
        $pdfUrl = subirPDFaSupabase($tempPath, $pdfFileName);
        if ($pdfUrl) {
            $pdfUrls[] = $pdfUrl;
        } else {
            $pdfUrls[] = "ERROR_SUBIENDO_PDF";
        }

        // Eliminar archivo temporal
        @unlink($tempPath);
    } else {
        // Guardar en carpeta local
        $pdfDir = __DIR__ . '/../pdfs';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }
        $pdfPath = $pdfDir . '/' . $pdfFileName;
        $mpdf->Output($pdfPath, "F");

        $pdfUrls[] = "/distribucionesjn/pdfs/" . $pdfFileName;
    }
}

echo json_encode([
    "success" => true,
    "pdfUrls" => $pdfUrls
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;

function subirPDFaSupabase($pdfPath, $pdfFileName) {
    $supabaseUrl = $_ENV['SUPABASE_URL'];
    $supabaseAnonKey = $_ENV['SUPABASE_ANON_KEY'];
    $bucketName = 'pdfs';

    error_log("📄 Iniciando subida de PDF a Supabase");
    error_log("Ruta PDF: $pdfPath");
    error_log("Nombre en Supabase: $pdfFileName");
    error_log("Supabase URL: $supabaseUrl");
    error_log("Bucket: $bucketName");

    if (!file_exists($pdfPath)) {
        error_log("❌ El archivo PDF no existe en la ruta especificada");
        return null;
    }

    $fileSize = filesize($pdfPath);
    error_log("Tamaño del PDF: {$fileSize} bytes");

    $client = new Client();

    try {
        $bodyContent = file_get_contents($pdfPath);
        if ($bodyContent === false) {
            error_log("❌ No se pudo leer el archivo PDF");
            return null;
        }

        error_log("Enviando solicitud POST a: {$supabaseUrl}/storage/v1/object/{$bucketName}/{$pdfFileName}");

        $response = $client->request('POST', "$supabaseUrl/storage/v1/object/$bucketName/$pdfFileName", [
            'headers' => [
                'apikey' => $supabaseAnonKey,
                'Authorization' => "Bearer $supabaseAnonKey",
                'Content-Type' => 'application/pdf',
                'x-upsert' => 'true'
            ],
            'body' => $bodyContent
        ]);

        $statusCode = $response->getStatusCode();
        error_log("Código de respuesta HTTP: $statusCode");

        $responseBody = (string) $response->getBody();
        error_log("Cuerpo de respuesta: $responseBody");

        if (in_array($response->getStatusCode(), [200, 201])) {
            $publicUrl = "$supabaseUrl/storage/v1/object/public/$bucketName/$pdfFileName";
            error_log("✅ Subida exitosa. URL pública: $publicUrl");
            return $publicUrl;
        }

        error_log("❌ Falló la subida. Código HTTP: $statusCode");

        return null;
    } catch (\Exception $e) {
        error_log("Error subiendo PDF a Supabase: " . $e->getMessage());
        return null;
    }
}

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
            WHERE dp.idpedido = ? AND (dp.cantidad - COALESCE(dp.faltante, 0)) > 0";
    return $db->GetAll($sql, [$idPedido]);
}
?>
