<?php
// Supongamos que recibes $factura, $cliente y $detalleFactura como arrays asociativos desde el controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?= $factura['numero'] ?></title>
    <link rel="stylesheet" href="../css/facturapdf.css">
</head>
<body>

    <!-- ENCABEZADO EMPRESA -->
    <div class="encabezado">
        <div class="logo">
            <img src="../assets/images/logonombre.png" alt="Logo Empresa" width="100">
        </div>
        <div class="datos-empresa">
            <h2><?= $empresa['nombre'] ?></h2>
            <p>NIT: <?= $empresa['nit'] ?></p>
            <p><?= $empresa['direccion'] ?></p>
            <p>Tel: <?= $empresa['telefono'] ?></p>
            <p><?= $empresa['otrosDatos'] ?></p>
        </div>
        <div class="qr">
            <!-- Aquí podrías generar un QR dinámico si lo necesitas -->
            <img src="../assets/images/qrplaceholder.png" alt="QR" width="80">
        </div>
    </div>

    <!-- DATOS CLIENTE -->
    <div class="datos-cliente">
        <table>
            <tr><th>Señores:</th><td><?= $cliente['razonsocial'] ?></td></tr>
            <tr><th>NIT/Cédula:</th><td><?= $cliente['cedula'] ?></td></tr>
            <tr><th>Dirección:</th><td><?= $cliente['direccion'] ?></td></tr>
            <tr><th>Ciudad:</th><td><?= $cliente['ubicacion'] ?></td></tr>
            <tr><th>Teléfono:</th><td><?= $cliente['telefono'] ?></td></tr>
        </table>
    </div>

    <!-- DATOS FACTURA -->
    <div class="datos-factura">
        <table>
            <tr><th>Factura Electrónica de Venta</th><td><?= $factura['numero'] ?></td></tr>
            <tr><th>Fecha:</th><td><?= $factura['fecha'] ?></td></tr>
            <tr><th>Vencimiento:</th><td><?= $factura['fechavencimiento'] ?></td></tr>
            <tr><th>Forma de pago:</th><td><?= $factura['formapago'] ?></td></tr>
            <tr><th>Medio de pago:</th><td><?= $factura['mediopago'] ?></td></tr>
        </table>
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <table class="tabla-productos">
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>U. Medida</th>
                <th>Cant.</th>
                <th>Vr. Unit</th>
                <th>Vr. IVA</th>
                <th>Dcto</th>
                <th>Vr. Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalleFactura as $i => $item): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $item['codigo'] ?></td>
                <td><?= $item['descripcion'] ?></td>
                <td><?= $item['umedida'] ?? 'Und' ?></td>
                <td><?= $item['cantidad'] ?></td>
                <td><?= number_format($item['precio_unitario'], 2) ?></td>
                <td><?= number_format($item['valor_iva'], 2) ?></td>
                <td><?= number_format($item['descuento'] ?? 0, 2) ?></td>
                <td><?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- RESÚMENES -->
    <div class="resumenes">
        <div class="impuestos">
            <table>
                <tr><th>Tipo</th><th>Impuestos</th></tr>
                <tr><td>IVA 19%</td><td><?= number_format($factura['iva19'], 2) ?></td></tr>
                <tr><td>IVA 10%</td><td><?= number_format($factura['iva10'], 2) ?></td></tr>
                <tr><td>IVA 5%</td><td><?= number_format($factura['iva5'], 2) ?></td></tr>
            </table>
        </div>
        <div class="totales">
            <table>
                <tr><th>Total Ítems:</th><td><?= count($detalleFactura) ?></td></tr>
                <tr><th>Valor Bruto:</th><td><?= number_format($factura['valorbruto'], 2) ?></td></tr>
                <tr><th>Descuento:</th><td><?= number_format($factura['descuento'], 2) ?></td></tr>
                <tr><th>Subtotal:</th><td><?= number_format($factura['subtotal'], 2) ?></td></tr>
                <tr><th>I.V.A:</th><td><?= number_format($factura['iva_total'], 2) ?></td></tr>
                <tr><th>Neto a Pagar:</th><td><?= number_format($factura['netoapagar'], 2) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- OBSERVACIONES Y VALOR EN LETRAS -->
    <div class="observaciones">
        <p><strong>Valor en letras:</strong> <?= $factura['valor_letras'] ?></p>
        <p><strong>Observaciones:</strong> <?= $factura['observaciones'] ?></p>
    </div>

    <!-- FIRMA -->
    <div class="firma">
        <p>_________________________________</p>
        <p>Firma, sello y fecha de recibo</p>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p><?= $empresa['infoLegal'] ?></p>
    </div>

</body>
</html>
