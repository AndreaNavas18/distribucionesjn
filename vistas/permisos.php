<?php
$tituloPagina = "Gestión de Permisos";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="verPermisos" class="bg-light">
    <div id="cabecera"></div>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Gestión de Permisos</h1>

                    <!-- Tabs para Roles o Usuarios -->
                    <ul class="nav nav-tabs" id="permisosTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="roles-tab" data-bs-toggle="tab" href="#roles" role="tab">Roles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="usuarios-tab" data-bs-toggle="tab" href="#usuarios" role="tab">Usuarios</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4">
                        <!-- Permisos por Rol -->
                        <div class="tab-pane fade show active" id="roles" role="tabpanel">
                            <div class="mb-3">
                                <label for="selectRol" class="form-label">Seleccionar Rol</label>
                                <select id="selectRol" class="form-select">
                                    <option value="">-- Seleccione un rol --</option>
                                </select>
                            </div>
                            <div id="permisosRol" class="mt-3 row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3"></div>
                        </div>

                        <!-- Permisos por Usuario -->
                        <div class="tab-pane fade" id="usuarios" role="tabpanel">
                            <div class="mb-3">
                                <label for="selectUsuario" class="form-label">Seleccionar Usuario</label>
                                <select id="selectUsuario" class="form-select">
                                    <option value="">-- Seleccione un usuario --</option>
                                </select>
                            </div>
                            <div id="permisosUsuario" class="mt-3 row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="../js/permisos.js"></script>

<?php require_once __DIR__ . '/../componentes/footer.php'; ?>
