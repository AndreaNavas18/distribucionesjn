<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/facturapdf.css">
</head>
<body>
  <div class="factura-container">
    <div class="header">
      <table class="header-table">
        <tr>
          <td style="width: 30%;">
            <img class="imgLogo" src="../assets/images/logo.png" alt="Logo de la empresa">
          </td>
          <td style="width: 40%;" class="empresa-info">
            <h1>Distribuciones JN</h1>
            <h4>¡Todo lo que necesitas a tu alcance!</h4>
            <p>NIT 94942292</p>
            <p>3163466573</p>
            <p>jn_distri1@gmail.com</p>
            <p>CALI, VALLE DEL CAUCA</p>
          </td>
          <td style="width: 30%;" class="factura-numero">
            <h3>REMISIÓN DE VENTA N°11</h3>
            <p>Fecha: <?php echo date("d/m/Y"); ?></p>
            <p>Hora: <?php echo date("H:i:s A"); ?></p>
          </td>
        </tr>
      </table>
    </div>

    <!-- DATOS CLIENTE -->
    <div class="datoscliente">
      <h3>DATOS DEL CLIENTE</h3>
      <table class="datoscliente-table">
        <tr>
          <td><strong>Nombre:</strong> <?php echo $pedido['cliente_nombre']; ?></td>
          <td><strong>Razón Social:</strong> <?php echo $pedido['razonsocial']; ?></td>
        </tr>
        <tr>
          <td><strong>Cédula:</strong> <?php echo $pedido['cedula']; ?></td>
          <td><strong>Teléfono:</strong> <?php echo $pedido['telefono']; ?></td>
        </tr>
        <tr>
          <td><strong>Teléfono 2:</strong> <?php echo $pedido['telefono2']; ?></td>
          <td><strong>Ubicación:</strong> <?php echo $pedido['ubicacion']; ?></td>
        </tr>
        <tr>
          <td colspan="2"><strong>Dirección:</strong> <?php echo $pedido['direccion']; ?></td>
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
        <tr>
          <td>2</td>
          <td>12345</td>
          <td>Producto A</td>
          <td>$50.00</td>
          <td>$100.00</td>
        </tr>
        <tr>
          <td>1</td>
          <td>67890</td>
          <td>Producto B</td>
          <td>$75.00</td>
          <td>$75.00</td>
        </tr>
      </tbody>
    </table>

    <!-- TABLA DE TOTALES -->
    <table class="totales tablee">
      <tr>
        <td class="label">Subtotal:</td>
        <td class="valor">$175.00</td>
      </tr>
      <tr>
        <td class="label">Total:</td>
        <td class="valor">$175.00</td>
      </tr>
    </table>
  </div>
</body>
</html>
