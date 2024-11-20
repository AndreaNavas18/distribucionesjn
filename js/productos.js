const SERVER = 'http://localhost/distribucionesjn/';

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "verProductos") {
        verProductos();
    } else if (vista === "index") {
        importarProductos();
    }
});

function verProductos() {
    fetch(SERVER + "controladores/productos.php", {
        method: "POST",
        body: JSON.stringify({ funcion: "obtenerproductos" })
    })
    .then(response => response.json())
    .then(data => {
        if (data && Array.isArray(data)) {
            const productos = document.getElementById("productos");
            if (productos) {
                for (const producto of data) {
                    productos.innerHTML += `
                    <tr>
                        <td>${producto.id}</td>
                        <td>${producto.nombre}</td>
                        <td>${producto.precioventa}</td>
                        <td>${producto.costo}</td>
                    </tr>
                    `;
                }
            }
        } else {
            console.error("Error:", data.error);
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
}

function importarProductos() {
    
    document.getElementById("uploadButton").addEventListener("click", function () {
        const fileInput = document.getElementById("excel_file");
        const formData = new FormData();
        
        if (fileInput.files.length === 0) {
            alert("Por favor, selecciona un archivo.");
            return;
        }
    
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