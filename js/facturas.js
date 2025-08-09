import { SERVER, pet, initSelect2, formatearMoneda, initDataTable, protegerVista } from "./base.js";
let tablaPedidosActivos;
let idProductoEditando = null;

document.addEventListener("DOMContentLoaded", function() {
    protegerVista(() => {
        const vista = document.body.id;
        if (vista === "prefactura") {
            const urlParams = new URLSearchParams(window.location.search);
            console.log("utlParams" + urlParams);
            const idPedido = urlParams.get('id');
            console.log("idPedido" + idPedido);
            agregarProducto();
            listarProductos();
            obtenerClientes();
            cargarPreFactura(idPedido);
        } else if (vista === "pedidosActivos") {
            inicialPreFactura();
        }
    })
});

async function inicialPreFactura () {
    console.log("Cargando pedidos activos...");
    const data = await pet("controladores/pedidos.php", { funcion: "obtenerpedidos" });
    if (data.pedidos && Array.isArray(data.pedidos)) {
        const tbody = document.getElementById("bodyPedidosA");
        tbody.innerHTML = data.pedidos.map(pedido => {
            return `
            <tr data-id="${pedido.id}">
                <td>${pedido.fecha}</td>
                <td>${pedido.cliente}</td>
                <td class='text-center'><button class="btn btn-primary btnEmpacar" data-id="${pedido.id}">Empacar</button></td>
            </tr>
            `;
        }).join("");
        tablaPedidosActivos = initDataTable("#tablaPedidosA");
        empacarPedido();
        tablaPedidosActivos.on("draw.dt", empacarPedido);
    } else {
        console.error("Error", data.error);
    }
}

function empacarPedido() {
    document.querySelector("#tablaPedidosA tbody").addEventListener("click", (e) => {
        const btn = e.target.closest(".btnEmpacar");
        if (!btn) return;
    
        const id = btn.getAttribute("data-id");
        idProductoEditando = id;
        console.log("ID del pedido:", idProductoEditando);
    
        window.location.href = `preFactura.php?id=${idProductoEditando}`;
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
        clientes.innerHTML = clientesArray.map(cliente =>
            `<option value="${cliente.id}">${cliente.nombre}</option>`
        ).join('');
    } else {
        console.error("El elemento 'slcClientes' no existe en el DOM");
    }
}

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
                            text: producto.nombre
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

async function cargarPreFactura(idPedido) {
    const data = await pet("controladores/pedidos.php", { funcion: "verpedido", id: idPedido });
    const selectClientes = document.getElementById("slcClientes");
    if (data.error) {
        console.error("Error:", data.error);
        return;
    }
    console.log(data);

    selectClientes.value = data.pedido.idcliente;
    selectClientes.setAttribute("disabled", "true");
    document.getElementById("observacion").value = data.pedido.observacion;

    const tbody = document.querySelector("#tablaPreFactura tbody");
    tbody.innerHTML = "";

    data.detalle.forEach((detallepedido) => {
        let fila = `
        <tr data-id="${detallepedido.id}" >
            <td id='cantidadBD' class='cantidadBD'>${detallepedido.cantidad}</td>
            <td>${detallepedido.nombre}</td>
            <td>${formatearMoneda(detallepedido.precioventa)}</td>
            <td class='subtotal'>${formatearMoneda(detallepedido.cantidad * detallepedido.precioventa)}</td>
            <td>${formatearMoneda(detallepedido.preciosugerido)}</td>
            <td>${formatearMoneda(detallepedido.cantidad * detallepedido.preciosugerido)}</td>
            <td><textarea class='form-control' name='observacionproducto'>
            ${detallepedido.observacionproducto || detallepedido.observacionproducto != null ? detallepedido.observacionproducto : ""}
            </textarea></td>
            <td class="btn-group">
                <button class="btn btn-danger btnNoLlego" data-id=${detallepedido.id}>No llegó</button>
                <button class="btn btn-primary btnOK" data-id=${detallepedido.id}>Completo</button>
            </td>
            <td><input type='number' class='form-control' name='cantempacar' value='${(detallepedido.faltante) ? detallepedido.cantidad-detallepedido.faltante : ""}'></td>
        </tr>
        `;
        tbody.innerHTML += fila;
    });

    changesPrefactura(idPedido);
}

function changesPrefactura(idPedido) {
    const btnNoLlego = document.querySelectorAll(".btnNoLlego");
    const btnOK = document.querySelectorAll(".btnOK");
    const btnGuardar = document.getElementById("btnGuardarPrefactura");

    btnNoLlego.forEach(boton => {
        boton.addEventListener("click", async function () {
            console.log("CLICK No llegó");
            const fila = this.closest("tr");
            fila.classList.toggle("fila-no-llego");
            const cantidadInput = fila.querySelector("input[name='cantempacar']");
            if (fila.classList.contains("fila-no-llego")) {
                cantidadInput.value = 0;
                fila.classList.remove("fila-ok");
            }
        });
    });

    btnOK.forEach(boton => {
        boton.addEventListener("click", async function () {
            console.log("CLICK OK");
            const fila = this.closest("tr");
            fila.classList.toggle("fila-ok");
            const cantidadInput = fila.querySelector("input[name='cantempacar']");
            if (fila.classList.contains("fila-ok")) {
                cantidadInput.value = fila.querySelector(".cantidadBD").textContent;
                fila.classList.remove("fila-no-llego");
            }
        });
    });

    //Si la cantidad - faltante es 0, se pone en verde
    const filas = document.querySelectorAll("#tablaPreFactura tbody tr");
    filas.forEach(fila => {
        const cantidad = parseInt(fila.querySelector(".cantidadBD").textContent);
        const faltante = parseInt(fila.querySelector("input[name='cantempacar']").value);
        if (cantidad - faltante === 0) {
            fila.classList.add("fila-ok");
        } else {
            fila.classList.remove("fila-ok");
        }
        //Si la cantidad - faltante es igual a la cantidad, se pone en rojo
        if (faltante === 0) {
            fila.classList.add("fila-no-llego");
        } else {
            fila.classList.remove("fila-no-llego");
        }
    });

    //Guardar cambios

    btnGuardar.addEventListener("click", async function () {
        const tbody = document.querySelector("#tablaPreFactura tbody");
        const filas = tbody.querySelectorAll("tr");
        const cambios = Array.from(filas).map(fila => {
            const iddetalle = fila.getAttribute("data-id");
            const cantidadempacada = fila.querySelector("input[name='cantempacar']").value;
            const observacion = fila.querySelector("textarea[name='observacionproducto']").value;
            const idproducto = fila.getAttribute("data-idproducto");
            const cantidad = fila.querySelector(".cantidadBD").textContent;
            const precio = fila.querySelector(".subtotal").textContent;
            return { iddetalle, cantidadempacada, observacion, idproducto, cantidad, precio };
        });
        console.log(cambios);
        const data = await pet("controladores/facturas.php", { funcion: "guardarprefactura", idpedido: idPedido, cambios: cambios });
        if (data.mensaje) {
            Swal.fire({
                title: "¡Éxito!",
                text: data.mensaje,
                icon: "success",
                showCancelButton: true,
                confirmButtonText: "Pedidos Activos",
                cancelButtonText: "Home"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'pedidosActivos.php';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = '../vistas/home.php';
                }
            });
        } else {
            Swal.fire({
                title: "Error!",
                text: "Hubo un error al guardar la prefactura.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        }
        console.log(data);
    });
}

async function agregarProducto() {
    const btnAgregar = document.getElementById("btnAgregar");
    const btnCantidad = document.getElementById("btnCantidad");
    const selectProductos = document.getElementById("slcProductos");
    const inputCantidad = document.getElementById("cantidad");
    const tablaPreFacturaBody = document.querySelector("#tablaPreFactura tbody");
    const tdIdProducto = document.getElementById("codigoProd");
    tdIdProducto.style.display = "none";

    if (inputCantidad && btnAgregar) {
        inputCantidad.addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                btnAgregar.click();
            }
        });
    }

    if (!btnAgregar || !btnCantidad || !selectProductos || !inputCantidad || !tablaPreFacturaBody) {
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
        const productoYaAgregado = tablaPreFacturaBody.querySelector(`tr[data-id="${idProducto}"]`);
        
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
        fila.setAttribute("data-idproducto", idProducto);
        fila.innerHTML = `
            <td id='cantidadBD' class='cantidadBD'>${cantidad}</td>
            <td>${nombreProducto}</td>
            <td>${formatearMoneda(precioProducto)}</td>
            <td class='subtotal'>${formatearMoneda(subTotal)}</td>
            <td>${formatearMoneda(precioFinal)}</td>
            <td>${formatearMoneda(subSugerido)}</td>
            <td><textarea class='form-control' name='observacionproducto'></textarea></td>
            <td class="btn-group">
            <button type="button" class="btn btn-danger btnNoLlego">No llegó</button>
            <button type="button" class="btn btn-primary btnOK">Completo</button>
            </td>
            <td><input type='number' class='form-control' name='cantempacar' min='0' max='${cantidad}''></td>
        `;

        fila.querySelector(".btnNoLlego").addEventListener("click", function () {
            const input = fila.querySelector("input[name='cantempacar']");
            input.value = 0;
        });

        fila.querySelector(".btnOK").addEventListener("click", function () {
            const input = fila.querySelector("input[name='cantempacar']");
            input.value = cantidad;
        });

        tablaPreFacturaBody.appendChild(fila);
        inputCantidad.value = "";
        $(selectProductos).val(null).trigger("change");
    });

    btnCantidad.addEventListener("click", function () {
        inputCantidad.value = 12;
    });

}
