import { pet, initDataTable } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "login") {
        inicializarLogin();
    }
});

function inicializarLogin() {
    const formulario = document.getElementById("formLogin");

    if (!formulario) {
        console.error("No se encontró el formulario de login.");
        return;
    }

    formulario.addEventListener("submit", async function (e) {
        e.preventDefault();

        const usuario = formulario.usuario?.value.trim();
        const clave = formulario.clave?.value.trim();

        if (!usuario || !clave) {
            mostrarError("Debes completar ambos campos.");
            return;
        }

        const respuesta = await pet("login.php", {
            funcion: "login",
            usuario,
            clave
        });

        if (respuesta.ok) {
            window.location.href = respuesta.redirect;
        } else {
            mostrarError(respuesta.mensaje || "Credenciales incorrectas.");
        }
    });
}

function mostrarError(mensaje) {
    const alerta = document.getElementById("alertaLogin");
    if (alerta) {
        alerta.textContent = mensaje;
        alerta.style.display = "block";
    } else {
        alert(mensaje);
    }
}