<?php require_once __DIR__ . '/../autenticacion/proteger.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Productos</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <!-- jQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS y otros scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
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
    <script type="module">
        import { cargarCabecera } from "../js/base.js";
        cargarCabecera();
    </script>
    <script type="module" src="../js/productos.js"></script>    
</body>

</html>