<?php
$tituloPagina = "Historial Pedidos";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="historialPedidos" class="bg-light">
    <div id="cabecera"></div>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Historial de Pedidos</h1>
                    <div class="row mb-3">
                        <select id="filtroPedido" class="form-select">
                            <option value="">Seleccione un filtro</option>
                            <option value="empacados">Pedidos empacados</option>
                            <option value="sinempacar">Pedidos sin empacar</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-primary" id="btnFiltroHp">Filtrar</button>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" id="btnImprimirSeleccionados" class="btn btn-success">Imprimir seleccionados</button>

                    </div>
                    <div class="table-responsive">
                        <table id="tablaHistorialP" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th></th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Observacion general</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="pedidos">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="../js/pedidos.js"></script>
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>