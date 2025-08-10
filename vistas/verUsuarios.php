<?php
$tituloPagina = "Usuarios";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="verUsuarios" class="bg-light">
    <div id="cabecera"></div>
 
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Usuarios</h1>
 
                    <div class="table-responsive">
                        <table id="tablaUsuarios" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usuarios">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="module" src="../js/usuarios.js"></script>

<?php require_once __DIR__ . '/../componentes/footer.php'; ?>