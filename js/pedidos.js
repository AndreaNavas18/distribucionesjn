import { SERVER, pet, initSelect2, formatearMoneda, initDataTable  } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "tomarPedido") {
        const urlParams = new URLSearchParams(window.location.search);
        const idPedido = urlParams.get('id');
        inicialTomaPedidos(idPedido);
        listarProductos();
        obtenerClientes();
        agregarProducto();
        if (idPedido) {
            cargarDatosPedido(idPedido);
        }
        guardarPedido(idPedido);
    } else if (vista === "historialPedidos") {
        historialPedidos();
    } else if (vista === "ordenCompra") {
        initCalendars();
        listarProveedores();
        verOrdenCompra();
    }
});

function listarProductos() {
    const selectProductos = document.getElementById("slcProductos");
    if(selectProductos) {
        initSelect2("#slcProductos", {
            placeholder: 'Escribe para buscar un producto...',
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            ajax: {
                url: SERVER + 'controladores/productos.php',
                type: 'POST',
                dataType: 'json',
                delay: 250,
                headers: {
                    'Content-Type': 'application/json'
                },
                data: params => JSON.stringify({
                    funcion: 'buscarproductos', 
                    query: params.term || ''
                }),
                processResults: data => {
                    if (data.error) {
                        console.error(data.error);
                        return { results: [] };
                    }
                    return {
                        results: data.productos.map(producto => ({
                            id: producto.id,
                            text: producto.nombre
                        }))
                    };
                },
                cache: true
            }
        });
    }
}

async function cargarDatosPedido(idPedido) {
    const data = await pet("controladores/pedidos.php", { funcion: "verpedido", id: idPedido });
    const totalPedidoInput = document.getElementById("totalPedido");
    const selectClientes = document.getElementById("slcClientes");

    if (data.error) {
        console.error("Error:", data.error);
        return;
    }
    console.log(data);

    selectClientes.value = data.pedido.idcliente;
    document.getElementById("totalPedido").value = formatearMoneda(data.pedido.total);
    document.getElementById("observacion").value = data.pedido.observacion;

    const tbody = document.querySelector("#tablaPedido tbody");
    tbody.innerHTML = "";

    data.detalle.forEach((producto) => {
        let fila = `
        <tr>
            <td>${producto.cantidad}</td>
            <td>${producto.nombre}</td>
            <td>${formatearMoneda(producto.precioventa)}</td>
            <td>${formatearMoneda(producto.cantidad * producto.precioventa)}</td>
            <td>${formatearMoneda(producto.preciosugerido)}</td>
            <td>${formatearMoneda(producto.cantidad * producto.preciosugerido)}</td>
            <td><input type='text-area' class='form-control' id='observacionproducto' name='observacionproducto' value='${producto.observacionproducto || producto.observacionproducto != null ? producto.observacionproducto : ""}'></td>
            <td><button class="btn btn-danger btnEliminar">Eliminar</button></td>
        </tr>
        `;
        tbody.innerHTML += fila;
    });

    actualizarTotal(totalPedidoInput);
}

function editarPedido() {
    console.log("Editar pedido");
    const btnEditarPedido = document.querySelectorAll("#btnEditarPedido");
    btnEditarPedido.forEach(boton => {
        boton.addEventListener("click", function () {
            const idPedido = this.dataset.id;
            window.location.href = `tomarPedido.html?id=${idPedido}`;
        });
    });
}

async function obtenerClientes() {
    const data = await pet("controladores/clientes.php", { funcion: "obtenerclientes" });

    if (data.error) {
        console.error("Error:", data.error);
        return;
    }
    const clientesArray = JSON.parse(data.clientes);
    const clientes = document.getElementById("slcClientes");
    if (clientes) {
        clientes.innerHTML = "<option value='elegir'>Seleccione un cliente</option>" + clientesArray.map(cliente =>
            `<option value="${cliente.id}">${cliente.nombre}</option>`
        ).join('');
    } else {
        console.error("El elemento 'slcClientes' no existe en el DOM");
    }
}

function inicialTomaPedidos(idPedido = null) {
    const divPrecios = document.getElementById("preciosPosibles");
    const selectProductos = document.getElementById("slcProductos");
    const divTarjeta = document.getElementById("divTarjetaP");
    const title = idPedido ? "Editar Pedido" : "Tomar Pedido";

    const h1 = document.createElement("h1");
    h1.classList.add("text-center");
    h1.classList.add("mb-4");
    h1.textContent = title;
    divTarjeta.prepend(h1);

    $(selectProductos).on("select2:select", async function () {
        const productoSeleccionado = this.value;
        const {costo, precioventa} = await obtenerCosto(productoSeleccionado);

        if (!costo) {
            divPrecios.innerHTML = "";
            divPrecios.style.display = "none";
            return;
        }

        const precios = {
            base: costo,
            diez: costo + (costo * 10 / 100),
            quince: costo + (costo * 15 / 100),
            veinticinco: costo + (costo * 25 / 100),
            precioventa: precioventa
        };

        divPrecios.innerHTML = `
        <div class="row g-2">
            <div class="col-auto">
                <button type="button" class="btn btn-outline-primary precio-btn" data-precio="${precios.precioventa}">Venta: ${formatearMoneda(precios.precioventa)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-primary precio-btn" data-precio="${precios.base}">Costo: ${formatearMoneda(precios.base)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-success precio-btn" data-precio="${precios.diez}">+10%: ${formatearMoneda(precios.diez)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-warning precio-btn" data-precio="${precios.quince}">+15%: ${formatearMoneda(precios.quince)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger precio-btn" data-precio="${precios.veinticinco}">+25%: ${formatearMoneda(precios.veinticinco)}</button>
            </div>
            <div class="col-auto">
                <input type="number" id="precioPersonalizado" class="form-control w-auto" placeholder="Otro precio">
            </div>
        </div>
        `;

        divPrecios.style.display = "block";

        document.querySelectorAll(".precio-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                document.querySelectorAll(".precio-btn").forEach(b => b.classList.remove("active"));
                this.classList.add("active");
                document.getElementById("precioPersonalizado").value = this.dataset.precio;
            });
        });
    });
}

async function obtenerCosto(productoId) {
    const productos = await pet("controladores/productos.php", { funcion: "obtenerproductos" });
    const producto = productos.find(p => p.id == productoId);
    return producto ? { costo: parseFloat(producto.costo), precioventa: parseFloat(producto.precioventa)} : {costo: 0, precioventa: 0};
}

async function agregarProducto() {
    const btnAgregar = document.getElementById("btnAgregar");
    const btnCantidad = document.getElementById("btnCantidad");
    const selectProductos = document.getElementById("slcProductos");
    const inputCantidad = document.getElementById("cantidad");
    const tablaPedidoBody = document.querySelector("#tablaPedido tbody");
    const tdIdProducto = document.getElementById("codigoProd");
    const totalPedidoInput = document.getElementById("totalPedido");
    const divPrecios = document.getElementById("preciosPosibles");
    tdIdProducto.style.display = "none";

    if (!btnAgregar || !btnCantidad || !selectProductos || !inputCantidad || !tablaPedidoBody || !totalPedidoInput) {
        console.error("Uno o más elementos no existen en el DOM");
        return;
    }

    let infoProductos = [];

    try {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproductos" });
        if (data && Array.isArray(data)) {
            infoProductos = data;
        }
    } catch (error) {
        console.error("Error obteniendo productos:", error);
    }

    btnAgregar.addEventListener("click", function () {
        const productoSeleccionado = selectProductos.options[selectProductos.selectedIndex];
        const cantidad = inputCantidad.value;

        if (!productoSeleccionado.value || cantidad <= 0) {
            Swal.fire({
                title: "!!!", 
                text: "Por favor, seleccione un producto y una cantidad válida.",
                icon: "warning",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        const idProducto = productoSeleccionado.value;
        const nombreProducto = productoSeleccionado.text;
        const producto = infoProductos.find(prod => prod.id == idProducto);

        if (!producto) {
            console.error("Producto no encontrado en la lista.");
            return;
        }
        const precioProducto = parseFloat(producto.precioventa);
        const precioSeleccionado = document.querySelector(".precio-btn.active");
        const precioPersonalizadoElement = document.getElementById("precioPersonalizado");
        const precioManual = precioPersonalizadoElement ? precioPersonalizadoElement.value : null;
        
        let precioFinal;
        if (precioManual) {
            precioFinal = parseFloat(precioManual);
        } else if (precioSeleccionado) {
            precioFinal = parseFloat(precioSeleccionado.dataset.precio);
        } else {
            precioFinal = "";
        }
        
        const subTotal = precioProducto * cantidad;
        const subSugerido = precioFinal * cantidad;

        const fila = document.createElement("tr");
        fila.setAttribute("data-id", idProducto);
        fila.innerHTML = `
            <td>${cantidad}</td>
            <td>${nombreProducto}</td>
            <td>${formatearMoneda(precioProducto)}</td>
            <td>${formatearMoneda(subTotal)}</td>
            <td>${formatearMoneda(precioFinal)}</td>
            <td>${formatearMoneda(subSugerido)}</td>
            <td><input type='text-area' class='form-control' id='observacionproducto' name='observacionproducto'></td>
            <td><button class="btn btn-danger btnEliminar">Eliminar</button></td>
        `;

        fila.querySelector(".btnEliminar").addEventListener("click", function () {
            fila.remove();
            actualizarTotal(totalPedidoInput);
        });

        tablaPedidoBody.appendChild(fila);
        inputCantidad.value = "";
        $(selectProductos).val(null).trigger("change");
        divPrecios.innerHTML = "";
        divPrecios.style.display = "none";

        actualizarTotal(totalPedidoInput);
    });

    btnCantidad.addEventListener("click", function () {
        inputCantidad.value = 12;
    });

}

function actualizarTotal(totalPedidoInput) {
    let total = 0;
    document.querySelectorAll("#tablaPedido tbody tr").forEach(fila => {
        const textoSubtotal = fila.children[3].textContent;
        const subTotal = parseFloat(textoSubtotal.replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.'));
        total += subTotal;
    });
    totalPedidoInput.value = formatearMoneda(total);
}

function guardarPedido(idPedido = null) {
    document.getElementById("btnGuardarPedido").addEventListener("click", async function () {
        const tablaPedidoBody = document.querySelector("#tablaPedido tbody");
        if (!tablaPedidoBody) {
            Swal.fire({
                title: "Error!", 
                text: "No se encontró la tabla de pedidos.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
    
        const filas = tablaPedidoBody.querySelectorAll("tr");
        if (filas.length === 0) {
            Swal.fire({
                title: "Info", 
                text: "No hay productos en la tabla.",
                icon: "info",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        const productos = Array.from(filas).map(fila => ({
            id: parseInt(fila.getAttribute("data-id"), 10),
            cantidad: parseInt(fila.cells[0]?.textContent.trim(), 10),
            preciofinal: parseFloat(fila.cells[2]?.textContent.trim().replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.')),
            observacionproducto: document.getElementById("observacionproducto").value,
            preciosugerido: parseFloat(fila.cells[4]?.textContent.trim().replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.'))
        })).filter(p => p.id && p.cantidad);
    
        if (productos.length === 0) {
            Swal.fire({
                title: "Error!",
                text: "No se capturaron productos correctamente.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        const totall = parseFloat(document.getElementById("totalPedido").value.replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.'));
    
        const data = await pet("controladores/pedidos.php", {
            funcion: "guardarpedido",
            datosForm: {
                idPedido: idPedido,
                total: totall,
                cliente: document.getElementById("slcClientes").value,
                observacion: document.getElementById("observacion").value,
                productos: productos
            }
        });

        if (data.mensaje) {
            Swal.fire({
                title: "¡Éxito!",
                text: data.mensaje,
                icon: "success",
                timer: 2000,
                showConfirmButton: false
            });
            tablaPedidoBody.innerHTML = "";
        } else {
            Swal.fire({
                title: "Error!",
                text: "Hubo un error al guardar el pedido.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        }

        document.getElementById("totalPedido").value = "";
        document.getElementById("slcClientes").value = "elegir";
        document.getElementById("preciosPosibles").innerHTML = "";
        document.getElementById("preciosPosibles").style.display = "none";
        document.getElementById("observacion").value = "";

    });
}

async function historialPedidos() {
    const data = await pet("controladores/pedidos.php", { funcion: "obtenerpedidos" });

    if (data.pedidos) {
        const pedidosArray = JSON.parse(data.pedidos);
        const pedidos = document.getElementById("pedidos");

        if (pedidos) {
            pedidos.innerHTML = pedidosArray.map(pedido => `
                <tr>
                    <td>${pedido.fecha}</td>
                    <td>${pedido.cliente}</td>
                    <td>${pedido.total}</td>
                    <td>${pedido.observacion ?? ""}</td>
                    <td><button class="btn btn-primary" id="btnEditarPedido" data-id="${pedido.id}">Editar</button></td>
                </tr>
            `).join("");
        }
    } else {
        console.error("Error", data.error);
    }

    const tablaHistorial = initDataTable("#tablaHistorialP");
    editarPedido();
    tablaHistorial.on("draw.dt", editarPedido);
}

function verOrdenCompra() {
    document.getElementById("btnFiltrar").addEventListener("click", async () => {
        document.getElementById("thRuta").style.display = "none";
        const form = document.getElementById("formFiltro");
        const formData = new FormData(form);
        const rutasSeleccionadas = [...document.getElementById("slcRuta").selectedOptions].map(opt => opt.value);
        const proveedoresSeleccionados = [...document.getElementById("slcProveedor").selectedOptions].map(opt => opt.value);
        const formDataObj = Object.fromEntries(formData.entries());
        formDataObj.ruta = rutasSeleccionadas;
        formDataObj.proveedor = proveedoresSeleccionados;

        const data = await pet("controladores/pedidos.php", {
            funcion: "verordencompra",
            datosForm: formDataObj
        });

        if (data.orden) {
            const ordenArr = JSON.parse(data.orden);
            const tablaOrdenCompra = document.getElementById("ordenesCompra");

            if (tablaOrdenCompra && Array.isArray(ordenArr)) {
                if(ordenArr[0].ruta){
                    document.getElementById("thRuta").style.display = "block";
                }

                tablaOrdenCompra.innerHTML = ordenArr.map(orden => `
                    <tr>
                        <td>${orden.nombre}</td>
                        <td>${orden.cantidad}</td>
                        <td>${orden.costo}</td>
                        <td>${orden.proveedor}</td>
                        ${orden.ruta ? `<td>${orden.ruta}</td>` : ''}
                        <td>${orden.observacion}</td>
                    </tr>
                `).join("");
            }
            generarOrden();
        } else {
            console.error("Error en la respuesta del servidor:", data.error);
        }

        // actualizarTotal();
    });
}

async function listarProveedores() {
    const selectProveedores = document.getElementById("slcProveedor");
    if(selectProveedores) {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproveedores" });

        if (data.error) {
            console.error("Error:", data.error);
            return;
        }

        if (Array.isArray(data.proveedores)) {
            selectProveedores.innerHTML = "<option value='elegir'>Todos los proveedores</option>" + 
            data.proveedores.map(proveedor =>
                `<option value="${proveedor.id}">${proveedor.proveedor}</option>`
            ).join('');
        } else {
            selectProveedores.innerHTML = "<option value=''>No hay proveedores registrados</option>";
        }
    }
}

function initCalendars() {
    const fechaFin = document.getElementById("fechaFin");
    const hoy = new Date().toISOString().split("T")[0];
    fechaFin.value = hoy; 
}

function generarOrden() {
    document.getElementById("btnGenerarPDF").addEventListener("click", function () {
        $('#modalColumnas').modal('show');
    });

    document.getElementById("btnConfirmarPDF").addEventListener("click", function () {
        $('#modalColumnas').modal('hide');
    
        let datos = [];
        document.querySelectorAll("#ordenesCompra tr").forEach(row => {
            let cols = row.querySelectorAll("td");
    
            let fila = {
                producto: document.getElementById("chkProducto").checked ? cols[0]?.innerText || "" : null,
                cantidad: document.getElementById("chkCantidad").checked ? cols[1]?.innerText || "" : null,
                costo: document.getElementById("chkCosto").checked ? cols[2]?.innerText || "" : null,
                proveedor: document.getElementById("chkProveedor").checked ? cols[3]?.innerText || "" : null,
                ruta: document.getElementById("chkRuta").checked ? cols[4]?.innerText || "" : null,
                observacion: document.getElementById("chkObservacion").checked ? cols[5]?.innerText || "" : null
            };
            datos.push(fila);
        });
    
        fetch("../controladores/generarordenpdf.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ordenes: datos,
                incluirProducto: document.getElementById("chkProducto").checked,
                incluirCantidad: document.getElementById("chkCantidad").checked,
                incluirCosto: document.getElementById("chkCosto").checked,
                incluirProveedor: document.getElementById("chkProveedor").checked,
                incluirRuta: document.getElementById("chkRuta").checked,
                incluirObservacion: document.getElementById("chkObservacion").checked
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.open(data.pdfUrl, '_blank');
            } else {
                console.error("Error generando PDF:", data.error);
            }
        })
        .catch(error => console.error("Error generando PDF:", error));
    });
}
        