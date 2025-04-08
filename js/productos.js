import { SERVER, pet, formatearMoneda, initDataTable  } from "./base.js";

let idProductoEditando = null;
let tablaProductosDT;
const dialog = document.getElementById("dialogProducto");
const btnGuardar = document.getElementById("btnGrabarProd");

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

    if (data.productos && Array.isArray(data.productos)) {
        const tbody = document.getElementById("productos");
        const productos = document.getElementById("productos");
        if (productos) {
            productos.innerHTML = data.productos.map(producto => {
                const costoProducto = parseFloat(producto.costo);
                return `
                    <tr data-id="${producto.id}">
                        <td>${producto.id}</td>
                        <td>${producto.nombre}</td>
                        <td>${formatearMoneda(producto.precioventa)}</td>
                        <td>${formatearMoneda(costoProducto)}</td>
                        <td>${formatearMoneda(producto.precioventa - costoProducto)}</td>
                        <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,25))}</td>
                        <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,15))}</td>
                        <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,10))}</td>
                        <td><button class="btn btn-primary btnEditarProducto" data-id="${producto.id}"><i class="fa-solid fa-pen-to-square"></i></button></td>
                    </tr>
                `;
            }).join("");
        }
        const tablaProductosDT = initDataTable("#tablaProductos");
        tablaProductosDT.on("init", function () {
            editarProducto();
            tablaProductosDT.on("draw", editarProducto);
        });
    } else {
        console.error("Error:", data.error);
    }
}

function editarProducto() {
    const botones = document.querySelectorAll(".btnEditarProducto");
    const dialog = document.querySelector("#dialogProducto");
    const cerrarDialog = document.querySelector("#btnCloseDialog");
    const btnGuardar = document.querySelector("#btnGrabarProd");

    botones.forEach(boton => {
        boton.addEventListener("click", async () => {
            const id = boton.getAttribute("data-id");
            idProductoEditando = id;
            dialog.showModal();
            const data = await pet("controladores/productos.php", { funcion: "verproducto", idproducto: id });
            if (data.error) {
                console.error("Error:", data.error);
            } else {
                const productoEscogido = data.producto;
                const campos = ["id","nombre", "precioventa", "costo"];
                campos.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.value = productoEscogido[campo];
                    }
                });
            }
        });
    });

    cerrarDialog.addEventListener("click", () => {
        dialog.close();
    });

    btnGuardar.addEventListener("click", async () => {
        const producto = {
            id: idProductoEditando,
            nombre: document.getElementById("nombre").value,
            precioventa: document.getElementById("precioventa").value,
            costo: document.getElementById("costo").value
        };
        const data = await pet("controladores/productos.php", { funcion: "editarproducto", producto: JSON.stringify(producto) });
        if (data.error) {
            console.error("Error:", data.error);
        } else {
            const fila = $(`#tablaProductos tbody tr[data-id="${producto.id}"]`);
            if (fila.length) {
                tablaProductosDT.row(fila).data([
                    producto.id,
                    producto.nombre,
                    formatearMoneda(producto.precioventa),
                    formatearMoneda(producto.costo),
                    formatearMoneda(producto.precioventa - producto.costo),
                    formatearMoneda(producto.costo + calcularPorcentaje(producto.costo, 25)),
                    formatearMoneda(producto.costo + calcularPorcentaje(producto.costo, 15)),
                    formatearMoneda(producto.costo + calcularPorcentaje(producto.costo, 10)),
                    `<button class="btn btn-primary btnEditarProducto" data-id="${producto.id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`
                ]).draw();
        
                editarProducto();
            }
            Swal.fire({
                title: "¡Éxito!",
                text: "Producto editado exitosamente.",
                icon: "success",
                timer: 2000,
                showConfirmButton: false
            });
            dialog.close();
        }
    });
}


function calcularPorcentaje(valor,porcentaje) {
    return (valor * porcentaje) / 100;
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
        
        fetch(SERVER + "/controladores/importacion.php", {
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