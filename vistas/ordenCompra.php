<?php
$tituloPagina = "Ordenes de compra";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="ordenCompra" class="bg-light">
    <div id="cabecera"></div>

    <div class="container mt-4">
        <h1 class="text-center mb-4">Órdenes de Compra</h1>

        <div class="card shadow p-4 mb-4">
            <h2 class="h4 mb-3">Filtrar Órdenes</h2>
            <form id="formFiltro" class="row g-3">
                <div class="col-md-3">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                </div>
                <div class="col-md-3">
                    <label for="fechaFin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin">
                </div>
                <div class="col-md-3">
                     <label class="form-label">Ruta</label>
                    <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ruta[]" value="1" id="ruta1">
                            <label class="form-check-label" for="ruta1">Ruta 1</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ruta[]" value="2" id="ruta2">
                            <label class="form-check-label" for="ruta2">Ruta 2</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ruta[]" value="3" id="ruta3">
                            <label class="form-check-label" for="ruta3">Ruta 3</label>
                        </div>
                    </div>
                    <small class="form-text text-muted">Selecciona una o más rutas</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Proveedor</label>
                    <div id="contenedorProveedores" class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                    </div>
                    <small class="form-text text-muted">Selecciona uno o más proveedores</small>
                </div>
                <div class="card shadow p-3 mb-4">
                    <h5 class="mb-2">Pedidos encontrados</h5>
                    <div id="listaPedidos" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                    </div>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="button" id="btnFiltrar" class="btn btn-primary">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>


        <div class="card shadow p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Órdenes de Compra</h2>
                <button type="button" id="btnGenerarPDF" class="btn btn-primary btn-sm ms-auto">📄 Generar PDF</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo</th>
                            <th>Proveedor</th>
                            <th id="thRuta" style="display: none;">Ruta</th>
                            <th>Observacion</th>
                        </tr>
                    </thead>
                    <tbody id="ordenesCompra">
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <p id="totalOrdenTexto" class="text-end fw-bold mt-2"></p>
            </div>
        </div>
    </div>

    <div id="modalColumnas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Seleccionar columnas para el PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Selecciona las columnas que deseas incluir en el PDF:</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkProducto" checked>
                        <label class="form-check-label" for="chkProducto">Producto</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkCantidad" checked>
                        <label class="form-check-label" for="chkCantidad">Cantidad</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkCosto">
                        <label class="form-check-label" for="chkCosto">Costo</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkProveedor" checked>
                        <label class="form-check-label" for="chkProveedor">Proveedor</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkRuta">
                        <label class="form-check-label" for="chkRuta">Ruta</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkObservacion">
                        <label class="form-check-label" for="chkObservacion">Observación</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chkTotalOrden">
                        <label class="form-check-label" for="chkTotalOrden">Total de la orden</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmarPDF" class="btn btn-primary">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="../js/pedidos.js"></script>    
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>
 