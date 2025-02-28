import { pet, initDataTable } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "crearCliente") {
        crearClientes();
    } else if (vista === "verClientes") {
        obtenerClientes();
    }
});

async function crearClientes() {
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

            if (respuesta.error) {
                console.error("Error:", respuesta.error);
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
        const clientes = document.getElementById("clientes");
        if (clientes) {
            respuesta.forEach(cliente => {
                clientes.innerHTML += `
                    <tr>
                        <td>${cliente.nombre}</td>
                        <td>${cliente.razonsocial}</td>
                        <td>${cliente.ubicacion}</td>
                        <td>${cliente.telefono}</td>
                    </tr>
                `;
            });
        } else {
            console.error("El elemento 'clientes' no existe en el DOM");
        }
    }

    initDataTable("#tablaClientes");
}
