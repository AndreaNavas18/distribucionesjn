import { SERVER, pet, initSelect2, formatearMoneda, initDataTable  } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "tomarPedido") {
        const urlParams = new URLSearchParams(window.location.search);
        const idPedido = urlParams.get('id');
        inicialTomaPedidos(idPedido);
        listarProductos();
        agregarProducto();
        if (idPedido) {
            cargarDatosPedido(idPedido);
        } else {
            obtenerClientes();
        }
        guardarPedido(idPedido);
    } else if (vista === "historialPedidos") {
        cargarPedidos();
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
            dropdownParent: $('#divTarjetaP'),
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
                            text: producto.nombre + " - " + (producto.precioventa || ""),
                        }))
                    };
                },
                cache: true
            }
        });
        $('#slcProductos').on('select2:select', function (e) {
            const inputCantidad = document.getElementById("cantidad");
            if (inputCantidad) {
                inputCantidad.focus();
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

    await obtenerClientes();
    console.log("2data");


    selectClientes.value = data.pedido.idcliente;
    document.getElementById("totalPedido").value = formatearMoneda(data.pedido.total);
    document.getElementById("observacion").value = data.pedido.observacion;

    const tbody = document.querySelector("#tablaPedido tbody");
    tbody.innerHTML = "";

    data.detalle.forEach((producto) => {
        const fila = document.createElement("tr");
        fila.setAttribute("data-id", producto.idproducto);
        fila.innerHTML = `
            <td><input type='number' min='1' step='1' class='form-control cantidadproducto' name='cantidad' value='${producto.cantidad || producto.cantidad != null ? producto.cantidad : ""}'></td>
            <td>${producto.nombre}</td>
            <td>${formatearMoneda(producto.precioventa)}</td>
            <td>${formatearMoneda(producto.cantidad * producto.precioventa)}</td>
            <td>${formatearMoneda(producto.preciosugerido)}</td>
            <td>${formatearMoneda(producto.cantidad * producto.preciosugerido)}</td>
            <td><textarea class='form-control' name='observacionproducto'>${producto.observacionproducto || producto.observacionproducto != null ? producto.observacionproducto : ""}</textarea></td>
            <td><button class="btn btn-danger btnEliminar">Eliminar</button></td>
            <td>
            <div class="form-check">
                <input 
                class="form-check-input no-orden" 
                type="checkbox" 
                value="${producto.id}" 
                ${producto.noorden == 1 ? "checked" : ""}
                id="noorden-${producto.id}"
                >
                <label class="form-check-label" for="noorden-${producto.id}">
                No incluir
                </label>
            </div>
            </td>
        `;

        fila.querySelector(".btnEliminar").addEventListener("click", function () {
            fila.remove();
            actualizarTotal(totalPedidoInput);
        });
        tbody.appendChild(fila);
    });

    actualizarTotal(totalPedidoInput);
}

function editarPedido() {
    console.log("Editar pedido");
    const btnEditarPedido = document.querySelectorAll("#btnEditarPedido");
    btnEditarPedido.forEach(boton => {
        boton.addEventListener("click", function () {
            console.log("Editar pedido xx");
            const idPedido = this.dataset.id;
            window.location.href = `tomarPedido.html?id=${idPedido}`;
        });
    });
}

async function obtenerClientes() {
    console.log("Obteniendo clientes");
    const data = await pet("controladores/clientes.php", { funcion: "obtenerclientes" });
    console.log("Clientes obtenidos:");
    
    if (data.error) {
        console.error("Error:", data.error);
        return;
    }
    const clientesArray = JSON.parse(data.clientes);
    const clientes = document.getElementById("slcClientes");
    if (clientes) {
        clientes.innerHTML = "<option value=''>Seleccione un cliente</option>" + clientesArray.map(cliente =>
            `<option value="${cliente.id}">${cliente.nombre + " - " + (cliente.ubicacion != null ? cliente.ubicacion : "") + " - Ruta: " + (cliente.ruta != null ? cliente.ruta : "")}</option>`
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
                <button type="button" class="btn btn-outline-primary precio-btn" data-id="1" data-precio="${precios.base}">Costo: ${formatearMoneda(precios.base)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-success precio-btn" data-id="10" data-precio="${precios.diez}">+10%: ${formatearMoneda(precios.diez)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-warning precio-btn" data-id="15" data-precio="${precios.quince}">+15%: ${formatearMoneda(precios.quince)}</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger precio-btn" data-id="25" data-precio="${precios.veinticinco}">+25%: ${formatearMoneda(precios.veinticinco)}</button>
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
    const data = await pet("controladores/productos.php", { funcion: "obtenerproductos" });
    const producto = data.productos.find(p => p.id == productoId);
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

    if (inputCantidad && btnAgregar) {
        inputCantidad.addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                btnAgregar.click();
            }
        });
    }

    if (!btnAgregar || !btnCantidad || !selectProductos || !inputCantidad || !tablaPedidoBody || !totalPedidoInput) {
        console.error("Uno o más elementos no existen en el DOM");
        return;
    }

    let infoProductos = [];

    try {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproductos" });
        if (data.productos && Array.isArray(data.productos)) {
            infoProductos = data.productos;
            console.log("Productos obtenidos:", data);
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
        const productoYaAgregado = tablaPedidoBody.querySelector(`tr[data-id="${idProducto}"]`);
        
        if (!producto) {
            console.error("Producto no encontrado en la lista.");
            return;
        }

        if (productoYaAgregado) {
            Swal.fire({
                title: "!!!",
                text: "Este producto ya se encuentra en el pedido.",
                icon: "info",
                timer: 2000,
                showConfirmButton: false
            });
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
            <td><input type='number' min='1' step='1' class='form-control cantidadproducto' name='cantidad' value='${cantidad}'></td>
            <td>${nombreProducto}</td>
            <td>${formatearMoneda(precioProducto)}</td>
            <td>${formatearMoneda(subTotal)}</td>
            <td>${formatearMoneda(precioFinal)}</td>
            <td>${formatearMoneda(subSugerido)}</td>
            <td><textarea class='form-control' name='observacionproducto'></textarea></td>
            <td><button class="btn btn-danger btnEliminar">Eliminar</button></td>
            <td>
             <div class="form-check">
                <input 
                    class="form-check-input no-orden" 
                    type="checkbox" 
                    value="${idProducto}" 
                    id="noorden-${idProducto}"
                    title="Marcar si NO se debe incluir este producto en la orden"
                >
                <label class="form-check-label small" for="noorden-${idProducto}">
                    No incluir
                </label>
            </div>
            </td>
            
            
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

        let filasConErrores = [];

        filas.forEach((fila, index) => {
            const cantidadInput = fila.querySelector(".cantidadproducto");
            const cantidad = parseInt(cantidadInput.value, 10);

            if (isNaN(cantidad) || cantidad < 1) {
                filasConErrores.push(index + 1);
                cantidadInput.classList.add("input-error");
                cantidadInput.focus();
            } else {
                cantidadInput.classList.remove("input-error");
            }
        });

        if (filasConErrores.length > 0) {
            Swal.fire({
                title: "CANTIDAD INVÁLIDA",
                text: `Corrige las cantidades en las siguientes filas: ${filasConErrores.join(", ")}`,
                icon: "error",
                timer: 4000,
                showConfirmButton: false
            });
            return;
        }

        const productos = Array.from(filas).map(fila => ({
            id: parseInt(fila.getAttribute("data-id"), 10),
            cantidad: parseInt(fila.querySelector(".cantidadproducto").value, 10),
            preciofinal: parseFloat(fila.cells[2]?.textContent.trim().replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.')),
            observacionproducto: fila.querySelector("textarea[name='observacionproducto']").value.trim(),
            preciosugerido: parseFloat(fila.cells[4]?.textContent.trim().replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.')),
            noorden: fila.querySelector(".no-orden").checked ? 1 : 0
        })).filter(p => p.id && p.cantidad);
        
        console.log("Productos a guardar:", productos);
    
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
            if (idPedido) {
                setTimeout(() => {
                    window.location.href = '../index.html';
                }, 2000);
            } else {
                tablaPedidoBody.innerHTML = "";
                document.getElementById("totalPedido").value = "";
                document.getElementById("slcClientes").value = "elegir";
                document.getElementById("preciosPosibles").innerHTML = "";
                document.getElementById("preciosPosibles").style.display = "none";
                document.getElementById("observacion").value = "";
            }
        } else {
            Swal.fire({
                title: "Error!",
                text: "Hubo un error al guardar el pedido.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

async function cargarPedidos(filtro = "") {
    const data = await pet("controladores/pedidos.php", { funcion: "obtenerpedidos" });

    if (data.pedidos && Array.isArray(data.pedidos)) {
        let pedidosFiltrados = data.pedidos;

        if (filtro === "empacados") {
            pedidosFiltrados = pedidosFiltrados.filter(pedido => pedido.estado == 1);
        } else if (filtro === "sinempacar") {
            pedidosFiltrados = pedidosFiltrados.filter(pedido => pedido.estado != 1);
        }

         if ( $.fn.dataTable.isDataTable("#tablaHistorialP") ) {
            $('#tablaHistorialP').DataTable().clear().destroy();
        }


        const pedidos = document.getElementById("pedidos");

        if (pedidos) {
            pedidos.innerHTML = pedidosFiltrados.map(pedido => `
                <tr>
                    <td><input type="checkbox" class="seleccionar-pedido" value="${pedido.id}" ${pedido.estado == 1 ? "" : "disabled"}></td>
                    <td>${pedido.fecha}</td>
                    <td>${pedido.cliente}</td>
                    <td>$${new Intl.NumberFormat('es-CO').format(pedido.total)}</td>
                    <td>${pedido.observacion ?? ""}</td>
                    <td class='text-center'><button class="btn btn-primary" id="btnEditarPedido" data-id="${pedido.id}">Editar</button></td>
                </tr>
            `).join("");
        }


        const tablaHistorial = initDataTable("#tablaHistorialP");
        editarPedido();
        tablaHistorial.on("draw.dt", editarPedido);
        document.getElementById("btnFiltroHp").addEventListener("click", () => {
            const filtro = document.getElementById("filtroPedido").value;
            cargarPedidos(filtro);
        });
        
        imprimirPedidos();
    } else {
        console.error("Error", data.error);
    }
}

function imprimirPedidos() {
    document.getElementById("btnImprimirSeleccionados").addEventListener("click", async () => {
        const seleccionados = Array.from(document.querySelectorAll(".seleccionar-pedido:checked")).map(checkbox => checkbox.value);
    
        if (seleccionados.length === 0) {
            alert("Por favor selecciona al menos un pedido para imprimir.");
            return;
        }

        fetch("../controladores/generarfacturaspdf.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ids: seleccionados })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                data.pdfUrls.forEach(url => window.open(url, '_blank'));
            } else {
                console.error(data.error);
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

function verOrdenCompra() {
     const btnFiltrar = document.getElementById("btnFiltrar");
    // Elimina listeners previos
    btnFiltrar.replaceWith(btnFiltrar.cloneNode(true));
    document.getElementById("btnFiltrar").addEventListener("click", async () => {
        const seleccionados = new Set(
            Array.from(document.querySelectorAll('input[name="pedidos[]"]:checked')).map(cb => String(cb.value))
        );
        console.log("Seleccionados antes de filtrar:", [...seleccionados]);
        document.getElementById("thRuta").style.display = "none";
        const listaPedidosDiv = document.getElementById("listaPedidos");
        const form = document.getElementById("formFiltro");
        const formData = new FormData(form);
        const rutasSeleccionadas = [...document.querySelectorAll('input[name="ruta[]"]:checked')].map(cb => cb.value);
        const proveedoresSeleccionados = [...document.querySelectorAll('input[name="proveedor[]"]:checked')].map(cb => cb.value);
        const formDataObj = Object.fromEntries(formData.entries());
        const pedidosSeleccionados = [...document.querySelectorAll('input[name="pedidos[]"]:checked')].map(cb => cb.value);
        formDataObj.ruta = rutasSeleccionadas;
        formDataObj.proveedor = proveedoresSeleccionados;
        formDataObj.pedidos = pedidosSeleccionados;

        const respuesta = await pet("controladores/pedidos.php", {
            funcion: "verordencompra",
            datosForm: formDataObj
        });

        const data = respuesta.orden || {};
        
        const tablaOrdenCompra = document.getElementById("ordenesCompra");
        if (Array.isArray(data.orden) && data.orden.length > 0) {
            if (data.orden[0].ruta) {
                document.getElementById("thRuta").style.display = "block";
            }

            let totalOrden = 0;

            let filasHTML = data.orden.map((orden, index) => {
                const cantidad = parseFloat(orden.cantidad) || 0;
                const costo = parseFloat(orden.costo) || 0;

                totalOrden += cantidad * costo;

                return `
                    <tr 
                        data-producto="${orden.nombre}" 
                        data-cantidad="${orden.cantidad}" 
                        data-costo="${orden.costo}" 
                        data-proveedor="${orden.proveedor || ''}" 
                        data-observacion="${orden.observacion || ''}" 
                        data-ruta="${orden.ruta || ''}">
                        <td>${index + 1}</td>
                        <td>${orden.nombre}</td>
                        <td>${orden.cantidad}</td>
                        <td>${orden.costo}</td>
                        <td>${orden.proveedor || ''}</td>
                        ${orden.ruta ? `<td>${orden.ruta}</td>` : ''}
                        <td>${orden.observacion || ''}</td>
                    </tr>
                `;
            }).join("");

            tablaOrdenCompra.innerHTML = filasHTML;

            document.getElementById("totalOrdenTexto").innerText = "TOTAL ORDEN: $" + totalOrden.toLocaleString('es-CO');

            generarOrden();
        } else {
            tablaOrdenCompra.innerHTML = "";
            // tablaOrdenCompra.innerHTML = "<tr><td colspan='6'>No hay órdenes de compra registradas</td></tr>";
            console.error("Error en la respuesta del servidor:", data.error);
        }

        if (Array.isArray(data.pedidos) && data.pedidos.length > 0) {
            console.log("Pedidos devueltos por el backend:", data.pedidos.map(p => p.id));
            listaPedidosDiv.innerHTML = data.pedidos.map(pedido => `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="pedidos[]" value="${pedido.id}" id="pedido${pedido.id}"
                        ${seleccionados.has(String(pedido.id)) ? 'checked' : ''}>
                    <label class="form-check-label" for="pedido${pedido.id}">
                        Pedido #${pedido.id} - ${pedido.fecha} (${pedido.cliente || 'Sin cliente'} - ${pedido.ubicacion || 'Sin ubicación'})
                    </label>
                </div>
            `).join('');
        } else {
            listaPedidosDiv.innerHTML = "<p>No se encontraron pedidos para los filtros seleccionados</p>";
        }
    });
}

async function listarProveedoresold() {
    const selectProveedores = document.getElementById("slcProveedor");
    if(selectProveedores) {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproveedores" });

        if (data.error) {
            console.error("Error:", data.error);
            return;
        }

        if (Array.isArray(data.proveedores)) {
            selectProveedores.innerHTML = "<option value=''>Todos los proveedores</option>" + 
            data.proveedores.map(proveedor =>
                `<option value="${proveedor.id}">${proveedor.proveedor}</option>`
            ).join('');
        } else {
            selectProveedores.innerHTML = "<option value=''>No hay proveedores registrados</option>";
        }
    }
}

async function listarProveedores() {
    const contenedor = document.getElementById("contenedorProveedores");

    if (contenedor) {
        const data = await pet("controladores/productos.php", { funcion: "obtenerproveedores" });

        if (data.error) {
            console.error("Error:", data.error);
            contenedor.innerHTML = "<p class='text-danger'>Error al cargar proveedores</p>";
            return;
        }

        if (Array.isArray(data.proveedores) && data.proveedores.length > 0) {
            contenedor.innerHTML = data.proveedores.map(proveedor => `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="proveedor[]" value="${proveedor.id}" id="proveedor${proveedor.id}">
                    <label class="form-check-label" for="proveedor${proveedor.id}">
                        ${proveedor.proveedor}
                    </label>
                </div>
            `).join('');
        } else {
            contenedor.innerHTML = "<p>No hay proveedores registrados</p>";
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
            let fila = {
                producto: document.getElementById("chkProducto").checked ? row.dataset.producto : null,
                cantidad: document.getElementById("chkCantidad").checked ? row.dataset.cantidad : null,
                costo: document.getElementById("chkCosto").checked ? row.dataset.costo : null,
                proveedor: document.getElementById("chkProveedor").checked ? row.dataset.proveedor : null,
                observacion: document.getElementById("chkObservacion").checked ? row.dataset.observacion : null,
                ruta: document.getElementById("chkRuta").checked ? row.dataset.ruta : null
            };
            datos.push(fila);
        });

        console.log("Datos a enviar: ", datos);
    
        fetch("../controladores/generarordenpdf.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ordenes: datos,
                incluirProducto: document.getElementById("chkProducto").checked,
                incluirCantidad: document.getElementById("chkCantidad").checked,
                incluirCosto: document.getElementById("chkCosto").checked,
                incluirProveedor: document.getElementById("chkProveedor").checked,
                incluirObservacion: document.getElementById("chkObservacion").checked,
                incluirRuta: document.getElementById("chkRuta").checked,
                incluirTotal: document.getElementById("chkTotalOrden").checked
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
        