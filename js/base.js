// base.js
export const BASE_URL = window.location.origin + window.location.pathname.split("/").slice(0, 2).join("/");
export const SERVER = `${BASE_URL}/`;

export async function pet(url, data) {
    try {
        const response = await fetch(SERVER + url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error("Error en la solicitud");
        }

        const res = await response.json();

        if (res.sesion === false) {
            window.location.href = `${BASE_URL}/loginview.php`;
        }

        return res;
    } catch (error) {
        console.error("Error en la solicitud:", error);
        return { error: "Error en la solicitud" };
    }
}

export function initDataTable(selector, options = {}) {
    if (typeof $ !== "undefined" && $.fn.DataTable) {
        return $(selector).DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            pagingType: "simple_numbers",
            info: true,
            lengthMenu: [5, 10, 25, 50],
            pageLength: 10,
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron resultados",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "No hay registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    previous: "←",
                    next: "→"
                }
            },
            ...options
        });
    } else {
        console.error("jQuery o DataTables no están cargados.");
    }
}

export function initSelect2(selector, options = {}) {
    if (typeof $ !== "undefined" && $.fn.select2) {
        const select2Options = {
            placeholder: "Selecciona una opción",
            allowClear: true,
            width: '100%',
            ...options
        };

        $(selector).select2(select2Options);

        // Enfocar el campo de búsqueda automáticamente al abrir
        $(selector).on('select2:open', () => {
            setTimeout(() => {
                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 0);
        });
    } else {
        console.error("jQuery o Select2 no están cargados.");
    }
}

export function esperarJQuery(callback) {
    const check = setInterval(() => {
        if (window.$) {
            clearInterval(check);
            callback();
        }
    }, 50);
}

export function formatearMoneda(valor, moneda = 'COP') {
    valor = parseFloat(valor);
    if (isNaN(valor)) return "$0";

    return new Intl.NumberFormat('es-CO', { 
        style: 'currency', 
        currency: moneda,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(valor);
}

export function cargarCabecera() {
    document.addEventListener("DOMContentLoaded", function () {
        fetch("../componentes/cabecera.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            const cabecera = document.getElementById("cabecera");
            if (cabecera) {
                cabecera.innerHTML = data;
                const btnBack = document.getElementById('btnBack');
                const btnMenuToggle = document.getElementById('btnMenuToggle');
                const menuLateral = document.getElementById('menuLateral');

                if (btnBack) {
                    btnBack.addEventListener('click', () => {
                        window.history.back();
                    });
                }

                if (btnMenuToggle && menuLateral) {
                    btnMenuToggle.addEventListener('click', () => {
                        const isVisible = menuLateral.style.display === 'block';
                        menuLateral.style.display = isVisible ? 'none' : 'block';
                    });

                    document.addEventListener('click', function (e) {
                        if (!menuLateral.contains(e.target) && !btnMenuToggle.contains(e.target)) {
                            menuLateral.style.display = 'none';
                        }
                    });
                }
            } else {
                console.error("No se encontró el elemento #cabecera en el DOM.");
            }
        })
        .catch(error => console.error("Error al cargar la cabecera:", error));
    });
}

export async function protegerVista(callback) {
    const res = await pet("autenticacion/verificarSesion.php", {});
    if (res.sesion === false) {
        window.location.href = `${BASE_URL}/loginview.php`;
        return;
    }
    callback();
}

export function logout() {
    fetch(`${SERVER}autenticacion/logout.php`, { method: "POST" })
        .then(() => window.location.href = `${BASE_URL}/loginview.php`);
}

