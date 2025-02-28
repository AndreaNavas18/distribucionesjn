import { SERVER, pet, formatearMoneda, initDataTable  } from "./base.js";


document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "verProductos") {
        verProductos();
    } else if (vista === "index") {
        inicial();
        importarProductos();
    }
});

async function verProductos() {
    const data = await pet("controladores/productos.php", {funcion: "obtenerproductos"});

    if (data && Array.isArray(data)) {
        const productos = document.getElementById("productos");
        if (productos) {
            productos.innerHTML = "";

            for (const producto of data) {
                const costoProducto = parseFloat(producto.costo);
                productos.innerHTML += `
                <tr>
                    <td>${producto.id}</td>
                    <td>${producto.nombre}</td>
                    <td>${formatearMoneda(producto.precioventa)}</td>
                    <td>${formatearMoneda(costoProducto)}</td>
                    <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,25))}</td>
                    <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,15))}</td>
                    <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,10))}</td>
                </tr>
                `;
            }

        }
    } else {
        console.error("Error:", data.error);
    }
    initDataTable("#tablaProductos");
}

function calcularPorcentaje(valor,porcentaje) {
    return (valor * porcentaje) / 100;
}

function importarProductos() {
    document.getElementById("uploadButtonProducto").addEventListener("click", function () {
        const fileInput = document.getElementById("excel_file_producto");
        
        if (fileInput.files.length === 0) {
            alert("Por favor, selecciona un archivo.");
            return;
        }
        
        const formData = new FormData();
        formData.append("excel_file", fileInput.files[0]);
        
        fetch(SERVER + "/controladores/importacion.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("Hubo un error al importar los productos: " + data.error);
            } else {
                alert("Productos importados exitosamente");
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
            alert("Ocurrió un error al subir el archivo.");
        });
    });
}

function inicial() {
    document.getElementById("excel_file_producto").addEventListener("change", function() {
        let fileName = this.files[0] ? this.files[0].name : "Seleccionar Archivo";
        document.getElementById("label_producto").innerHTML = `<i class="fa-solid fa-file-arrow-up"></i> ${fileName}`;
    });
    
    document.getElementById("excel_file_cliente").addEventListener("change", function() {
        let fileName = this.files[0] ? this.files[0].name : "Seleccionar Archivo";
        document.getElementById("label_cliente").innerHTML = `<i class="fa-solid fa-file-arrow-up"></i> ${fileName}`;
    });
}