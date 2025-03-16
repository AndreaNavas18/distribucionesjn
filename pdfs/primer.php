<?php
require __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

// Crear una instancia de mPDF
$mpdf = new Mpdf();

// Agregar contenido al PDF
$mpdf->WriteHTML('<h1>¡Hola, mundo!</h1><p>Este es mi primer PDF con mPDF.</p>');

// Generar y mostrar el PDF en el navegador
$mpdf->Output('mi_primer_pdf.pdf', 'I'); 
