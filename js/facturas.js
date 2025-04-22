import { SERVER, pet, initSelect2, formatearMoneda, initDataTable  } from "./base.js";
let tablaPedidosActivos;
let idProductoEditando = null;

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "prefactura") {
        const urlParams = new URLSearchParams(window.location.search);
        console.log("utlParams" + urlParams);
        const idPedido = urlParams.get('id');
        console.log("idPedido" + idPedido);
        listarProductos();
        obtenerClientes();
        cargarPreFactura(idPedido);
    } else if (vista === "pedidosActivos") {
        inicialPreFactura();
    }
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
                <td><button class="btn btn-primary btnEmpacar" data-id="${pedido.id}">Empacar</button></td>
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
    
        window.location.href = `preFactura.html?id=${idProductoEditando}`;
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
        <tr data-id="${detallepedido.id}">
            <td id='cantidadBD'>${detallepedido.cantidad}</td>
            <td>${detallepedido.nombre}</td>
            <td>${formatearMoneda(detallepedido.precioventa)}</td>
            <td>${formatearMoneda(detallepedido.cantidad * detallepedido.precioventa)}</td>
            <td>${formatearMoneda(detallepedido.preciosugerido)}</td>
            <td>${formatearMoneda(detallepedido.cantidad * detallepedido.preciosugerido)}</td>
            <td><input type='text-area' class='form-control' id='observacionproducto' name='observacionproducto' value='${detallepedido.observacionproducto || detallepedido.observacionproducto != null ? detallepedido.observacionproducto : ""}'></td>
            <td class="btn-group">
                <button class="btn btn-danger btnNoLlego" data-id=${detallepedido.id}>No llegó</button>
                <button class="btn btn-primary btnOK" data-id=${detallepedido.id}>Completo</button>
            </td>
            <td><input type='number' class='form-control' id='cantempacar' name='cantempacar' value='${(detallepedido.faltante) ? detallepedido.cantidad-detallepedido.faltante : ""}'></td>
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
            const cantidadInput = fila.querySelector("#cantempacar");
            if (fila.classList.contains("fila-no-llego")) {
                cantidadInput.value = "";
                fila.classList.remove("fila-ok");
            }
        });
    });

    btnOK.forEach(boton => {
        boton.addEventListener("click", async function () {
            console.log("CLICK OK");
            const fila = this.closest("tr");
            fila.classList.toggle("fila-ok");
            const cantidadInput = fila.querySelector("#cantempacar");
            if (fila.classList.contains("fila-ok")) {
                cantidadInput.value = fila.querySelector("#cantidadBD").textContent;
                fila.classList.remove("fila-no-llego");
            }
        });
    });

    btnGuardar.addEventListener("click", async function () {
        const tbody = document.querySelector("#tablaPreFactura tbody");
        const filas = tbody.querySelectorAll("tr");
        const cambios = Array.from(filas).map(fila => {
            const iddetalle = fila.getAttribute("data-id");
            const cantidadempacada = fila.querySelector("#cantempacar").value;
            const observacion = fila.querySelector("#observacionproducto").value;
            return { iddetalle, cantidadempacada, observacion };
        });
        console.log(cambios);
        const data = await pet("controladores/facturas.php", { funcion: "guardarprefactura", idpedido: idPedido, cambios: cambios });
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
                text: "Hubo un error al guardar la prefactura.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        }
        console.log(data);
    });
}