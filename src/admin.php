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
    <title>Tinta Negra - Gestión de Pedidos y Panel Administrativo</title>

    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css" />
    <link rel="stylesheet" href="assets/css/boxicons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/orders.css" />

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background-color: #f8f9fa; }
        .admin-nav { shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card { border-radius: 12px; border: none; border-start: 5px solid; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .image-preview-container { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; border: 1px dashed #ccc; padding: 10px; border-radius: 8px; background: #fff; min-height: 50px; }
        .image-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .section-title-admin { font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; color: #333; }
        .filter-info { font-size: 0.8rem; color: #666; margin-bottom: 10px; display: block; }
        
        /* Estilos para la tabla tipo tarjeta */
        .table-responsive { background: transparent; padding: 0; box-shadow: none; }
        .table-container { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="bx bxs-dashboard me-2"></i> PANEL DE ADMINISTRADOR
        </a>
        <div class="d-flex gap-2">
            <button onclick="location.reload()" class="btn btn-outline-light btn-sm">
                <i class="bx bx-refresh"></i>
            </button>
            <a href="php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container">
    
    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm p-3 border-warning bg-white">
                <small class="text-muted d-block fw-bold text-uppercase">Producción</small>
                <strong class="h3 mb-0" id="stat-produccion">0</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm p-3 border-primary bg-white">
                <small class="text-muted d-block fw-bold text-uppercase">Entrega Hoy</small>
                <strong class="h3 mb-0 text-primary" id="stat-entrega">0</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm p-3 border-danger bg-white">
                <small class="text-muted d-block fw-bold text-uppercase">Por Cobrar</small>
                <strong class="h3 mb-0 text-danger" id="stat-cobro">$0</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm p-3 border-success bg-white">
                <small class="text-muted d-block fw-bold text-uppercase">Listos</small>
                <strong class="h3 mb-0 text-success" id="stat-finalizados">0</strong>
            </div>
        </div>
    </div>

    <div class="table-container mb-5">
        <div class="row mb-3 align-items-center">
            <div class="col-md-8">
                <h5 class="mb-0 fw-bold">Pedidos Activos</h5>
                <span class="filter-info">* Trabajos pendientes de entrega.</span>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                    <input type="text" id="buscadorNombre" class="form-control" placeholder="Buscar cliente...">
                </div>
            </div>
        </div>
        <div id="resultados">
            <p class="text-center py-4">Cargando pedidos...</p>
        </div>
    </div>

    <form id="pedidoForm" action="php/createOrder.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="pedidoId" name="pedido_id">

        <div class="card mb-4 p-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="section-title-admin text-center" id="formHeader">Nuevo Pedido</div>
            
            <div class="mb-3">
                <label for="nombrePedido" class="form-label fw-bold">Nombre del Pedido / Cliente</label>
                <input type="text" class="form-control" id="nombrePedido" name="nombre" required>
            </div>

            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label fw-bold">Estado</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Recibida">Recibida</option>
                        <option value="Anticipo recibido">Anticipo Recibido</option>
                        <option value="En produccion">En Producción</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Entregada">Entregada</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fechaInicio" class="form-label fw-bold">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="fechaEntrega" class="form-label fw-bold">Fecha Entrega</label>
                    <input type="date" class="form-control" id="fechaEntrega" name="fechaEntrega" required>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="costo" class="form-label fw-bold">Costo Total ($)</label>
                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="anticipo" class="form-label fw-bold">Anticipo Recibido ($)</label>
                    <input type="number" step="0.01" class="form-control" id="anticipo" name="anticipo" value="0.00">
                </div>
            </div>
        </div> 

        <div class="card mb-4 p-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="mb-0 fw-bold">Detalle de Tallas</h5>
                <button type="button" id="addTalla" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Añadir</button>
            </div>
            <div id="tallasContainer"></div>
        </div> 

        <div class="card mb-4 p-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="section-title-admin text-center">Diseño y Color</div>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold">Imágenes del Diseño</label>
                    <input type="file" class="form-control mb-2" id="imagenes" name="imagenes[]" multiple accept="image/*">
                    <div id="imagenesPreview" class="image-preview-container"></div>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold">Paleta de Colores</label>
                    <input type="file" class="form-control mb-2" id="paletaColor" name="paletaColor" accept="image/*">
                    <div id="paletaColorPreview" class="image-preview-container"></div>
                </div>
            </div>
        </div> 

        <div class="d-grid gap-2 mb-5">
            <button type="submit" id="submitButton" class="btn btn-dark btn-lg">Guardar Información</button>
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">Cancelar / Limpiar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tallasContainer = document.getElementById('tallasContainer');
    const resultadosDiv = document.getElementById('resultados');
    const buscador = document.getElementById('buscadorNombre');
    const pedidoIdField = document.getElementById('pedidoId');
    const formHeader = document.getElementById('formHeader');
    const submitButton = document.getElementById('submitButton');
    const imagenesPreview = document.getElementById('imagenesPreview');
    const paletaColorPreview = document.getElementById('paletaColorPreview');

    const inputInicio = document.getElementById('fechaInicio');
    const inputEntrega = document.getElementById('fechaEntrega');

    // Validación de fechas
    inputInicio.addEventListener('change', function() {
        inputEntrega.min = this.value;
        if (inputEntrega.value && inputEntrega.value < this.value) {
            inputEntrega.value = this.value;
        }
    });

    function cargarPedidos(nombre = '') {
        fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(nombre)}`)
            .then(res => res.json())
            .then(data => {
                let stats = { produccion: 0, entregaHoy: 0, porCobrar: 0, listos: 0 };
                
                // CORRECCIÓN DE FECHA LOCAL
                const ahora = new Date();
                const anio = ahora.getFullYear();
                const mes = String(ahora.getMonth() + 1).padStart(2, '0');
                const dia = String(ahora.getDate()).padStart(2, '0');
                const hoy = `${anio}-${mes}-${dia}`; // Formato exacto YYYY-MM-DD local

                if (data.success && data.pedidos.length > 0) {
                    const pedidosFiltrados = data.pedidos.filter(p => p.status !== 'Entregada');

                    if (pedidosFiltrados.length === 0) {
                        resultadosDiv.innerHTML = '<p class="text-center py-4">No hay pedidos pendientes.</p>';
                        actualizarEstadisticas(stats);
                        return;
                    }

                    let html = `<div class="table-responsive">
                        <table class="table table-hover align-middle" style="min-width: 800px; border-collapse: separate; border-spacing: 0 10px;">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th style="width: 30%; padding-left: 15px;">Cliente</th>
                                    <th style="width: 15%;">Entrega</th>
                                    <th style="width: 15%;">Saldo</th>
                                    <th style="width: 20%;">Estado</th>
                                    <th style="width: 20%;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    pedidosFiltrados.forEach(p => {
                        // Cálculos de estadísticas
                        if (p.status === 'En produccion') stats.produccion++;
                        if (p.status === 'Finalizada') stats.listos++;
                        if (p.fechaEntrega === hoy) stats.entregaHoy++;
                        
                        const costo = parseFloat(p.costo || 0);
                        const anticipo = parseFloat(p.anticipo || 0);
                        const saldo = costo - anticipo;
                        if (saldo > 0) stats.porCobrar += saldo;

                        let badgeClass = 'bg-light text-dark border';
                        if (p.status === 'Recibida') badgeClass = 'bg-info text-dark';
                        if (p.status === 'Anticipo recibido') badgeClass = 'bg-primary text-white';
                        if (p.status === 'En produccion') badgeClass = 'bg-warning text-dark';
                        if (p.status === 'Finalizada') badgeClass = 'bg-success text-white';

                        html += `
                        <tr style="background: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border-radius: 10px;">
                            <td style="padding-left: 15px; border-radius: 10px 0 0 10px;">
                                <a href="showOrder.php?id=${p.id}" class="fw-bold text-decoration-none text-dark">${p.nombre}</a>
                            </td>
                            <td class="small text-muted">${p.fechaEntrega}</td>
                            <td class="fw-bold ${saldo > 0 ? 'text-danger' : 'text-success'}">$${saldo.toFixed(2)}</td>
                            <td><span class="badge rounded-pill ${badgeClass}">${p.status.toUpperCase()}</span></td>
                            <td class="text-center" style="border-radius: 0 10px 10px 0;">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-light border edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-light border delete-btn" data-id="${p.id}"><i class="bx bx-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });

                    html += `</tbody></table></div>`;
                    resultadosDiv.innerHTML = html;
                    actualizarEstadisticas(stats);
                } else {
                    resultadosDiv.innerHTML = '<p class="text-center py-4">Sin registros.</p>';
                    actualizarEstadisticas(stats);
                }
            });
    }

    function actualizarEstadisticas(s) {
        document.getElementById('stat-produccion').innerText = s.produccion;
        document.getElementById('stat-entrega').innerText = s.entregaHoy;
        document.getElementById('stat-finalizados').innerText = s.listos;
        document.getElementById('stat-cobro').innerText = '$' + s.porCobrar.toLocaleString('es-MX', {minimumFractionDigits:2});
    }

    function addTallaEntry(talla = '', cantidad = 1, color = '#000000') {
        const div = document.createElement('div');
        div.className = 'talla-entry d-flex align-items-center gap-2 mb-2 bg-light p-2 rounded';
        div.innerHTML = `
            <input type="text" class="form-control" name="talla[]" placeholder="Ej: L" value="${talla}">
            <input type="number" class="form-control" name="cantidad[]" value="${cantidad}" min="1" style="width: 80px;">
            <input type="color" class="form-control form-control-color" name="color[]" value="${color}">
            <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>
        `;
        tallasContainer.appendChild(div);
    }

    document.getElementById('addTalla').addEventListener('click', () => addTallaEntry());
    tallasContainer.addEventListener('click', (e) => {
        if (e.target.closest('.remove-talla')) e.target.closest('.talla-entry').remove();
    });

    // Carga inicial
    cargarPedidos();
    addTallaEntry();
    buscador.addEventListener('input', (e) => cargarPedidos(e.target.value));

    // EDITAR Y ELIMINAR
    document.addEventListener('click', function(e) {
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
                        (p.tallas || []).forEach(t => addTallaEntry(t.talla, t.cantidad, t.color));
                        
                        imagenesPreview.innerHTML = (p.imagenes || []).map(r => `<div class="image-preview"><img src="${r}"></div>`).join('');
                        paletaColorPreview.innerHTML = p.paletaColor ? `<div class="image-preview"><img src="${p.paletaColor}"></div>` : '';

                        formHeader.innerText = "Editando Pedido #" + p.id;
                        submitButton.innerText = "Actualizar Pedido";
                        window.scrollTo({ top: document.getElementById('pedidoForm').offsetTop - 20, behavior: 'smooth' });
                    }
                });
        }

        const btnDelete = e.target.closest('.delete-btn');
        if (btnDelete) {
            const id = btnDelete.getAttribute('data-id');
            if (confirm(`¿Eliminar pedido #${id}?`)) {
                fetch(`php/deleteOrder.php?id=${id}`, { method: 'DELETE' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) { cargarPedidos(buscador.value); if(pedidoIdField.value == id) location.reload(); }
                    });
            }
        }
    });
});
</script>
</body>
</html>