import { pet, protegerVista, initDataTable } from "./base.js";

document.addEventListener("DOMContentLoaded", function() {
    protegerVista(() => {
        const vista = document.body.id;
        if (vista === "crearUsuario") {
            const urlParams = new URLSearchParams(window.location.search);
            const idUsuario = urlParams.get('id');
            crearUsuario(idUsuario);
            if (idUsuario) {
                const titulo = document.getElementById("tituloU");
                if (titulo) titulo.textContent = "Editar usuario";
                const boton = document.querySelector("#crearUsuarioForm button[type='submit']");
                if (boton) boton.textContent = "Guardar cambios";
                const inputClave = document.getElementById("clave");
                if (inputClave) inputClave.required = false;
                cargarDatosUsuario(idUsuario);
            }
        } else if (vista === "verUsuarios") {
            obtenerUsuarios();
        }
    });
});

function crearUsuario(idUsuario = null) {
    const form = document.getElementById("crearUsuarioForm");
    
    if (form) { 
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            const usuarioData = {};
            formData.forEach((value, key) => {
                usuarioData[key] = value;
            });

            const respuesta = await pet("controladores/usuarios.php", {
                funcion: "crearusuario",
                dataUsuario: usuarioData,
                idUsuario: idUsuario
            });

            if (!respuesta.error) {
                Swal.fire({
                    title: "¡Éxito!", 
                    text: "Los datos se han guardado correctamente.",
                    icon: "success",
                    timer: 3000,
                    showConfirmButton: false
                });
                if (idUsuario) {
                    setTimeout(() => {
                        window.location.href = 'verUsuarios.php';
                    }, 2000);
                } else {
                    form.reset();
                }
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

async function cargarDatosUsuario(idUsuario) {
    const data = await pet("controladores/usuarios.php", { funcion: "verusuario", id: idUsuario });

    if (data.error) {
        console.error("Error:", data.error);
    } else {
        const usuarioEscogido = data.usuario[0];
        const campos = ["nombres", "apellidos", "cedula", "usuario", "rol"];
        campos.forEach(campo => {
            const input = document.getElementById(campo);
            if (input && usuarioEscogido[campo] !== undefined) {
                input.value = usuarioEscogido[campo];
            }
        });
    }
}

async function obtenerUsuarios() {
    const respuesta = await pet("controladores/usuarios.php", {
        funcion: "obtenerusuarios"
    });

    if (respuesta.error) {
        console.error("Error:", respuesta.error);
    } else {
        const usuariosArray = JSON.parse(respuesta.usuarios);
        const usuarios = document.getElementById("usuarios");

        if (usuarios) {
            usuarios.innerHTML = usuariosArray.map(usuario => `
                <tr>
                    <td>${usuario.usuario}</td>
                    <td>${usuario.nombrecompleto ?? ""}</td>
                    <td>${usuario.rol ?? ""}</td>
                    <td class='text-center'><button class="btn btn-primary" id="btnEditarUsuario" data-id="${usuario.id}">Editar</button></td>
                </tr>
            `).join("");
        } else {
            console.error("El elemento 'usuarios' no existe en el DOM");
        }
    }

    const tablaUsuarios = initDataTable("#tablaUsuarios");
    editarUsuario();
    tablaUsuarios.on("draw", editarUsuario);
}

function editarUsuario() {
    const btnEditarUsuario = document.querySelectorAll("#btnEditarUsuario");
    btnEditarUsuario.forEach(boton => {
        boton.addEventListener("click", function () {
            const idUsuario = this.dataset.id;
            window.location.href = `crearUsuario.php?id=${idUsuario}`;
        });
    });
}
