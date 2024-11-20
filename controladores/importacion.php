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

            $database->insert('productos', [
                'nombre' => $nombre,
                'precioventa' => $precio,
                'costo' => $costo,
                'p25' => $veintep,
                'p15' => $quincep,
                'p10' => $diezp,
                'proveedor' => $proveedor
            ]);
        }
        
        echo json_encode(["mensaje" => "Productos importados con éxito."]);
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al leer el archivo: " . $e->getMessage()]);
    }
}