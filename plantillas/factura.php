<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/facturapdf.css">
</head>
<body>
  <div class="factura-container">
    <!-- DATOS CLIENTE -->
    <div class="titulo-factura">
      REMISIÓN DE VENTA
    </div>
    <div class="datoscliente">
      <h3>DATOS DEL CLIENTE</h3>
      <table class="datoscliente-table">
        <tr>
          <td><strong>Nombre:</strong> <?php echo $pedido['cliente_nombre']; ?></td>
          <?php if (!empty($pedido['ubicacion'])): ?>
            <td><strong>Ubicación:</strong> <?php echo $pedido['ubicacion']; ?></td>
          <?php endif; ?>
        </tr>
        <tr>
          <?php if (!empty($pedido['cedula'])): ?>
            <td colspan="2"><strong>Cédula:</strong> <?php echo $pedido['cedula']; ?></td>
          <?php endif; ?>
        </tr>
        <tr>
          <?php if (!empty($pedido['direccion'])): ?>
            <td><strong>Dirección:</strong> <?php echo $pedido['direccion']; ?></td>
          <?php endif; ?>
          <?php if (!empty($pedido['telefono'])): ?>
            <td><strong>Teléfono:</strong> <?php echo $pedido['telefono']; ?></td>
          <?php endif; ?>
        </tr>
        <tr>
          <?php if (!empty($pedido['razonsocial'])): ?>
            <td colspan="2"><strong>Razón Social:</strong> <?php echo $pedido['razonsocial']; ?></td>
          <?php endif; ?>
        </tr>
      </table>
      
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <table class="tablee">
      <thead>
        <tr>
          <th>CANTIDAD</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN</th>
          <th>VALOR UNITARIO</th>
          <th>VALOR TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $subtotal = 0;
          foreach ($detalles as $item):
            $cantidad = (float) $item['cantidad_facturada'];
            $precioUnitario = (float) $item['precio_factura'];
            $totalProducto = $cantidad * $precioUnitario;
            $subtotal += $totalProducto;
        ?>
        <tr>
          <td style="text-align: center;"><?php echo number_format($cantidad, 0); ?></td>
          <td><?php echo str_pad($item['idproducto'], 8, '0', STR_PAD_LEFT); ?></td>
          <td><?php echo $item['producto_nombre']; ?></td>
          <td>$<?php echo number_format($precioUnitario, 0, ',', '.'); ?></td>
          <td>$<?php echo number_format($totalProducto, 0, ',', '.'); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- TABLA DE TOTALES -->
    <table class="totales tablee">
      <tr>
        <td class="label">Subtotal:</td>
        <td class="valor">$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
      </tr>
      <tr>
        <td class="label">Total:</td>
        <td class="valor">$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
      </tr>
    </table>
  </div>
</body>
</html>
