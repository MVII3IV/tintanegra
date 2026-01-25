<?php
session_start(); 
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once 'php/config.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Pedidos - Panel Administrativo</title>

    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css" />
    <link rel="stylesheet" href="assets/css/boxicons.min.css" />
    <link rel="stylesheet" href="assets/css/footer.css" />
    <link rel="stylesheet" href="assets/css/navbar.css" />
    <link rel="stylesheet" href="assets/css/orders.css" />
    <link rel="stylesheet" href="assets/css/responsive.css" />
    <link rel="stylesheet" href="assets/css/rtl.css" />
    <link rel="stylesheet" href="assets/css/scrollCue.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css" />

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background-color: #f4f4f4; }
        .image-preview-container { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; border: 1px dashed #ccc; padding: 10px; border-radius: 8px; background: #fff; min-height: 50px; }
        .image-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .table-responsive { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .section-title-admin { font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; color: #333; }
        .action-btns { display: flex; gap: 5px; justify-content: center; }
        /* Badge para resaltar que estamos en vista filtrada */
        .filter-info { font-size: 0.8rem; color: #666; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="pedido-header text-center mb-5">
        <h2 id="formTitle">Panel de Gestión de Pedidos</h2>
        <p class="pedido-subtitle" id="formSubtitle">Mostrando solo pedidos pendientes de entrega.</p>
    </div>

    <div class="card mb-4 p-4 shadow-sm border-0">
        <div class="row mb-3 align-items-center">
            <div class="col-md-8">
                <h5 class="mb-0">Pedidos Activos</h5>
                <span class="filter-info">* Los pedidos con estado "Entregada" están ocultos.</span>
            </div>
            <div class="col-md-4">
                <input type="text" id="buscadorNombre" class="form-control" placeholder="Buscar cliente activo...">
            </div>
        </div>
        <div id="resultados" class="table-responsive">
            <p class="text-center">Cargando pedidos...</p>
        </div>
    </div>

    <form id="pedidoForm" action="php/createOrder.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="pedidoId" name="pedido_id">

        <div class="card mb-4 p-4 shadow-sm border-0">
            <div class="section-title-admin text-center">Datos Generales</div>
            <div class="mb-3">
                <label for="nombrePedido" class="form-label">Nombre del Pedido / Cliente</label>
                <input type="text" class="form-control" id="nombrePedido" name="nombre" required>
            </div>

            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Estado del Pedido</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Recibida">Recibida</option>
                        <option value="Anticipo recibido">Anticipo Recibido</option>
                        <option value="En produccion">En Producción</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Entregada">Entregada</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fechaEntrega" class="form-label">Fecha Entrega</label>
                    <input type="date" class="form-control" id="fechaEntrega" name="fechaEntrega" required>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="costo" class="form-label">Costo Total ($)</label>
                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="anticipo" class="form-label">Anticipo Recibido ($)</label>
                    <input type="number" step="0.01" class="form-control" id="anticipo" name="anticipo" value="0.00">
                </div>
            </div>
        </div> 

        <div class="card mb-4 p-4 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="mb-0">Configuración de Tallas</h5>
                <button type="button" id="addTalla" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Añadir Talla</button>
            </div>
            <div id="tallasContainer"></div>
        </div> 

        <div class="card mb-4 p-4 shadow-sm border-0">
            <div class="section-title-admin text-center">Evidencia Visual y Colores</div>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Imágenes del Diseño</label>
                    <input type="file" class="form-control mb-2" id="imagenes" name="imagenes[]" multiple accept="image/*">
                    <div id="imagenesPreview" class="image-preview-container"></div>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Paleta de Colores</label>
                    <input type="file" class="form-control mb-2" id="paletaColor" name="paletaColor" accept="image/*">
                    <div id="paletaColorPreview" class="image-preview-container"></div>
                </div>
            </div>
        </div> 

        <div class="d-grid gap-2 mb-5">
            <button type="submit" id="submitButton" class="btn btn-dark btn-lg">Guardar Información</button>
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">Cancelar / Nuevo Pedido</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tallasContainer = document.getElementById('tallasContainer');
    const resultadosDiv = document.getElementById('resultados');
    const buscador = document.getElementById('buscadorNombre');
    const pedidoIdField = document.getElementById('pedidoId');
    const formTitle = document.getElementById('formTitle');
    const submitButton = document.getElementById('submitButton');
    const imagenesPreview = document.getElementById('imagenesPreview');
    const paletaColorPreview = document.getElementById('paletaColorPreview');

    // 1. CARGAR PEDIDOS (CON FILTRO DE ESTATUS)
    ///showOrder?id=canguro-volador-no-indentificado-2025122602&updated=true
    function cargarPedidos(nombre = '') {
        fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(nombre)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.pedidos.length > 0) {
                    const pedidosFiltrados = data.pedidos.filter(p => p.status !== 'Entregada');

                    if (pedidosFiltrados.length === 0) {
                        resultadosDiv.innerHTML = '<p class="text-center">No hay pedidos pendientes.</p>';
                        return;
                    }

                    // Eliminamos <th>ID</th> del encabezado
                    let html = `<table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Cliente / Pedido</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>`;

                    pedidosFiltrados.forEach(p => {
                        const linkDetalle = `showOrder?id=${p.id}`; 
                        
                        // Eliminamos el <td> con el ID
                        html += `<tr>
                            <td>
                                <a href="${linkDetalle}" style="text-decoration: none; color: #0d6efd; font-weight: 500;">
                                    ${p.nombre}
                                </a>
                            </td>
                            <td><span class="badge bg-secondary">${p.status}</span></td>
                            <td class="action-btns text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="${p.id}"><i class="bx bx-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                    html += `</tbody></table>`;
                    resultadosDiv.innerHTML = html;
                } else {
                    resultadosDiv.innerHTML = '<p class="text-center">No se encontraron registros.</p>';
                }
            });
    }

    cargarPedidos();

    // Evento buscador
    buscador.addEventListener('input', (e) => cargarPedidos(e.target.value));

    // 2. TALLAS
    function addTallaEntry(talla = '', cantidad = 1, color = '#000000') {
        const div = document.createElement('div');
        div.className = 'talla-entry d-flex align-items-center gap-2 mb-2 bg-light p-2 rounded';
        div.innerHTML = `
            <input type="text" class="form-control" name="talla[]" placeholder="Talla" value="${talla}">
            <input type="number" class="form-control" name="cantidad[]" value="${cantidad}" min="1" style="width: 80px;">
            <input type="color" class="form-control form-control-color" name="color[]" value="${color}">
            <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-x"></i></button>
        `;
        tallasContainer.appendChild(div);
    }
    document.getElementById('addTalla').addEventListener('click', () => addTallaEntry());
    tallasContainer.addEventListener('click', (e) => {
        if (e.target.closest('.remove-talla')) e.target.closest('.talla-entry').remove();
    });
    addTallaEntry();

    // 3. EDITAR Y ELIMINAR
    document.addEventListener('click', function(e) {
        
        // EDITAR
        const btnEdit = e.target.closest('.edit-btn');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            fetch(`php/editor.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const p = data.pedido;
                        pedidoIdField.value = p.id;
                        document.getElementById('nombrePedido').value = p.nombre;
                        document.getElementById('status').value = p.status;
                        document.getElementById('fechaInicio').value = p.fechaInicio;
                        document.getElementById('fechaEntrega').value = p.fechaEntrega;
                        document.getElementById('costo').value = p.costo;
                        document.getElementById('anticipo').value = p.anticipo;

                        tallasContainer.innerHTML = '';
                        if (p.tallas && p.tallas.length > 0) {
                            p.tallas.forEach(t => addTallaEntry(t.talla, t.cantidad, t.color));
                        } else { addTallaEntry(); }

                        imagenesPreview.innerHTML = '';
                        (p.imagenes || []).forEach(ruta => {
                            const div = document.createElement('div');
                            div.className = 'image-preview';
                            div.innerHTML = `<img src="${ruta}">`;
                            imagenesPreview.appendChild(div);
                        });

                        paletaColorPreview.innerHTML = '';
                        if (p.paletaColor) {
                            const div = document.createElement('div');
                            div.className = 'image-preview';
                            div.innerHTML = `<img src="${p.paletaColor}">`;
                            paletaColorPreview.appendChild(div);
                        }

                        formTitle.innerText = "Editando Pedido #" + p.id;
                        submitButton.innerText = "Actualizar Pedido";
                        window.scrollTo({ top: document.getElementById('pedidoForm').offsetTop - 50, behavior: 'smooth' });
                    }
                });
        }

        // ELIMINAR
        const btnDelete = e.target.closest('.delete-btn');
        if (btnDelete) {
            const id = btnDelete.getAttribute('data-id');
            if (confirm(`¿Seguro que deseas eliminar el pedido #${id}?`)) {
                fetch(`php/deleteOrder.php?id=${id}`, { method: 'DELETE' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert("Pedido borrado.");
                            cargarPedidos(buscador.value);
                            if (pedidoIdField.value == id) location.reload();
                        } else {
                            alert("Error: " + data.message);
                        }
                    });
            }
        }
    });
});
</script>
</body>
</html>