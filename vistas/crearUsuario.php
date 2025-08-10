<?php
$tituloPagina = "Crear Usuario";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="crearUsuario"class="bg-light">
    <div id="cabecera"></div>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h1 id="tituloU" class="text-center mb-4">Crear Usuario</h1>
                    <form id="crearUsuarioForm" autocomplete="off">
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula</label>
                            <input type="text" id="cedula" name="cedula" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" id="nombres" name="nombres" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" id="usuario" name="usuario" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" id="clave" name="clave" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select name="rol" id="rol" class="form-select" required>
                                <option value="">Seleccionar rol</option>
                                <option value="1">Administrador</option>
                                <option value="2">Vendedor</option>
                                <option value="3">Empacador</option>
                            </select>
                        </div>

                        <div class="text-center">
                            <button id="btnGrabarUsuario" type="submit" class="btn btn-success w-100">
                                <i class="fa-solid fa-user-plus"></i> Crear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        
    <script type="module" src="../js/usuarios.js"></script>
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>