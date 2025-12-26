<?php
session_start(); 
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: php/login.php");
    exit;
}
require_once 'php/config.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Gestión de Pedidos - Panel Local</title>

<link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css" />
<link rel="stylesheet" href="assets/css/swiper-bundle.min.css" />
<link rel="stylesheet" href="assets/css/scrollCue.css" />
<link rel="stylesheet" href="assets/css/boxicons.min.css" />
<link rel="stylesheet" href="assets/fonts/flaticon_pozu.css" />
<link rel="stylesheet" href="assets/css/navbar.css" />
<link rel="stylesheet" href="assets/css/footer.css" />
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/responsive.css" />
<link rel="stylesheet" href="assets/css/rtl.css" />
<link rel="stylesheet" href="assets/css/orders.css" /> 

<script src="assets/js/swiper-bundle.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<style>
    body { background-color: gray; } /* Mantengo tu color de fondo original */
    
    #resultados table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
    }
    #resultados th, #resultados td {
        padding: 12px;
        border: 1px solid #dee2e6;
        text-align: center;
    }
    #resultados th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    .image-preview-container {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .image-preview img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>
</head>
<body>

<div class="pedido-container">
    <div class="pedido-header text-center mb-5">
        <h2 id="formTitle">Gestionar Pedido</h2>
        <p class="pedido-subtitle" id="formSubtitle">Crea un nuevo pedido o selecciona uno de la lista para editar.</p>
    </div>

    <div class="card-soft mb-4 p-4">
        <div class="section-title text-center mb-4">Pedidos en Sistema</div>
        
        <div id="resultados" class="mt-3 table-responsive">
            <p class="text-center">Cargando pedidos...</p>
        </div>
    </div>

    <form id="pedidoForm" action="php/createOrder.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="pedidoId" name="pedido_id">

        <div class="card-soft mb-4 p-4">
            <div class="section-title text-center mb-4">Información General</div>
            <div class="mb-3">
                <label for="nombrePedido" class="form-label">Nombre del Pedido</label>
                <input type="text" class="form-control" id="nombrePedido" name="nombre" required>
            </div>

            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Recibida">Recibida</option>
                        <option value="Anticipo recibido">Anticipo Recibido</option>
                        <option value="En produccion">En Producción</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Entregada">Entregada</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fechaEntrega" class="form-label">Fecha Estimada de Entrega</label>
                    <input type="date" class="form-control" id="fechaEntrega" name="fechaEntrega" required>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="costo" class="form-label">Costo Total</label>
                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="anticipo" class="form-label">Anticipo Recibido</label>
                    <input type="number" step="0.01" class="form-control" id="anticipo" name="anticipo" value="0.00">
                </div>
            </div>
        </div> 

        <div class="card-soft mb-4 p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div class="section-title mb-0">Tallas y Colores</div>
                <button type="button" id="addTalla" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Añadir Talla
                </button>
            </div>
            <div id="tallasContainer"></div>
        </div> 

        <div class="card-soft mb-4 p-4">
            <div class="section-title text-center mb-4">Imágenes del Pedido y Paleta</div>

            <div class="mb-4">
                <label for="imagenes" class="form-label">Subir Imágenes del Diseño (múltiples)</label>
                <input type="file" class="form-control" id="imagenes" name="imagenes[]" multiple accept="image/*">
                <div id="imagenesPreview" class="image-preview-container"></div>
            </div>

            <div class="mb-3">
                <label for="paletaColor" class="form-label">Subir Imagen de Paleta de Colores (una)</label>
                <input type="file" class="form-control" id="paletaColor" name="paletaColor" accept="image/*">
                <div id="paletaColorPreview" class="image-preview-container"></div>
            </div>
        </div> 

        <div class="d-grid mt-4">
            <button type="submit" id="submitButton" class="btn btn-primary btn-lg">Guardar Pedido</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="location.reload()">Nuevo Pedido / Limpiar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tallasContainer = document.getElementById('tallasContainer');
    const resultadosDiv = document.getElementById('resultados');
    const pedidoIdField = document.getElementById('pedidoId');
    const formTitle = document.getElementById('formTitle');
    const formSubtitle = document.getElementById('formSubtitle');
    const submitButton = document.getElementById('submitButton');
    const imagenesPreview = document.getElementById('imagenesPreview');
    const paletaColorPreview = document.getElementById('paletaColorPreview');
    const nombrePedidoInput = document.getElementById('nombrePedido');

    // ==========================================
    // 1. CARGA AUTOMÁTICA DE PEDIDOS
    // ==========================================
    function cargarPedidos(nombre = '') {
        fetch('php/getOrderByName.php?nombre=' + encodeURIComponent(nombre))
            .then(res => res.json())
            .then(data => {
                resultadosDiv.innerHTML = '';
                if (data.success && data.pedidos.length > 0) {
                    const table = document.createElement('table');
                    table.innerHTML = `
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Status</th>
                                <th>Inicio</th>
                                <th>Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    `;
                    data.pedidos.forEach(p => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${p.id}</td>
                            <td>${p.nombre}</td>
                            <td>${p.status}</td>
                            <td>${p.fechaInicio}</td>
                            <td>${p.fechaEntrega}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i> Editar</button>
                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${p.id}"><i class="bx bx-trash"></i></button>
                            </td>
                        `;
                        table.querySelector('tbody').appendChild(row);
                    });
                    resultadosDiv.appendChild(table);
                } else {
                    resultadosDiv.innerHTML = '<p class="text-muted">No hay pedidos disponibles.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                resultadosDiv.innerHTML = '<p class="text-danger">Error al cargar la tabla.</p>';
            });
    }

    // Ejecutar al cargar la página
    cargarPedidos();

    // Evento del botón buscar
    document.getElementById('searchByNameButton').addEventListener('click', function() {
        const query = document.getElementById('nombreBusqueda').value;
        cargarPedidos(query);
    });

    // ==========================================
    // 2. LÓGICA DE TALLAS
    // ==========================================
    function addTallaEntry(talla = '', cantidad = 1, color = '#563d7c') {
        const newTallaEntry = document.createElement('div');
        newTallaEntry.className = 'talla-entry d-flex align-items-center gap-2 mb-2';
        newTallaEntry.innerHTML = `
            <input type="text" class="form-control" name="talla[]" placeholder="Talla" value="${talla}">
            <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" value="${cantidad}" min="1">
            <input type="color" class="form-control form-control-color" name="color[]" value="${color}">
            <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>
        `;
        tallasContainer.appendChild(newTallaEntry);
    }

    document.getElementById('addTalla').addEventListener('click', (e) => {
        e.preventDefault();
        addTallaEntry();
    });

    tallasContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-talla')) {
            if (tallasContainer.children.length > 1) {
                e.target.closest('.talla-entry').remove();
            }
        }
    });

    // Iniciar con una fila de talla
    addTallaEntry();

    // ==========================================
    // 3. EDITAR PEDIDO (CARGAR EN FORMULARIO)
    // ==========================================
    resultadosDiv.addEventListener('click', function(e){
        const editBtn = e.target.closest('.edit-btn');
        if(editBtn) {
            const id = editBtn.dataset.id;
            // IMPORTANTE: Asegúrate de que el archivo sea php/editor.php o php/getOrderDetail.php
            fetch(`php/editor.php?id=${id}`) 
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        const p = data.pedido;
                        pedidoIdField.value = p.id;
                        nombrePedidoInput.value = p.nombre;
                        document.getElementById('status').value = p.status;
                        document.getElementById('fechaInicio').value = p.fechaInicio;
                        document.getElementById('fechaEntrega').value = p.fechaEntrega;
                        document.getElementById('costo').value = p.costo;
                        document.getElementById('anticipo').value = p.anticipo;

                        // Cargar tallas
                        tallasContainer.innerHTML = '';
                        if(p.tallas && p.tallas.length > 0) {
                            p.tallas.forEach(t => addTallaEntry(t.talla, t.cantidad, t.color));
                        } else {
                            addTallaEntry();
                        }

                        // Preview Imágenes
                        imagenesPreview.innerHTML = '';
                        (p.imagenes || []).forEach(img => {
                            const div = document.createElement('div');
                            div.className = 'image-preview';
                            div.innerHTML = `<img src="${img}">`;
                            imagenesPreview.appendChild(div);
                        });

                        formTitle.textContent = `Editar Pedido #${p.id}`;
                        formSubtitle.textContent = 'Modifica los datos y presiona Actualizar.';
                        submitButton.textContent = 'Actualizar Pedido';
                        
                        // Scroll suave al formulario
                        window.scrollTo({ top: document.getElementById('pedidoForm').offsetTop - 50, behavior: 'smooth' });
                    }
                });
        }
    });

});
</script>
</body>
</html>