import { pet, protegerVista, formatearMoneda } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    protegerVista(() => {
        const vista = document.body.id;
        if (vista === "home") {
            inicial();
            importarProductos();
            importarClientes();
        }
    });
});

function inicial() {    
    const inputBusqueda = document.getElementById("txtBusquedaProducto");
    const resultadosDiv = document.getElementById("resultadosBusqueda");

    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", async function() {
            const query = inputBusqueda.value.trim();
            if (query.length < 2) {
                resultadosDiv.innerHTML = "";
                resultadosDiv.style.display = "none";
                return;
            }

            const data = await pet("controladores/productos.php", {
                funcion: "buscarproductos",
                query: query
            });

            if (data.productos && Array.isArray(data.productos) && data.productos.length > 0) {
                resultadosDiv.innerHTML = data.productos.map(prod => `
                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>${prod.nombre}</span>
                        <span class="badge bg-primary rounded-pill">${formatearMoneda(prod.precioventanew ?? prod.precioventa)}</span>
                    </a>
                `).join('');
                resultadosDiv.style.display = "block";
            } else {
                resultadosDiv.innerHTML = `<div class="list-group-item">No hay coincidencias</div>`;
                resultadosDiv.style.display = "block";
            }
        });

        // Ocultar resultados al perder foco
        inputBusqueda.addEventListener("blur", function() {
            setTimeout(() => { resultadosDiv.style.display = "none"; }, 200);
        });

        // Mostrar resultados si vuelve a enfocar y hay texto
        inputBusqueda.addEventListener("focus", function() {
            if (inputBusqueda.value.trim().length >= 2 && resultadosDiv.innerHTML) {
                resultadosDiv.style.display = "block";
            }
        });
    }
    
    document.getElementById("excel_file_producto").addEventListener("change", function() {
        let fileName = this.files[0] ? this.files[0].name : "Seleccionar Archivo";
        document.getElementById("label_producto").innerHTML = `<i class="fa-solid fa-file-arrow-up"></i> ${fileName}`;
    });
    
    document.getElementById("excel_file_cliente").addEventListener("change", function() {
        let fileName = this.files[0] ? this.files[0].name : "Seleccionar Archivo";
        document.getElementById("label_cliente").innerHTML = `<i class="fa-solid fa-file-arrow-up"></i> ${fileName}`;
    });
}

function importarProductos() {
    document.getElementById("uploadButtonProducto").addEventListener("click", function () {
        const fileInput = document.getElementById("excel_file_producto");
        
        if (fileInput.files.length === 0) {
            Swal.fire({
                title: "Info",
                text: "Por favor, selecciona un archivo.",
                icon: "info",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        const formData = new FormData();
        formData.append("excel_file", fileInput.files[0]);
        
        fetch(SERVER + "/controladores/importacionproductos.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire({
                    title: "Error!",
                    text: "Hubo un error al importar los productos." + data.error,
                    icon: "error",
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    title: "¡Éxito!",
                    text: "Productos importados exitosamente.",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
            Swal.fire({
                title: "Error!",
                text: "Ocurrió un error al subir el archivo.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        });
    });
}

function importarClientes() {
    document.getElementById("uploadButtonCliente").addEventListener("click", function () {
        const fileInput = document.getElementById("excel_file_cliente");
        
        if (fileInput.files.length === 0) {
            Swal.fire({
                title: "Info",
                text: "Por favor, selecciona un archivo.",
                icon: "info",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        const formData = new FormData();
        formData.append("excel_file", fileInput.files[0]);
        
        fetch(SERVER + "/controladores/importacionclientes.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire({
                    title: "Error!",
                    text: "Hubo un error al importar los clientes." + data.error,
                    icon: "error",
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    title: "¡Éxito!",
                    text: "Clientes importados exitosamente.",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
            Swal.fire({
                title: "Error!",
                text: "Ocurrió un error al subir el archivo.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        });
    });
}