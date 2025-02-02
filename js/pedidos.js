const SERVER = 'http://localhost/distribucionesjn/';

document.addEventListener("DOMContentLoaded", function() {
    const vista = document.body.id;
    if (vista === "tomarPedido") {
        console.log("Estoy en tomarPedido");
        listarProductos();
        obtenerClientes();
        agregarProducto();
        guardarPedido();
    } else if (vista === "historialPedidos") {
        historialPedidos();
    } else if (vista === "ordenCompra") {
        verOrdenCompra();
    }
});

function listarProductos() {
    const selectProductos = document.getElementById("slcProductos");
    if(selectProductos) {
        $(document).ready(function() {
            $('#slcProductos').select2({
                placeholder: 'Escribe para buscar un producto...',
                minimumInputLength: 0,
                ajax: {
                    url: SERVER + 'controladores/productos.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    data: function(params) {
                        return JSON.stringify({
                            funcion: 'buscarproductos', 
                            query: params.term || ''
                        });
                    },
                    processResults: function(data) {
                        console.log(data);
                        if (data.error) {
                            console.error(data.error);
                            return { results: [] };
                        }
                        return {
                            results: data.map(function(producto) {
                                return {
                                    id: producto.id,
                                    text: producto.nombre
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        });
    }
}

function obtenerClientes() {
    fetch(SERVER + "controladores/clientes.php", {
        method: "POST",
        body: JSON.stringify({ funcion: "obtenerclientes" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error("Error:", data.error);
        } else {
            const clientes = document.getElementById("slcClientes");
            if (clientes) {
                for (const cliente of data) {
                    clientes.innerHTML += `
                    <option value="${cliente.id}">${cliente.nombre}</option>
                    `;
                }
            } else {
                console.error("El elemento 'clientes' no existe en el DOM");
            }
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
}

function formatearMoneda(valor) {
    valor = parseFloat(valor);
    
    if (isNaN(valor)) return "$0";

    return new Intl.NumberFormat('es-CO', { 
        style: 'currency', 
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(valor);
}

function agregarProducto() {
        const btnAgregar = document.getElementById("btnAgregar");
        const btnCantidad = document.getElementById("btnCantidad");
        const selectProductos = document.getElementById("slcProductos");
        const inputCantidad = document.getElementById("cantidad");
        const tablaPedidoBody = document.querySelector("#tablaPedido tbody");
        const tdIdProducto = document.getElementById("codigoProd");
        tdIdProducto.style.display = "none";
        const infoProductos = [];

        fetch(SERVER + "controladores/productos.php", {
            method: "POST",
            body: JSON.stringify({ funcion: "obtenerproductos" })
        })
        .then(response => response.json())
        .then(data => {
            if (data) {
                infoProductos.push(...data);
            }
        })
        .catch(error => console.error("Error en la solicitud:", error));
    
        btnAgregar.addEventListener("click", function () {
            console.log("dentro de agregar producto, di click");
            const productoSeleccionado = selectProductos.options[selectProductos.selectedIndex];
            const cantidad = inputCantidad.value;
            
            if (productoSeleccionado.value && cantidad > 0) {
                const nombreProducto = productoSeleccionado.text;
                const idProducto = productoSeleccionado.value;
                const precioProducto = infoProductos.find(producto => producto.id == productoSeleccionado.value).precioventa;
    
                const fila = document.createElement("tr");
    
                const celdaCantidad = document.createElement("td");
                celdaCantidad.textContent = cantidad;
                fila.appendChild(celdaCantidad);

                const celdaIdProducto = document.createElement("td");
                celdaIdProducto.textContent = idProducto;
                celdaIdProducto.style.display = "none";
                fila.appendChild(celdaIdProducto);
    
                const celdaProducto = document.createElement("td");
                celdaProducto.textContent = nombreProducto;
                fila.appendChild(celdaProducto);

                const celdaPrecio = document.createElement("td");
                celdaPrecio.textContent = formatearMoneda(precioProducto);
                fila.appendChild(celdaPrecio);

                const celdaSubTotal = document.createElement("td");
                const subtotal = precioProducto * cantidad;
                celdaSubTotal.textContent = formatearMoneda(subtotal);
                fila.appendChild(celdaSubTotal);
    
                // Celda de acciones con botón para eliminar la fila
                const celdaAcciones = document.createElement("td");
                const botonEliminar = document.createElement("button");
                botonEliminar.textContent = "Eliminar";
                botonEliminar.classList.add("btn", "btn-danger");
                botonEliminar.addEventListener("click", function () {
                    tablaPedidoBody.removeChild(fila);
                });
                celdaAcciones.appendChild(botonEliminar);
                fila.appendChild(celdaAcciones);
    
                tablaPedidoBody.appendChild(fila);
                inputCantidad.value = "";
                $(selectProductos).val(null).trigger("change");

                // Calcular total
                let total = 0;
                const filas = tablaPedidoBody.querySelectorAll("tr");
                filas.forEach((fila) => {
                    const textoSubtotal = fila.querySelector("td:nth-child(5)").textContent;
                    const subTotal = textoSubtotal.replace(/[\s$]/g, '').replace(/\./g, '').replace(',', '.');
                    total += parseFloat(subTotal);
                });
                document.getElementById("totalPedido").value = formatearMoneda(total);
                
            } else {
                alert("Por favor, seleccione un producto y una cantidad válida.");
            }
        });

        btnCantidad.addEventListener("click", function () {
            //inserto un 12 en el input cantidad
            inputCantidad.value = 12;
        });
}

function guardarPedido() {
        const btnGuardar = document.getElementById("btnGuardarPedido");
        const tablaPedidoBody = document.querySelector("#tablaPedido tbody");
        const filas = tablaPedidoBody.querySelectorAll("tr");
        const cliente = document.getElementById("slcClientes").value;
        const observacion = document.getElementById("observacion").value;
    
        const productos = [];
    
        filas.forEach((fila) => {
            const cantidad = fila.querySelector("td:nth-child(1)").textContent;
            const idProducto = fila.querySelector("td:nth-child(2)").textContent;
    
            productos.push({
                id: parseInt(idProducto, 10),
                cantidad: parseInt(cantidad, 10)
            });
        });

        btnGuardar.addEventListener("click", function () {
            if (productos.length > 0) {
                if (!cliente || cliente === "elegir") {
                    alert("Por favor, seleccione un cliente.");
                    return;
                } else {
                    fetch(SERVER + "controladores/pedidos.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            funcion: "guardarpedido",
                            productos: productos,
                            cliente: cliente,
                            observacion: observacion
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.mensaje) {
                            alert(data.mensaje);
                            tablaPedidoBody.innerHTML = "";
                        } else {
                            alert("Hubo un error al guardar el pedido");
                        }
                    })
                    .catch(error => console.error("Error:", error));
                }
            } else {
                alert("No hay productos en el pedido para guardar.");
            }
        });
}

function historialPedidos() {
    fetch(SERVER + "controladores/pedidos.php", {
        method: "POST",
        body: JSON.stringify({ funcion: "obtenerpedidos" })
    })
    .then(response => response.json())
    .then(data => {
        if(data.pedidos){
            const pedidosArray = JSON.parse(data.pedidos); 
            const pedidos = document.getElementById("pedidos");
            if(pedidos) {
                for (const pedido of pedidosArray) {
                    pedidos.innerHTML += `
                    <tr>
                        <td>${pedido.fecha}</td>
                        <td>${pedido.cliente}</td>
                        <td>${pedido.total}</td>
                        <td>${pedido.observacion}</td>
                    </tr>
                    `;
                }
            }
        } else {
            console.error("Error:", data.error);
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
}

function verOrdenCompra() {
    document.getElementById("btnFiltrar").addEventListener("click", () => {
        const form = document.getElementById("formFiltro");
        const formData = new FormData(form);

        const formDataObj = {};
        formData.forEach((value, key) => {
            formDataObj[key] = value;
        });

        fetch(SERVER + "controladores/pedidos.php", {
            method: "POST",
            body: JSON.stringify({ funcion: "verordencompra", datosForm: formDataObj })
        })
            .then(response => response.json())
            .then(data => {
                //creo dinamicamente la tabla con el objeto que me llega de cada producto y su cantidad
                if(data.orden){
                    const ordenArr = JSON.parse(data.orden); 
                    const tablaOrdenCompra = document.getElementById("ordenesCompra");
                    if(tablaOrdenCompra) {
                        tablaOrdenCompra.innerHTML = "";
                        for (const orden of ordenArr) {
                            tablaOrdenCompra.innerHTML += `
                            <tr>
                                <td>${orden.nombre}</td>
                                <td>${orden.cantidad}</td>
                                <td>${orden.costo}</td>
                                <td>${orden.proveedor}</td>
                            </tr>
                            `;
                        }
                    }
                }
            })
            .catch(error => console.error("Error en la solicitud:", error));
    });
}