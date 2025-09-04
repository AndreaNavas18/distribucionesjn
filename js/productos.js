import { SERVER, pet, formatearMoneda, initDataTable, protegerVista } from "./base.js";

let idProductoEditando = null;
let tablaProductosDT;
let formatearCOP = new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "COP",
    minimumFractionDigits: 0
});

document.addEventListener("DOMContentLoaded", function() {
     protegerVista(() => {
        const vista = document.body.id;
        if (vista === "verProductos") {
            verProductos();
        } else if (vista === "crearProducto") {
            listarProveedores();
            crearProducto();
            changes();
        }
     });
});

async function verProductos() {
    const tbody = document.getElementById("productos");
    tbody.innerHTML = `<tr><td colspan="10" class="text-center">Cargando productos...</td></tr>`;

    const data = await pet("controladores/productos.php", {funcion: "obtenerproductos"});

    if (data.productos && Array.isArray(data.productos)) {
        tbody.innerHTML = data.productos.map(producto => {
            const costoProducto = parseFloat(producto.costo);
            const precioBase = costoProducto > 0 
                ? parseFloat(producto.precioventanew) 
                : parseFloat(producto.precioventa);

            return `
                <tr data-id="${producto.id}">
                    <td>${producto.id}</td>
                    <td>${producto.nombre}</td>
                    <td>${formatearMoneda(precioBase)}</td>
                    <td>${formatearMoneda(costoProducto)}</td>
                    <td>${formatearMoneda(precioBase - costoProducto)}</td>
                    <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,25))}</td>
                    <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,15))}</td>
                    <td>${formatearMoneda(costoProducto + calcularPorcentaje(costoProducto,10))}</td>
                    <td>${producto.proveedor}</td>
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
    const cerrarDialog = document.querySelector("#btnCloseDialog");
    const inputCosto = document.getElementById("costo");
    const inputVentaNew = document.getElementById("precioventanew");
    const inputMarkup = document.getElementById("porcentajeventa");
    const inputVenta = document.getElementById("precioventa");
    const selectProveedor = document.getElementById("idproveedor");

    [inputCosto, inputVenta].forEach(input => {
        input.addEventListener("input", () => formatearInput(input));
        input.addEventListener("blur", () => formatearInput(input));
    });

    document.querySelector("#tablaProductos tbody").addEventListener("click", async (e) => {
        const btn = e.target.closest(".btnEditarProducto");
        if (!btn) return;

        const id = btn.dataset.id; 
        idProductoEditando = id;

        dialog.showModal();
        const data = await pet("controladores/productos.php", { funcion: "verproducto", idproducto: id });
        await obtenerProveedores();

        if (data.error) {
            console.error("Error:", data.error);
        } else {
            const productoEscogido = data.producto;
            const campos = ["id", "nombre", "precioventanew", "porcentajeventa", "costo", "idproveedor", "precioventa"];
            campos.forEach(campo => {
                const input = document.getElementById(campo);
                if (input) {
                    input.value = productoEscogido[campo];
                    if (campo === "costo") {
                        input.setAttribute("data-real", productoEscogido[campo]);
                    }
                }
            });

            if (selectProveedor) {
                selectProveedor.value = productoEscogido.idproveedor;
            }

            if (parseFloat(productoEscogido.costo) > 0) {
                // Tiene costo → usar estructura nueva
                const divVentaNew = document.getElementById("divVentaNew");
                const divMarkup = document.getElementById("divMarkup");
                const divVentaManual = document.getElementById("divVentaManual");
                divVentaManual.style.display = "none";
                divVentaNew.style.display = "flex";
                divMarkup.style.display = "flex";
                actualizarPorcentajes(parseFloat(productoEscogido.costo));
            } else {
                divVentaManual.style.display = "flex";
                divVentaNew.style.display = "none";
                divMarkup.style.display = "none";
            }
        }
    });

    cerrarDialog.addEventListener("click", () => {
        dialog.close();
    });
    
    btnGuardar.addEventListener("click", async () => {
        formatearInput(inputCosto);
        formatearInput(inputVenta);
        const formEditar = document.getElementById("formEditarProducto");
        const nombreProveedor = selectProveedor.options[selectProveedor.selectedIndex].text || "";
        const costo = parseFloat(inputCosto.dataset.real);

        const producto = {
            id: idProductoEditando,
            nombre: document.getElementById("nombre").value,
            costo: costo,
            idproveedor: selectProveedor ? selectProveedor.value : null
        };

        if (costo > 0) {
            producto.precioventanew = parseFloat(inputVentaNew.dataset.real);
            producto.porcentajeventa = parseFloat(inputMarkup.value);
        } else {
            producto.precioventa = parseFloat(inputVenta.dataset.real);
        }

        console.log("Producto a editar:", producto);
    
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
            const precioFinal = costo > 0 
                ? producto.precioventanew 
                : producto.precioventa;

            tablaProductosDT.row(fila).data([
                producto.id,
                producto.nombre,
                formatearMoneda(precioFinal),
                formatearMoneda(costo),
                formatearMoneda(precioFinal - costo),
                formatearMoneda(costo + calcularPorcentaje(costo, 25)),
                formatearMoneda(costo + calcularPorcentaje(costo, 15)),
                formatearMoneda(costo + calcularPorcentaje(costo, 10)),
                nombreProveedor,
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
        console.log("Formulario antes de formatear:", document.getElementById("formEditarProducto").innerHTML);

        formEditar.reset();
        document.getElementById("costo").removeAttribute("data-real");
        document.getElementById("precioventa").removeAttribute("data-real");
        console.log("Formulario después de formatear:", document.getElementById("formEditarProducto").innerHTML);
        dialog.close();
    });

    inputCosto.addEventListener("input", function () {
        const raw = inputCosto.value.replace(/[^\d]/g, "");
        const costo = parseFloat(raw);
        console.log("Valor crudo:", inputCosto.value, " → limpio:", raw, " → parseado:", costo);
        if (!isNaN(costo) && costo > 0) {
            actualizarPorcentajes(costo);
        }
    });
}

function actualizarPorcentajes(costo) {
    const div = document.getElementById("divPorcentajes");
    console.log("Actualizar porcentajes con costo:", costo);
    if (isNaN(costo) || costo <= 0) {
        div.innerHTML = "";
        return;
    }

    const porcentajes = [10, 15, 25];
    let html = "<label class='form-label'>Precios sugeridos:</label><ul class='list-group mt-2'>";

    porcentajes.forEach(porcentaje => {
        const precio = costo * (1 + porcentaje / 100);
        html += `<li class='list-group-item'>${porcentaje}%: <strong>${formatearCOP.format(precio)}</strong></li>`;
    });

    html += "</ul>";
    div.innerHTML = html;
}

async function obtenerProveedores() {
    const select = document.getElementById("idproveedor");

    if (select) {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproveedores" });

        if (data.error) {
            console.error("Error:", data.error);
            select.innerHTML = "<option class='text-danger'>Error al cargar proveedores</option>";
            return;
        }

        if (Array.isArray(data.proveedores) && data.proveedores.length > 0) {
            select.innerHTML = data.proveedores.map(proveedor => `
                <option value="${proveedor.id}">${proveedor.proveedor}</option>
            `).join('');
        }
    }

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
