<?php require_once __DIR__ . '/../autenticacion/proteger.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../css/index.css">
</head>
<body id="home">
    <header class="d-flex justify-content-between align-items-center p-3">
        <a href="../autenticacion/logout.php" class="btn btn-outline-danger" style="position:fixed; top:10px; right:10px; z-index:1000;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </header>

    <section class="hero">
        <img src="../public/images/logo.png" alt="Logo" class="logoInicio">
        <div class="search-box position-relative">
            <input type="text" class="form-control" id="txtBusquedaProducto" placeholder="Buscar producto" autocomplete="off">
            <div id="resultadosBusqueda" class="list-group" style="position:absolute; top:100%; left:0; right:0; z-index:10; display:none;"></div>
        </div>
    </section>
    
    <section class="import-section" style="display: none;">
        <div class="import-card">
            <form id="importFormProducto" enctype="multipart/form-data" class="import-container">
                <label for="excel_file_producto" class="custom-file-upload" id="label_producto">
                    <i class="fa-solid fa-file-arrow-up"></i> Seleccionar Archivo
                </label>
                <input type="file" id="excel_file_producto" accept=".xlsx, .xls" hidden />
                <button type="button" id="uploadButtonProducto" class="btn btn-primary">
                    <i class="fa-solid fa-upload"></i> Importar Productos
                </button>
            </form>
        </div>
        <div class="import-card">
            <form id="importFormCliente" enctype="multipart/form-data" class="import-container">
                <label for="excel_file_cliente" class="custom-file-upload" id="label_cliente">
                    <i class="fa-solid fa-file-arrow-up"></i> Seleccionar Archivo
                </label>
                <input type="file" id="excel_file_cliente" accept=".xlsx, .xls" hidden />
                <button type="button" id="uploadButtonCliente" class="btn btn-primary">
                    <i class="fa-solid fa-upload"></i> Importar Clientes
                </button>
            </form>
        </div>
    </section>
    <?php if (can('VERSECCIONADMIN')): ?>
        <section class="buttons-grid">
            <a href="./tomarPedido.php" id="tomarPedido">Tomar Pedido</a>
            <a href="./historialPedidos.php">Historial Pedidos</a>
            <a href="./pedidosActivos.php">Pedidos Activos</a>
            <a href="./ordenCompra.php">Orden de Compra</a>
            <a href="./verProductos.php">Ver Productos</a>
            <a href="./verClientes.php">Ver Clientes</a>
            <a href="./verUsuarios.php">Ver Usuarios</a>
            <a href="./crearProducto.php">Crear Producto</a>
            <a href="./crearCliente.php">Crear Cliente</a>
            <a href="./crearUsuario.php">Crear Usuario</a>
        </section>
    <?php endif; ?>

    <?php if (can('VERSECCIONEMPACADOR')): ?>
        <section class="buttons-grid">
            <a href="./pedidosActivos.php">Pedidos Activos</a>
        </section>
    <?php endif; ?>

    <script type="module" src="../js/home.js"></script>
</body>
</html>