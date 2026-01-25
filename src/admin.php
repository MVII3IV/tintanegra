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
    <title>Tinta Negra - Panel Administrativo</title>

    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css" />
    <link rel="stylesheet" href="assets/css/boxicons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/orders.css" />

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border-radius: 12px; border: none; border-start: 5px solid; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .image-preview-container { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; border: 1px dashed #ccc; padding: 10px; border-radius: 8px; background: #fff; min-height: 50px; align-items: center; justify-content: center; }
        .image-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .section-title-admin { font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; color: #333; }
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
            <button onclick="location.reload()" class="btn btn-outline-light btn-sm"><i class="bx bx-refresh"></i></button>
            <a href="php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container">
    
    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm p-3 border-warning bg-white"><small class="text-muted d-block fw-bold text-uppercase">Producción</small><strong class="h3 mb-0" id="stat-produccion">0</strong></div></div>
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm p-3 border-primary bg-white"><small class="text-muted d-block fw-bold text-uppercase">Entrega Hoy</small><strong class="h3 mb-0 text-primary" id="stat-entrega">0</strong></div></div>
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm p-3 border-danger bg-white"><small class="text-muted d-block fw-bold text-uppercase">Por Cobrar</small><strong class="h3 mb-0 text-danger" id="stat-cobro">$0</strong></div></div>
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm p-3 border-success bg-white"><small class="text-muted d-block fw-bold text-uppercase">Listos</small><strong class="h3 mb-0 text-success" id="stat-finalizados">0</strong></div></div>
    </div>

    <div class="table-container mb-5">
        <div class="row mb-3 align-items-center">
            <div class="col-md-8"><h5 class="mb-0 fw-bold">Pedidos Activos</h5></div>
            <div class="col-md-4">
                <div class="input-group shadow-sm">
                    <input type="text" id="buscadorNombre" class="form-control" placeholder="Buscar cliente...">
                    <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                </div>
            </div>
        </div>
        <div id="resultados"></div>
    </div>

    <form id="pedidoForm" action="php/createOrder.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="pedidoId" name="pedido_id">

        <div class="card mb-4 p-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="section-title-admin text-center" id="formHeader">Nuevo Pedido</div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Nombre del Pedido / Cliente</label>
                    <input type="text" class="form-control" id="nombrePedido" name="nombre" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teléfono (WhatsApp)</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Ej: 6141234567">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Estado</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Recibida">Recibida</option>
                        <option value="Anticipo recibido">Anticipo Recibido</option>
                        <option value="En produccion">En Producción</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Entregada">Entregada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fecha Entrega</label>
                    <input type="date" class="form-control" id="fechaEntrega" name="fechaEntrega" required>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Costo Total ($)</label>
                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Anticipo Recibido ($)</label>
                    <input type="number" step="0.01" class="form-control" id="anticipo" name="anticipo" required>
                </div>
            </div>
        </div> 

        <div class="card mb-4 p-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="mb-0 fw-bold">Detalle de Tallas</h5>
                    <small class="text-muted">Total acumulado: <span id="totalPiezasAdmin" class="fw-bold text-primary">0</span> piezas</small>
                </div>
                <button type="button" id="addTalla" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Añadir</button>
            </div>
            <div id="tallasContainer" class="mb-4"></div>

            <div class="border-top pt-3">
                <label class="form-label fw-bold">Instrucciones Técnicas de Impresión</label>
                <textarea class="form-control" id="instrucciones" name="instrucciones" rows="3" placeholder="Ej: Tinta base agua, a 15cm del cuello..."></textarea>
            </div>
        </div> 

        <div class="card mb-4 p-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="section-title-admin text-center">Diseño y Documentación</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Imágenes del Diseño</label>
                    <input type="file" class="form-control mb-2" id="imagenes" name="imagenes[]" multiple accept="image/*">
                    <div id="imagenesPreview" class="image-preview-container"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Paleta de Colores</label>
                    <input type="file" class="form-control mb-2" id="paletaColor" name="paletaColor" accept="image/*">
                    <div id="paletaColorPreview" class="image-preview-container"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Cotización (PDF/Imagen)</label>
                    <input type="file" class="form-control mb-2" id="cotizacion" name="cotizacion" accept=".pdf,image/*">
                    <div id="cotizacionPreview" class="image-preview-container"></div>
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

    // CONTEO DE PIEZAS
    function calcularTotalPiezas() {
        const inputs = tallasContainer.querySelectorAll('input[name="cantidad[]"]');
        let total = 0;
        inputs.forEach(i => total += parseInt(i.value) || 0);
        document.getElementById('totalPiezasAdmin').innerText = total;
    }

    function addTallaEntry(talla = '', cantidad = 1, color = '#000000') {
        const div = document.createElement('div');
        div.className = 'talla-entry d-flex align-items-center gap-2 mb-2 bg-light p-2 rounded';
        div.innerHTML = `
            <input type="text" class="form-control" name="talla[]" placeholder="Ej: L" value="${talla}">
            <input type="number" class="form-control" name="cantidad[]" value="${cantidad}" min="0" style="width: 80px;">
            <input type="color" class="form-control form-control-color" name="color[]" value="${color}">
            <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>`;
        tallasContainer.appendChild(div);
        div.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotalPiezas);
        calcularTotalPiezas();
    }

    document.getElementById('addTalla').addEventListener('click', () => addTallaEntry());
    tallasContainer.addEventListener('click', (e) => {
        if (e.target.closest('.remove-talla')) { e.target.closest('.talla-entry').remove(); calcularTotalPiezas(); }
    });

    // CARGAR PEDIDOS Y STATS
    function cargarPedidos(nombre = '') {
        fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(nombre)}`)
            .then(res => res.json())
            .then(data => {
                let stats = { produccion: 0, entregaHoy: 0, porCobrar: 0, listos: 0 };
                const hoy = new Date().toISOString().split('T')[0];

                if (data.success && data.pedidos.length > 0) {
                    const filtrados = data.pedidos.filter(p => p.status !== 'Entregada');
                    let html = `<div class="table-responsive"><table class="table table-hover align-middle">
                        <thead><tr class="text-muted small text-uppercase"><th>Cliente</th><th>Entrega</th><th>Saldo</th><th>Estado</th><th class="text-center">Acciones</th></tr></thead><tbody>`;

                    filtrados.forEach(p => {
                        if (p.status === 'En produccion') stats.produccion++;
                        if (p.status === 'Finalizada') stats.listos++;
                        if (p.fechaEntrega === hoy) stats.entregaHoy++;
                        const saldo = parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0);
                        if (saldo > 0) stats.porCobrar += saldo;

                        html += `<tr class="bg-white">
                            <td><a href="showOrder.php?id=${p.id}" class="fw-bold text-dark text-decoration-none">${p.nombre}</a></td>
                            <td class="small">${p.fechaEntrega}</td>
                            <td class="fw-bold ${saldo > 0 ? 'text-danger' : 'text-success'}">$${saldo.toFixed(2)}</td>
                            <td><span class="badge rounded-pill bg-light text-dark border">${p.status}</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light border edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-light border delete-btn" data-id="${p.id}"><i class="bx bx-trash text-danger"></i></button>
                            </td></tr>`;
                    });
                    resultadosDiv.innerHTML = html + "</tbody></table></div>";
                    document.getElementById('stat-produccion').innerText = stats.produccion;
                    document.getElementById('stat-entrega').innerText = stats.entregaHoy;
                    document.getElementById('stat-finalizados').innerText = stats.listos;
                    document.getElementById('stat-cobro').innerText = '$' + stats.porCobrar.toLocaleString('es-MX');
                } else {
                    resultadosDiv.innerHTML = '<p class="text-center py-4">Sin pedidos activos.</p>';
                }
            });
    }

    // EVENTOS DE EDICIÓN
    document.addEventListener('click', function(e) {
        const btnEdit = e.target.closest('.edit-btn');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            fetch(`php/editor.php?id=${id}`).then(res => res.json()).then(data => {
                if (data.success) {
                    const p = data.pedido;
                    pedidoIdField.value = p.id;
                    document.getElementById('nombrePedido').value = p.nombre;
                    document.getElementById('telefono').value = p.telefono || '';
                    document.getElementById('status').value = p.status;
                    document.getElementById('fechaInicio').value = p.fechaInicio;
                    document.getElementById('fechaEntrega').value = p.fechaEntrega;
                    document.getElementById('costo').value = p.costo;
                    document.getElementById('anticipo').value = p.anticipo;
                    document.getElementById('instrucciones').value = p.instrucciones || '';

                    tallasContainer.innerHTML = '';
                    (p.tallas || []).forEach(t => addTallaEntry(t.talla, t.cantidad, t.color));
                    
                    document.getElementById('imagenesPreview').innerHTML = (p.imagenes || []).map(r => `<div class="image-preview"><img src="${r}"></div>`).join('');
                    document.getElementById('paletaColorPreview').innerHTML = p.paletaColor ? `<div class="image-preview"><img src="${p.paletaColor}"></div>` : '';
                    document.getElementById('cotizacionPreview').innerHTML = p.cotizacion ? `<a href="${p.cotizacion}" target="_blank" class="btn btn-sm btn-outline-primary w-100">Ver Cotización</a>` : '';

                    formHeader.innerText = "Editando Pedido #" + p.id;
                    submitButton.innerText = "Actualizar Pedido";
                    window.scrollTo({ top: document.getElementById('pedidoForm').offsetTop - 20, behavior: 'smooth' });
                }
            });
        }
        
        const btnDel = e.target.closest('.delete-btn');
        if (btnDel && confirm('¿Eliminar pedido?')) {
            fetch(`php/deleteOrder.php?id=${btnDel.getAttribute('data-id')}`, { method: 'DELETE' })
            .then(res => res.json()).then(data => { if (data.success) cargarPedidos(); });
        }
    });

    cargarPedidos();
    addTallaEntry();
    buscador.addEventListener('input', (e) => cargarPedidos(e.target.value));
});
</script>
</body>
</html>