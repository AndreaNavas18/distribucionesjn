<?php
$tituloPagina = "Crear Productos";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="crearProducto" class="bg-light">
    <div id="cabecera"></div>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Crear Producto</h1>
                    <form id="formCrearProducto">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="costo" class="form-label">Costo:</label>
                            <input type="text" class="form-control" id="costo" name="costo">
                        </div>
                        
                        <div class="mb-3" id="divPorcentajes"></div>

                        <div class="mb-3">
                            <label for="precioventa" class="form-label">Precio de Venta:</label>
                            <input type="text" class="form-control" id="precioventa" name="precioventa">
                        </div>

                        <div class="mb-3">
                            <label for="idproveedor" class="form-label">Proveedor</label>
                            <select class="form-select" id="idproveedor" name="idproveedor">
                            </select>
                        </div>

                        <div class="text-center">
                            <button id="btnGrabarProducto" type="submit" class="btn btn-success w-100">
                                <i class="fa-solid fa-user-plus"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="../js/productos.js"></script>    
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>