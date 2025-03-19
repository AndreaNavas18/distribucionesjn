import { pet, initDataTable } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "crearCliente") {
        const urlParams = new URLSearchParams(window.location.search);
        const idCliente = urlParams.get('id');
        crearClientes();
        if (idCliente) {
            cargarDatosCliente(idCliente);
        }
    } else if (vista === "verClientes") {
        obtenerClientes();
    }
});

function crearClientes() {
    const form = document.getElementById("formCliente");
    
    if (form) { 
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            const clienteData = {};
            formData.forEach((value, key) => {
                clienteData[key] = value.toUpperCase();
            });

            const respuesta = await pet("controladores/clientes.php", {
                funcion: "crearcliente",
                dataCliente: clienteData
            });

            if (!respuesta.error) {
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

async function obtenerClientes() {
    const respuesta = await pet("controladores/clientes.php", {
        funcion: "obtenerclientes"
    });

    if (respuesta.error) {
        console.error("Error:", respuesta.error);
    } else {
        const clientesArray = JSON.parse(respuesta.clientes);
        const clientes = document.getElementById("clientes");

        if (clientes) {
            clientes.innerHTML = clientesArray.map(cliente => `
                <tr>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.razonsocial ?? ""}</td>
                    <td>${cliente.ubicacion ?? ""}</td>
                    <td>${cliente.direccion ?? ""}</td>
                    <td>${cliente.telefono ?? ""}</td>
                    <td>${cliente.telefono2 ?? ""}</td>
                    <td>${cliente.ruta ?? ""}</td>
                    <td><button class="btn btn-primary" id="btnEditarCliente" data-id="${cliente.id}">Editar</button></td>
                </tr>
            `).join("");
        } else {
            console.error("El elemento 'clientes' no existe en el DOM");
        }
    }

    const tablaClientes = initDataTable("#tablaClientes");
    editarCliente();
    tablaClientes.on("draw", editarCliente);
}

function editarCliente() {
    const btnEditarCliente = document.querySelectorAll("#btnEditarCliente");
    btnEditarCliente.forEach(boton => {
        boton.addEventListener("click", function () {
            const idCliente = this.dataset.id;
            window.location.href = `crearCliente.html?id=${idCliente}`;
        });
    });
}

async function cargarDatosCliente(idCliente) {
    const data = await pet("controladores/clientes.php", { funcion: "vercliente", id: idCliente });

    if (data.error) {
        console.error("Error:", data.error);
    } else {
        const clienteEscogido = data.cliente[0];
        const campos = ["nombre", "razonsocial", "ubicacion", "direccion", "telefono", "telefono2", "ruta"];
        campos.forEach(campo => {
            const input = document.getElementById(campo);
            if (input && clienteEscogido[campo] !== undefined) {
                input.value = clienteEscogido[campo];
            }
        });
    }
}
