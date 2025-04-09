import { SERVER, pet, initSelect2, formatearMoneda, initDataTable  } from "./base.js";
let tablaPrefactura;
let idProductoEditando = null;

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "prefactura") {
        const urlParams = new URLSearchParams(window.location.search);
        const idPedido = urlParams.get('id');
        inicialPreFactura();
        cargarPreFactura(idPedido);
        guardarPreFactura(idPedido);
    }
});

async function inicialPreFactura () {
    const data = await pet("controladores/pedidos.php", { funcion: "obtenerpedidos" });
    if (data.pedidos && Array.isArray(data.pedidos)) {
        const tbody = document.getElementById("tbodyPreFactura");
        tbody.innerHTML = data.pedidos.map(pedido => {
            return `
            <tr data-id="${pedido.id}">
                <td>${pedido.fecha}</td>
                <td>${pedido.cliente}</td>
                <td><button class="btn btn-primary btnEmpacar">Empacar</button></td>
            </tr>
            `;
        }).join("");
        tablaPrefactura = initDataTable("#tablaPreFactura");
        empacarPedido();
        tablaPrefactura.on("draw.dt", empacarPedido);
    } else {
        console.error("Error", data.error);
    }
}

function empacarPedido() {
    const btnEmpacarPedido = document.querySelectorAll(".btnEmpacar");
    document.querySelector("#tablaPreFactura tbody").addEventListener("click", (e) => {
        const btn = e.target.closest(".btnEmpacar");
        if (!btn) return;
    
        const id = btn.getAttribute("data-id");
        idProductoEditando = id;
    
        const fila = btn.closest("tr");
        const celdas = fila.querySelectorAll("td");
    
        window.location.href = `preFactura.html?id=${idProductoEditando}`;
        
    });
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
}

function guardarPreFactura(idPedido) {}