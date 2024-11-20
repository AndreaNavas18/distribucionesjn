const SERVER = 'http://localhost/distribucionesjn/';

(function crearClientes() {
    const form = document.getElementById("formCliente");
    
    if (form) { 
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            const clienteData = {};
            formData.forEach((value, key) => {
                clienteData[key] = value.toUpperCase();
            });
        
            fetch(SERVER + "controladores/clientes.php", {
                method: "POST",
                body: JSON.stringify({
                    funcion: "crearcliente",
                    dataCliente: clienteData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Error:", data.error);
                } else {
                    console.log("Éxito:", data.mensaje);
                }
            })
            .catch(error => console.error("Error en la solicitud:", error));
        });
    }
})();

(function obtenerClientes() {
    fetch(SERVER + "controladores/clientes.php", {
        method: "POST",
        body: JSON.stringify({ funcion: "obtenerclientes" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error("Error:", data.error);
        } else {
            const clientes = document.getElementById("clientes");
            if (clientes) {
                for (const cliente of data) {
                    clientes.innerHTML += `
                    <tr>
                        <td>${cliente.nombre}</td>
                        <td>${cliente.razonsocial}</td>
                        <td>${cliente.ubicacion}</td>
                        <td>${cliente.telefono}</td>
                    </tr>
                    `;
                }
            } else {
                console.error("El elemento 'clientes' no existe en el DOM");
            }
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
})();
