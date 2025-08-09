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
            $proveedor = $productData[3];

            $sql = "INSERT INTO productos (nombre, precioventa, costo, idproveedor)
                    VALUES (?, ?, ?, ?)";
            $params = [$nombre, $precio, $costo, $proveedor];

            // Ejecutar consulta
            $result = $db->Execute($sql, $params);

            if (!$result) {
                error_log("Error al insertar producto: " . $db->ErrorMsg());
            }
        }

        $db->CompleteTrans();
        
        echo json_encode(["mensaje" => "Productos importados con éxito."]);
    } catch (Exception $e) {
        $db->FailTrans();
        $db->CompleteTrans();
        echo json_encode(["error" => "Error al leer el archivo: " . $e->getMessage()]);
    }
}