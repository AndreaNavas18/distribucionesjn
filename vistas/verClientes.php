<?php
$tituloPagina = "Clientes";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="verClientes" class="bg-light">
    <div id="cabecera"></div>
 
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Clientes</h1>
 
                    <div class="table-responsive">
                        <table id="tablaClientes" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Razón Social</th>
                                    <th>Ubicación</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Otro Teléfono</th>
                                    <th>Ruta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientes">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="module" src="../js/clientes.js"></script>

<?php require_once __DIR__ . '/../componentes/footer.php'; ?>