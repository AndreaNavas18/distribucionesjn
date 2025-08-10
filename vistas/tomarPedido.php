<?php
$tituloPagina = "Tomar Pedido";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="tomarPedido" class="bg-light">
    <div id="cabecera"></div>
    <div class="container mt-4" id="contenidoPedido">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div id="divTarjetaP" class="card shadow p-4">
                    <!-- <h1 class="text-center mb-4"></h1> -->

                    <form id="formPedido">
                        <div class="mb-3">
                            <label for="cliente" class="form-label">
                                <h5>Cliente</h5>
                            </label>
                            <select class="form-select" name="cliente" id="slcClientes">
                                <option value="elegir">Elegir un cliente</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="producto" class="form-label">
                                <h5>Producto</h5>
                            </label>
                            <select class="form-select" name="producto" id="slcProductos">
                                <option></option>
                            </select>
                        </div>

                        <div id="preciosPosibles" class="mb-3" style="display: none;">
                        </div>

                        <div class="mb-3">
                            <label for="cantidad" class="form-label">
                                <h5>Cantidad</h5>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="cantidad" id="cantidad">
                                <button class="btn btn-outline-secondary" type="button" id="btnCantidad">DOCENA</button>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button class="btn btn-primary btn-lg w-100" type="button" id="btnAgregar">Agregar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-10 mt-4">
                <div class="card shadow p-4">
                    <div class="table-responsive">
                        <table id="tablaPedido" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Cantidad</th>
                                    <th id="codigoProd">Código</th>
                                    <th>Producto</th>
                                    <th>Valor Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Precio Sugerido</th>
                                    <th>Subtotal Sugerido</th>
                                    <th>Observacion</th>
                                    <th colspan="2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <label for="total" class="form-label">
                            <h5>Total</h5>
                        </label>
                        <input class="form-control form-control-lg text-center" type="text" name="total" id="totalPedido" disabled>
                    </div>

                    <div class="mt-3">
                        <label for="observacion" class="form-label">
                            <h5>Observaciones</h5>
                        </label>
                        <textarea class="form-control" name="observacion" id="observacion" rows="4"></textarea>
                    </div>

                    <div class="mt-4 text-center">
                        <button class="btn btn-success btn-lg w-100" id="btnGuardarPedido">Guardar Pedido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="../js/pedidos.js"></script>
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>