import { SERVER, pet, formatearMoneda, initDataTable  } from "./base.js";

let idProductoEditando = null;
let tablaProductosDT;
let formatearCOP = new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "COP",
    minimumFractionDigits: 0
});

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "verProductos") {
        verProductos();
    } else if (vista === "index") {
        inicial();
        importarProductos();
        importarClientes();
    } else if (vista === "crearProducto") {
        listarProveedores();
        crearProducto();
        changes();
    }
});

async function verProductos() {
    const data = await pet("controladores/productos.php", {funcion: "obtenerproductos"});

    if (data.productos && Array.isArray(data.productos)) {
        const tbody = document.getElementById("productos");
        tbody.innerHTML = data.productos.map(producto => {
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
        
        tablaProductosDT = initDataTable("#tablaProductos");
        changeProductos();
    } else {
        console.error("Error:", data.error);
    }
}

function changeProductos() {
    const dialog = document.getElementById("dialogProducto");
    const btnGuardar = document.getElementById("btnGrabarProd");
    const botones = document.querySelectorAll(".btnEditarProducto");
    const cerrarDialog = document.querySelector("#btnCloseDialog");
    const inputCosto = document.getElementById("costo");
    const inputVenta = document.getElementById("precioventa");
    const divPorcentajes = document.getElementById("divPorcentajes");

    [inputCosto, inputVenta].forEach(input => {
        input.addEventListener("input", () => formatearInput(input));
        input.addEventListener("blur", () => formatearInput(input));
    });

    document.querySelector("#tablaProductos tbody").addEventListener("click", (e) => {
        const btn = e.target.closest(".btnEditarProducto");
        if (!btn) return;
    
        const id = btn.getAttribute("data-id");
        idProductoEditando = id;
    
        const fila = btn.closest("tr");
        const celdas = fila.querySelectorAll("td");
    
        document.getElementById("nombre").value = celdas[1].textContent;
        document.getElementById("precioventa").value = parseFloat(celdas[2].textContent.replace(/[^0-9.]/g, ""));
        document.getElementById("costo").value = parseFloat(celdas[3].textContent.replace(/[^0-9.]/g, ""));
    
        dialog.showModal();
    });

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
                        if (campo === "costo") {
                            input.dispatchEvent(new Event("input"));
                        }
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
            precioventa: parseFloat(inputVenta.getAttribute("data-real")) || 0,
            costo: parseFloat(inputCosto.getAttribute("data-real")) || 0
        };
    
        const data = await pet("controladores/productos.php", {
            funcion: "editarproducto",
            producto: JSON.stringify(producto)
        });
    
        if (data.error) {
            console.error("Error:", data.error);
            return;
        }
    
        const fila = $(`#tablaProductos tbody tr[data-id="${producto.id}"]`);
        if (fila.length) {
            const costo = producto.costo;
            tablaProductosDT.row(fila).data([
                producto.id,
                producto.nombre,
                formatearMoneda(producto.precioventa),
                formatearMoneda(costo),
                formatearMoneda(producto.precioventa - costo),
                formatearMoneda(costo + calcularPorcentaje(costo, 25)),
                formatearMoneda(costo + calcularPorcentaje(costo, 15)),
                formatearMoneda(costo + calcularPorcentaje(costo, 10)),
                `<button class="btn btn-primary btnEditarProducto" data-id="${producto.id}">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>`
            ]).draw();
        }
    
        Swal.fire({
            title: "¡Éxito!",
            text: "Producto editado exitosamente.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false
        });
    
        dialog.close();
    });

    inputCosto.addEventListener("input", function () {
        const costo = parseFloat(inputCosto.value.replace(/[^0-9.]/g, '').replace(",", "."));

        if (isNaN(costo) || costo <= 0) {
            divPorcentajes.innerHTML = "";
            return;
        }

        const porcentajes = [10, 15, 25];
        let html = "<label class='form-label'>Precios sugeridos:</label><ul class='list-group mt-2'>";

        porcentajes.forEach(porcentaje => {
            const precio = costo * (1 + porcentaje / 100);
            html += `<li class='list-group-item'>${porcentaje}%: <strong>${formatearCOP.format(precio)}</strong></li>`;
        });

        html += "</ul>";
        divPorcentajes.innerHTML = html;
    });
}

function limpiarNumero(valor) {
    return parseFloat(valor.replace(/[^\d,]/g, '').replace(",", "."));
}

function formatearInput(input) {
        const valor = limpiarNumero(input.value);
        if (!isNaN(valor)) {
            input.setAttribute("data-real", valor);
            input.value = formatearCOP.format(valor);
        } else {
            input.removeAttribute("data-real");
            input.value = "";
        }
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
                        <span class="badge bg-primary rounded-pill">${formatearMoneda(prod.precioventa)}</span>
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

function crearProducto() {
    const form = document.getElementById("formCrearProducto");

    if (form) {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            const productoData = {};
            formData.forEach((value, key) => {
                productoData[key] = value.toUpperCase();
            });

            console.log("Producto Data: ", productoData);

            const respuesta = await pet("controladores/productos.php", {
                funcion: "crearproducto",
                dataProducto: productoData
            });

            if (!respuesta.error) {
                const divPorcentajes = document.getElementById("divPorcentajes");
                divPorcentajes.innerHTML = "";
                Swal.fire({
                    title: "¡Éxito!", 
                    text: "Los datos se han guardado correctamente.",
                    icon: "success",
                    timer: 3000,
                    showConfirmButton: false
                });
                form.reset();
            } else {
                Swal.fire({
                    title: "Error!", 
                    text: "Hubo un error al guardar los datos.",
                    icon: "warning",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }
}

async function listarProveedores() {
    console.log("listarProveedores");
    const selectProveedores = document.getElementById("idproveedor");
    if(selectProveedores) {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproveedores" });

        if (data.error) {
            console.error("Error:", data.error);
            return;
        } else {
            const proveedores = data.proveedores;
            selectProveedores.innerHTML = "<option value='elegir'>Elegir los proveedores</option>" + 
            proveedores.map(proveedor =>
                `<option value="${proveedor.id}">${proveedor.proveedor}</option>`
            ).join('');
        }
    }
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

function changes() {
    const inputCosto = document.getElementById("costo");
    const divPorcentajes = document.getElementById("divPorcentajes");

    if (!inputCosto || !divPorcentajes) return;

      const formatearCOP = new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0
    });

    inputCosto.addEventListener("input", function () {
        const costo = parseFloat(inputCosto.value.replace(/[^0-9.]/g, '').replace(",", "."));

        if (isNaN(costo) || costo <= 0) {
            divPorcentajes.innerHTML = "";
            return;
        }

        const porcentajes = [10, 15, 25];
        let html = "<label class='form-label'>Precios sugeridos:</label><ul class='list-group'>";

        porcentajes.forEach(porcentaje => {
            const precio = costo * (1 + porcentaje / 100);
             html += `<li class='list-group-item'>${porcentaje}%: <strong>${formatearCOP.format(precio)}</strong></li>`;
        });

        html += "</ul>";
        divPorcentajes.innerHTML = html;
    });
}
