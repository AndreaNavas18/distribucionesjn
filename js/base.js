// base.js
export const SERVER = 'http://localhost/distribucionesjn/';

export async function pet(url, data) {
    try {
        const response = await fetch(SERVER + url, {
            method: "POST",
            body: JSON.stringify(data),
            headers: {
                "Content-Type": "application/json"
            }
        });

        return await response.json();
    } catch (error) {
        console.error("Error en la solicitud:", error);
        return { error: "Error en la solicitud" };
    }
}

export function initDataTable(selector, options = {}) {
    if (typeof $ !== "undefined" && $.fn.DataTable) {
        $(selector).DataTable({
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
        $(selector).select2({
            placeholder: "Selecciona una opción",
            allowClear: true,
            width: '100%',
            ...options
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
        fetch("../componentes/cabecera.html")
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
                } else {
                    console.error("No se encontró el elemento #cabecera en el DOM.");
                }
            })
            .catch(error => console.error("Error al cargar la cabecera:", error));
    });
}


