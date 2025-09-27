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
<title>Crear / Editar Pedido</title>

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
    /* Estilos para la tabla de resultados */
    #resultados table {
        width: 100%;
        border-collapse: collapse;
    }
    body{
        background-color: gray;
    }
    #resultados th, #resultados td {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
    }
    #resultados th {
        background-color: #f8f9fa;
    }
    #resultados td button {
        margin-right: 5px;
    }
</style>
</head>
<body>

<div class="pedido-container">
    <div class="pedido-header text-center mb-5">
        <h2 id="formTitle">Gestionar Pedido</h2>
        <p class="pedido-subtitle" id="formSubtitle">Crea un nuevo pedido o busca uno existente para editar.</p>
    </div>

    <!-- 🔍 Buscador por NOMBRE -->
    <div class="card-soft mb-4 p-4">
        <div class="section-title text-center mb-4">Buscar Pedido por Nombre</div>
        <div class="input-group mb-3">
            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Introduce el nombre a buscar">
            <button class="btn btn-primary" type="submit" id="searchByNameButton">
                <i class="bx bx-search"></i> Buscar
            </button>
        </div>
        <div id="resultados" class="mt-3"></div>
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
                <small class="form-text text-muted">Max 2MB por archivo</small>
                <div id="imagenesPreview" class="image-preview-container"></div>
            </div>

            <div class="mb-3">
                <label for="paletaColor" class="form-label">Subir Imagen de Paleta de Colores (una)</label>
                <input type="file" class="form-control" id="paletaColor" name="paletaColor" accept="image/*">
                <small class="form-text text-muted">Max 2MB por archivo</small>
                <div id="paletaColorPreview" class="image-preview-container"></div>
            </div>
        </div> 

        <div class="d-grid mt-4">
            <button type="submit" id="submitButton" class="btn btn-primary btn-lg">Guardar Pedido</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tallasContainer = document.getElementById('tallasContainer');
    const pedidoIdField = document.getElementById('pedidoId');
    const formTitle = document.getElementById('formTitle');
    const formSubtitle = document.getElementById('formSubtitle');
    const submitButton = document.getElementById('submitButton');
    const imagenesPreview = document.getElementById('imagenesPreview');
    const paletaColorPreview = document.getElementById('paletaColorPreview');
    const nombrePedidoInput = document.getElementById('nombrePedido');

    // ======================
    // Función para añadir tallas
    // ======================
    function addTallaEntry(talla = '', cantidad = 1, color = '#563d7c') {
        const newTallaEntry = document.createElement('div');
        newTallaEntry.className = 'talla-entry d-flex align-items-center gap-2 mb-2';
        newTallaEntry.innerHTML = `
            <input type="text" class="form-control" name="talla[]" placeholder="Talla (Ej: S, M, L)" value="${talla}">
            <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" value="${cantidad}" min="1">
            <input type="color" class="form-control form-control-color" name="color[]" value="${color}" title="Elige tu color">
            <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>
        `;
        tallasContainer.appendChild(newTallaEntry);
    }

    // ======================
    // Evento para añadir talla
    // ======================
    document.getElementById('addTalla').addEventListener('click', function(e){
        e.preventDefault();
        addTallaEntry();
    });

    // ======================
    // Eliminar talla
    // ======================
    tallasContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-talla') || e.target.closest('.remove-talla')) {
            const button = e.target.classList.contains('remove-talla') ? e.target : e.target.closest('.remove-talla');
            if (tallasContainer.children.length > 1) {
                button.closest('.talla-entry').remove();
            } else {
                alert('Debe haber al menos una talla.');
            }
        }
    });

    // ======================
    // Buscar por nombre
    // ======================
    document.getElementById('searchByNameButton').addEventListener('click', function(e) {
        e.preventDefault();
        const nombre = document.getElementById('nombre').value.trim();
        const resultadosDiv = document.getElementById('resultados');
        resultadosDiv.innerHTML = 'Buscando...';

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
                                <button class="btn btn-sm btn-primary edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i> Editar</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${p.id}"><i class="bx bx-trash"></i> Eliminar</button>
                            </td>
                        `;
                        table.querySelector('tbody').appendChild(row);
                    });
                    resultadosDiv.appendChild(table);
                } else {
                    resultadosDiv.innerHTML = '<p class="text-muted">No se encontraron pedidos.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                resultadosDiv.innerHTML = '<p class="text-danger">Ocurrió un error al buscar.</p>';
            });
    });

    // ======================
    // Editar pedido desde la tabla
    // ======================
    document.getElementById('resultados').addEventListener('click', function(e){
        if(e.target.closest('.edit-btn')) {
            const id = e.target.closest('.edit-btn').dataset.id;
            fetch(`php/getOrderById?id=${id}`)
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
                        const tallas = p.tallas || [];
                        if(tallas.length > 0) tallas.forEach(t => addTallaEntry(t.talla, t.cantidad, t.color));
                        else addTallaEntry();

                        // Cargar imágenes
                        imagenesPreview.innerHTML = '';
                        (p.imagenes || []).forEach(img => {
                            const div = document.createElement('div');
                            div.className = 'image-preview';
                            div.innerHTML = `<img src="${img}" alt="Imagen previa">`;
                            imagenesPreview.appendChild(div);
                        });

                        // Cargar paleta
                        paletaColorPreview.innerHTML = '';
                        if(p.paletaColor) {
                            const div = document.createElement('div');
                            div.className = 'image-preview';
                            div.innerHTML = `<img src="${p.paletaColor}" alt="Paleta de colores">`;
                            paletaColorPreview.appendChild(div);
                        }

                        formTitle.textContent = `Editar Pedido #${p.id}`;
                        formSubtitle.textContent = 'Edita los detalles de este pedido existente.';
                        submitButton.textContent = 'Actualizar Pedido';
                    }
                });
        }
    });

});




</script>
</body>
</html>
