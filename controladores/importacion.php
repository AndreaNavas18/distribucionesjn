<?php
require_once '../database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $inputFileName = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        $db->StartTrans();
        
        // Leer los datos de las filas
        foreach ($sheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $productData = [];
            foreach ($cellIterator as $cell) {
                $productData[] = $cell->getFormattedValue();
            }
            
            $nombre = $productData[0];
            $precio = $productData[1];
            $costo = $productData[2];
            $veintep = $productData[3];
            $quincep = $productData[4];
            $diezp = $productData[5];
            $proveedor = $productData[6];

            $sql = "INSERT INTO productos (nombre, precioventa, costo, p25, p15, p10, proveedor)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [$nombre, $precio, $costo, $veintep, $quincep, $diezp, $proveedor];

            // Ejecutar consulta
            $db->Execute($sql, $params);
        }

        $db->CompleteTrans();
        
        echo json_encode(["mensaje" => "Productos importados con éxito."]);
    } catch (Exception $e) {
        $db->FailTrans();
        $db->CompleteTrans();
        echo json_encode(["error" => "Error al leer el archivo: " . $e->getMessage()]);
    }
}