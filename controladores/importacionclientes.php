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

        $rowNumber = 1; // para rastrear el número de fila

        // Leer los datos de las filas (desde la fila 2)
        foreach ($sheet->getRowIterator(2) as $row) {
            $rowNumber++;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $clientData = [];
            foreach ($cellIterator as $cell) {
                $clientData[] = $cell->getFormattedValue();
            }

            // Asignar valores
            $nombre = $clientData[0];
            $cedula = $clientData[1];
            $razonsocial = $clientData[2];
            $ubicacion = $clientData[3];
            $direccion = $clientData[4];
            $telefono = $clientData[5];
            $telefono2 = $clientData[6];
            $ruta = $clientData[7];

            // Limpiar campos opcionales
            $razonsocialx = trim($razonsocial) === '' ? null : $razonsocial;
            $ubicacionx  = trim($ubicacion) === '' ? null : $ubicacion;
            $direccionx  = trim($direccion) === '' ? null : $direccion;
            $cedulax    = trim($cedula) === '' ? null : $cedula;
            $telefonox  = trim($telefono) === '' ? null : $telefono;
            $telefono2x = trim($telefono2) === '' ? null : $telefono2;
            $rutax      = trim($ruta) === '' ? null : $ruta;

            $params = [$nombre, $cedulax, $razonsocialx, $ubicacionx, $direccionx, $telefonox, $telefono2x, $rutax];
            $sql = "INSERT INTO clientes (nombre, cedula, razonsocial, ubicacion, direccion, telefono, telefono2, ruta)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            try {
                $result = $db->Execute($sql, $params);
                if (!$result) {
                    error_log("❌ Error al insertar cliente en fila $rowNumber (nombre: $nombre): " . $db->ErrorMsg());
                }
            } catch (Exception $e) {
                error_log("❌ Excepción en fila $rowNumber (nombre: $nombre): " . $e->getMessage());
            }
        }

        echo json_encode(["mensaje" => "Importación finalizada. Revisa logs para errores individuales."]);
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al leer el archivo: " . $e->getMessage()]);
    }
}