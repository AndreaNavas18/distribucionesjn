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
        guardarPreFactura(idPedido);
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

    data.detalle.forEach((producto) => {
        let fila = `
        <tr>
            <td id='cantidadBD'>${producto.cantidad}</td>
            <td>${producto.nombre}</td>
            <td>${formatearMoneda(producto.precioventa)}</td>
            <td>${formatearMoneda(producto.cantidad * producto.precioventa)}</td>
            <td>${formatearMoneda(producto.preciosugerido)}</td>
            <td>${formatearMoneda(producto.cantidad * producto.preciosugerido)}</td>
            <td><input type='text-area' class='form-control' id='observacionproducto' name='observacionproducto' value='${producto.observacionproducto || producto.observacionproducto != null ? producto.observacionproducto : ""}'></td>
            <td><button class="btn btn-danger btnNoLlego" data-id=${producto.id}>No llegó</button></td>
            <td><input type='number' class='form-control' id='cantempacar' name='cantempacar' value='${(producto.faltante) ? producto.cantidad-producto.faltante : ""}'></td>
        </tr>
        `;
        tbody.innerHTML += fila;
    });

    changesPrefactura();
}

function changesPrefactura() {
    const btnNoLlego = document.querySelectorAll(".btnNoLlego");
    btnNoLlego.forEach(boton => {
        boton.addEventListener("click", async function () {
            console.log("CLICK No llegó");
            const fila = this.closest("tr");
            fila.classList.toggle("fila-no-llego");
            const cantidadInput = fila.querySelector("#cantempacar");
            const cantidadBD = fila.querySelector("#cantidadBD").textContent;
            if (fila.classList.contains("fila-no-llego")) {
                cantidadInput.value = cantidadBD;
            } else {
                cantidadInput.value = "";
            }
        });
    });
}

function guardarPreFactura(idPedido) {}