import { pet, protegerVista } from "./base.js";

document.addEventListener("DOMContentLoaded", function () {
    protegerVista(() => {
        const vista = document.body.id;

        if (vista === "verPermisos") {
            inicializarPermisos();
        }
    });
});

async function inicializarPermisos() {
    await cargarRoles();
    await cargarUsuarios();

    document.getElementById("selectRol").addEventListener("change", async function () {
        const idRol = this.value;
        if (idRol) {
            await mostrarPermisosRol(idRol);
        } else {
            document.getElementById("permisosRol").innerHTML = "";
        }
    });

    document.getElementById("selectUsuario").addEventListener("change", async function () {
        const idUsuario = this.value;
        if (idUsuario) {
            await mostrarPermisosUsuario(idUsuario);
        } else {
            document.getElementById("permisosUsuario").innerHTML = "";
        }
    });
}

async function cargarRoles() {
    const data = await pet("controladores/permisos.php", { funcion: "obtenerroles" });
    if (!data.error) {
        const select = document.getElementById("selectRol");
        select.innerHTML = `<option value="">-- Selecciona un rol --</option>`;
        data.roles.forEach(rol => {
            select.innerHTML += `<option value="${rol.id}">${rol.nombre}</option>`;
        });
    }
}

async function cargarUsuarios() {
    const data = await pet("controladores/permisos.php", { funcion: "obtenerusuarios" });
    if (!data.error) {
        const select = document.getElementById("selectUsuario");
        select.innerHTML = `<option value="">-- Selecciona un usuario --</option>`;
        data.usuarios.forEach(usuario => {
            select.innerHTML += `<option value="${usuario.id}">${usuario.nombre}</option>`;
        });
    }
}

async function mostrarPermisosRol(idRol) {
    const data = await pet("controladores/permisos.php", { funcion: "permisosrol", idRol });
    if (!data.error) {
        renderizarPermisos("permisosRol", data.permisos, data.asignados, async (seleccionados) => {
            const resp = await pet("controladores/permisos.php", {
                funcion: "guardarpermisosrol",
                idRol,
                permisos: seleccionados
            });
            if (!resp.error) {
                Swal.fire("✅ Guardado", "Permisos del rol actualizados", "success");
            } else {
                Swal.fire("❌ Error", "No se pudo guardar", "error");
            }
        });
    }
}

async function mostrarPermisosUsuario(idUsuario) {
    const data = await pet("controladores/permisos.php", { funcion: "permisosusuario", idUsuario });
    if (!data.error) {
        renderizarPermisos("permisosUsuario", data.permisos, data.asignados, async (seleccionados) => {
            const resp = await pet("controladores/permisos.php", {
                funcion: "guardarpermisosusuario",
                idUsuario,
                permisos: seleccionados
            });
            if (!resp.error) {
                Swal.fire("✅ Guardado", "Permisos del usuario actualizados", "success");
            } else {
                Swal.fire("❌ Error", "No se pudo guardar", "error");
            }
        });
    }
}

function renderizarPermisos(containerId, permisos, asignados, onGuardar) {
    const container = document.getElementById(containerId);
    container.innerHTML = "";

    if (!permisos.length) {
        container.innerHTML = `<p class="text-muted">No hay permisos registrados.</p>`;
        return;
    }

    const form = document.createElement("form");
    form.classList.add("mt-3", "row", "g-3");

    permisos.forEach(p => {
        const col = document.createElement("div");
        col.className = "col";

        const card = document.createElement("div");
        card.className = "perm-card form-check form-switch";

        const input = document.createElement("input");
        input.type = "checkbox";
        input.className = "form-check-input";
        input.id = `${containerId}_perm_${p.id}`;
        input.value = p.id;
        if (asignados.map(Number).includes(Number(p.id))) {
            input.checked = true;
        }

        const label = document.createElement("label");
        label.className = "form-check-label fw-semibold ms-2";
        label.htmlFor = input.id;
        label.textContent = p.nombre;

        card.appendChild(label);
        card.appendChild(input);
        col.appendChild(card);
        form.appendChild(col);
    });

    const btn = document.createElement("button");
    btn.type = "submit";
    btn.className = "btn btn-primary mt-3";
    btn.textContent = "Guardar cambios";
    form.appendChild(btn);

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const seleccionados = Array.from(form.querySelectorAll("input[type=checkbox]:checked"))
            .map(chk => parseInt(chk.value));
        onGuardar(seleccionados);
    });

    container.appendChild(form);
}

