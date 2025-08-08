import { pet } from "./base.js";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formRegistro");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const nombre = form.nombre.value.trim();
    const apellido = form.apellido.value.trim();
    const cedula = form.cedula.value.trim();
    const usuario = form.usuario.value.trim();
    const clave = form.clave.value;
    const confirmar = form.confirmarClave.value;

    if (clave !== confirmar) {
      Swal.fire("Error", "Las contraseñas no coinciden", "error");
      return;
    }

    const dataUsuario = {
      nombre,
      apellido,
      cedula,
      usuario,
      clave,
      confirmar
    };

    const res = await pet("controladores/registro.php", {
      funcion: "registrar",
      dataUsuario: dataUsuario
    });

    if (res.exito) {
      Swal.fire("Éxito", "Usuario registrado correctamente", "success").then(() => {
        window.location.href = "/login.html";
      });
    } else {
      Swal.fire("Error", res.mensaje || "No se pudo registrar", "error");
    }
  });
});
