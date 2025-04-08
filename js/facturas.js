import { SERVER, pet, initSelect2, formatearMoneda, initDataTable  } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "prefactura") {
        const urlParams = new URLSearchParams(window.location.search);
        const idPedido = urlParams.get('id');
        inicialPreFactura(idPedido);
        if (idPedido) {
            cargarDatosPedido(idPedido);
        }
        guardarPreFactura(idPedido);
    }
});

function inicialPreFactura(idPedido) {}

function cargarDatosPedido(idPedido) {}

function guardarPreFactura(idPedido) {}