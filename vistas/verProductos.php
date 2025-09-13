<?php
$tituloPagina = "Productos";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="verProductos" class="bg-light">
    <div id="cabecera"></div>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12" style="width: 100% !important;">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Productos</h1>
                    <div class="table-responsive">
                        <table id="tablaProductos" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Precio Venta New</th>
                                    <th>Precio Venta</th>
                                    <th>Costo</th>
                                    <th>Utilidad</th>
                                    <th>25%</th>
                                    <th>15%</th>
                                    <th>10%</th>
                                    <th>Proveedor</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody id="productos">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <dialog id="dialogProducto" class="modalProducto">
        <h1 class="text-center mb-4">Editar producto</h1>
        <form id="formEditarProducto" method="dialog">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre"  <?php if (!can("VERSECCIONADMIN")) echo "readonly"; ?>>
            </div>
            <div class="mb-3">
                <label for="costo" class="form-label">Costo:</label>
                <input type="text" class="form-control" id="costo" name="costo">
            </div>
            <?php if (can("VERSECCIONADMIN")): ?>
                <div class="mb-3" id="divPorcentajes"></div>
                <div class="mb-3" id="divVentaManual">
                    <label for="precioventa" class="form-label">Precio venta manual:</label>
                    <input type="text" class="form-control" id="precioventa" name="precioventa">
                </div>
                <div class="mb-3" id="divMarkup">
                    <label for="porcentajeventa" class="form-label">Porcentaje de venta (decimal):</label>
                    <input type="text" class="form-control" id="porcentajeventa" name="porcentajeventa">
                </div>
                <div class="mb-3" id="divVentaNew">
                    <label for="precioventanew" class="form-label">Precio Venta Nuevo:</label>
                    <input type="text" class="form-control" id="precioventanew" name="precioventanew">
                </div>
                <div class="text-center mt-3">
                    <label for="idproveedor" class="form-label">Proveedor</label>
                    <select class="form-select" id="idproveedor" name="idproveedor"></select>
                </div>
            <?php endif; ?>
            <div class="text-center mt-3">
                <button class="btn btn-primary btn-lg w-100" type="button" id="btnGrabarProd">Guardar Cambios</button>
            </div>
            <div class="text-center mt-3">
                <button id="btnCloseDialog" type="button" class="btn btn-secondary btn-lg w-100">Cerrar</button>
            </div>
        </form>
    </dialog>

    <script type="module" src="../js/productos.js"></script>
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>