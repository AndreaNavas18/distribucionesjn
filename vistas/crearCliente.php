<?php
$tituloPagina = "Crear Cliente";
require_once __DIR__ . '/../componentes/header.php';
?>
<body id="crearCliente" class="bg-light">
    <div id="cabecera"></div>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h1 class="text-center mb-4">Crear Cliente</h1>
                    <form id="formCliente">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="razonsocial" class="form-label">Razón Social:</label>
                            <input type="text" class="form-control" id="razonsocial" name="razonsocial">
                        </div>

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación:</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono:</label>
                            <input type="number" class="form-control" id="telefono" name="telefono">
                        </div>

                        <div class="mb-3">
                            <label for="telefono2" class="form-label">Otro Teléfono:</label>
                            <input type="number" class="form-control" id="telefono2" name="telefono2">
                        </div>

                        <div class="mb-3">
                            <label for="ruta" class="form-label">Ruta:</label>
                            <select class="form-select" id="ruta" name="ruta">
                                <option value="">Elegir una ruta</option>
                                <option value="1">Ruta 1</option>
                                <option value="2">Ruta 2</option>
                                <option value="3">Ruta 3</option>
                            </select>
                        </div>

                        <div class="text-center">
                            <button id="btnGrabarCliente" type="submit" class="btn btn-success w-100">
                                <i class="fa-solid fa-user-plus"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="../js/clientes.js"></script>
<?php require_once __DIR__ . '/../componentes/footer.php'; ?>