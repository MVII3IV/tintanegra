<?php
session_start(); 
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once 'php/config.php'; 

// Cargamos el catálogo para los selectores del formulario
$stmtCat = $pdo->query("SELECT * FROM catalogo_prendas WHERE activo = 1 ORDER BY tipo_prenda ASC");
$prendasCatalogo = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
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
        
        .d-flex.justify-content-center.gap-1 .btn, 
        .d-flex.justify-content-center.gap-2 .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
        }

        .bxl-whatsapp { transform: translateY(1px); font-size: 1.1rem; }
        .modal-header .btn-close { margin: 0; position: absolute; left: 15px; top: 20px; }
        .modal-title { width: 100%; text-align: center; padding-right: 0; }
        .badge-catalogo { font-size: 0.75rem; background: #e9ecef; color: #495057; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="bx bxs-dashboard me-2"></i> PANEL DE ADMINISTRADOR
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalCatalogo">
                <i class="bx bx-closet"></i> Catálogo
            </button>
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
            <div class="col-md-5">
                <h5 class="mb-0 fw-bold">Pedidos Activos</h5>
            </div>
            <div class="col-md-7">
                <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bx bx-search text-primary"></i>
                    </span>
                    <input type="text" id="buscadorNombre" class="form-control border-start-0 border-end-0 ps-0" placeholder="Buscar cliente...">
                    <a href="orders.php" class="btn btn-primary d-flex align-items-center gap-1 border-0">
                        <i class="bx bx-list-ul fs-5"></i> <span class="d-none d-sm-inline">Ver Todas</span>
                    </a>
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
                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Ej: 614-123-4567">
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
                    <h5 class="mb-0 fw-bold">Detalle de Prendas y Tallas</h5>
                    <small class="text-muted">Total acumulado: <span id="totalPiezasAdmin" class="fw-bold text-primary">0</span> piezas</small>
                </div>
                <button type="button" id="addTalla" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Añadir Prenda</button>
            </div>
            <div id="tallasContainer" class="mb-4"></div>

            <div class="border-top pt-3">
                <label class="form-label fw-bold">Instrucciones Técnicas de Impresión</label>
                <textarea class="form-control" id="instrucciones" name="instrucciones" rows="3" placeholder="Ej: Tinta base agua..."></textarea>
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

<div class="modal fade" id="modalCatalogo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title text-white">Catálogo de Prendas Base</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formNuevaPrenda" class="row g-3 mb-4 pb-4 border-bottom">
                    <div class="col-md-2">
                        <label class="small fw-bold">Tipo</label>
                        <input type="text" class="form-control form-control-sm" name="tipo_prenda" placeholder="Playera" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Marca</label>
                        <input type="text" class="form-control form-control-sm" name="marca" placeholder="Yazbek" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Modelo</label>
                        <input type="text" class="form-control form-control-sm" name="modelo" placeholder="C0300" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Género</label>
                        <select class="form-select form-select-sm" name="genero">
                            <option value="Unisex">Unisex</option>
                            <option value="Dama">Dama</option>
                            <option value="Niño">Niño</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Costo Base</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="costo_base" value="0.00">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark btn-sm w-100">Guardar</button>
                    </div>
                </form>
                
                <div class="table-responsive" style="max-height: 350px;">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr><th>Prenda</th><th>Marca</th><th>Modelo</th><th>Género</th><th>Costo</th><th class="text-end">Acciones</th></tr>
                        </thead>
                        <tbody id="listaCatalogo">
                            <?php 
                            $stmtC = $pdo->query("SELECT * FROM catalogo_prendas WHERE activo = 1 ORDER BY id DESC");
                            while($r = $stmtC->fetch()): ?>
                            <tr id="prenda-<?= $r['id'] ?>">
                                <td><?= htmlspecialchars($r['tipo_prenda']) ?></td>
                                <td><?= htmlspecialchars($r['marca']) ?></td>
                                <td><?= htmlspecialchars($r['modelo']) ?></td>
                                <td><span class="badge-catalogo"><?= $r['genero'] ?></span></td>
                                <td>$<?= number_format($r['costo_base'], 2) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-eliminar-prenda" data-id="<?= $r['id'] ?>">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="waModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-success text-white border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bx bxl-whatsapp me-2"></i> Previsualizar Notificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Enviar a:</label>
                    <div id="wa-destinatario" class="fw-bold fs-5"></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Mensaje:</label>
                    <textarea id="wa-mensaje-pre" class="form-control bg-light rounded-3 border" style="font-size: 0.95rem; height: 150px; resize: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="wa-confirmar-link" target="_blank" class="btn btn-success px-4">Confirmar y Enviar</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4"><i class="bx bx-error-circle text-danger" style="font-size: 80px;"></i></div>
                <h4 class="fw-bold mb-3">¿Confirmar eliminación?</h4>
                <p class="text-muted mb-4">Esta acción eliminará permanentemente el pedido y sus archivos.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 text-white">Eliminar Ahora</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });
    const catalogoPrendas = <?= json_encode($prendasCatalogo) ?>;

    /**
     * GESTIÓN DE PEDIDOS Y ESTADÍSTICAS
     */
    function generarLinkWhatsApp(p) {
        if (!p.telefono) return '#'; 
        const telLimpio = p.telefono.replace(/\D/g, '');
        const host = window.location.hostname === 'localhost' ? 'http://localhost:8080' : 'https://www.tintanegra.mx';
        const urlPedido = `${host}/showOrder.php?id=${p.id}`;
        const saldo = (p.status === 'Entregada') ? 0 : (parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0));
        
        const mensaje = `Hola *${p.nombre}*, te saludamos de Tinta Negra.\n\nTu pedido *${p.id}* ha cambiado a: *${p.status.toUpperCase()}*.\n\nSaldo: ${fmtMoney(saldo)}.\nDetalles: ${urlPedido}`;
        return `https://wa.me/52${telLimpio}?text=${encodeURIComponent(mensaje)}`;
    }

    function cargarPedidos(nombre = '') {
        fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(nombre)}`)
            .then(res => res.json())
            .then(data => {
                let stats = { produccion: 0, entregaHoy: 0, porCobrar: 0, listos: 0 };
                const hoy = new Date().toISOString().split('T')[0];
                const resultadosDiv = document.getElementById('resultados');

                if (data.success && data.pedidos.length > 0) {
                    let html = `<div class="table-responsive"><table class="table table-hover align-middle">
                        <thead><tr class="text-muted small text-uppercase"><th>Cliente</th><th>Entrega</th><th>Saldo</th><th>Estado</th><th class="text-center">Acciones</th></tr></thead><tbody>`;
                    
                    data.pedidos.filter(p => p.status !== 'Entregada').forEach(p => {
                        if (p.status === 'En produccion') stats.produccion++;
                        if (p.status === 'Finalizada') stats.listos++;
                        if (p.fechaEntrega === hoy) stats.entregaHoy++;
                        const saldo = parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0);
                        if (saldo > 0) stats.porCobrar += saldo;

                        html += `<tr class="bg-white">
                            <td><span class="fw-bold">${p.nombre}</span></td>
                            <td class="small">${p.fechaEntrega}</td>
                            <td class="fw-bold ${saldo > 0 ? 'text-warning' : 'text-success'}">$${saldo.toFixed(2)}</td>
                            <td><span class="badge rounded-pill bg-light text-dark border">${p.status}</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-success border-0 btn-wa-preview" data-nombre="${p.nombre}" data-tel="${p.telefono}" data-link="${generarLinkWhatsApp(p)}"><i class="bx bxl-whatsapp"></i></button>
                                    <button class="btn btn-sm btn-light border edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                    <button class="btn btn-sm btn-light border delete-btn" data-id="${p.id}"><i class="bx bx-trash text-danger"></i></button>
                                </div>
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

    /**
     * GESTIÓN DE TALLAS Y PRENDAS CON COPIA AUTOMÁTICA
     */
    function addTallaEntry(talla = '', cantidad = 1, color = '#000000', prendaId = '') {
        const tallasContainer = document.getElementById('tallasContainer');
        
        // Si no se pasan valores (clic manual en añadir), intentamos copiar de la última fila
        if (talla === '' && prendaId === '' && color === '#000000') {
            const filas = tallasContainer.querySelectorAll('.talla-entry');
            if (filas.length > 0) {
                const ultimaFila = filas[filas.length - 1];
                prendaId = ultimaFila.querySelector('select[name="prenda_id[]"]').value;
                talla = ultimaFila.querySelector('select[name="talla[]"]').value;
                color = ultimaFila.querySelector('input[name="color[]"]').value;
                cantidad = 1; // Reseteamos cantidad a 1 para la copia
            }
        }

        const div = document.createElement('div');
        div.className = 'talla-entry d-flex align-items-center gap-2 mb-2 bg-light p-2 rounded';
        
        // Opciones del catálogo de prendas
        let prendasHtml = `<option value="">-- Prenda --</option>`;
        catalogoPrendas.forEach(p => {
            const selected = (p.id == prendaId) ? 'selected' : '';
            prendasHtml += `<option value="${p.id}" ${selected}>${p.tipo_prenda} ${p.marca} (${p.modelo})</option>`;
        });

        // Opciones fijas para el ComboBox de tallas
        const tallasFijas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        let tallasHtml = `<option value="">-- Talla --</option>`;
        tallasFijas.forEach(t => {
            const selected = (t == talla) ? 'selected' : '';
            tallasHtml += `<option value="${t}" ${selected}>${t}</option>`;
        });

        div.innerHTML = `
            <select class="form-select form-select-sm" name="prenda_id[]" required style="flex: 2;">${prendasHtml}</select>
            <select class="form-select form-select-sm" name="talla[]" required style="flex: 1;">${tallasHtml}</select>
            <input type="number" class="form-control form-control-sm" name="cantidad[]" value="${cantidad}" min="1" style="width: 70px;">
            <input type="color" class="form-control form-control-color border-0" name="color[]" value="${color}" style="width: 40px;">
            <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>`;
        
        tallasContainer.appendChild(div);
        div.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotalPiezas);
        calcularTotalPiezas();
    }

    function calcularTotalPiezas() {
        let total = 0;
        document.querySelectorAll('input[name="cantidad[]"]').forEach(i => total += parseInt(i.value) || 0);
        document.getElementById('totalPiezasAdmin').innerText = total;
    }

    /**
     * EVENTOS DOM
     */
    document.addEventListener('DOMContentLoaded', () => {
        const waModal = new bootstrap.Modal(document.getElementById('waModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        let idToDelete = null;

        cargarPedidos();
        addTallaEntry();

        document.getElementById('addTalla').addEventListener('click', () => addTallaEntry());
        document.getElementById('buscadorNombre').addEventListener('input', (e) => cargarPedidos(e.target.value));

        document.addEventListener('click', (e) => {
            // WhatsApp
            const btnWA = e.target.closest('.btn-wa-preview');
            if (btnWA) {
                const link = btnWA.getAttribute('data-link');
                const urlObj = new URL(link);
                document.getElementById('wa-destinatario').innerText = btnWA.getAttribute('data-nombre');
                document.getElementById('wa-mensaje-pre').value = decodeURIComponent(urlObj.searchParams.get("text"));
                document.getElementById('wa-confirmar-link').dataset.tel = btnWA.getAttribute('data-tel').replace(/\D/g, '');
                waModal.show();
            }

            // Editar Pedido
            const btnEdit = e.target.closest('.edit-btn');
            if (btnEdit) {
                fetch(`php/editor.php?id=${btnEdit.getAttribute('data-id')}`).then(res => res.json()).then(data => {
                    if (data.success) {
                        const p = data.pedido;
                        document.getElementById('pedidoId').value = p.id;
                        document.getElementById('nombrePedido').value = p.nombre;
                        document.getElementById('telefono').value = p.telefono || '';
                        document.getElementById('status').value = p.status;
                        document.getElementById('fechaInicio').value = p.fechaInicio;
                        document.getElementById('fechaEntrega').value = p.fechaEntrega;
                        document.getElementById('costo').value = p.costo;
                        document.getElementById('anticipo').value = p.anticipo;
                        document.getElementById('instrucciones').value = p.instrucciones || '';
                        document.getElementById('tallasContainer').innerHTML = '';
                        (p.tallas || []).forEach(t => addTallaEntry(t.talla, t.cantidad, t.color, t.prenda_id));
                        document.getElementById('formHeader').innerText = "Editando Pedido #" + p.id;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });
            }

            // Eliminar Pedido
            if (e.target.closest('.delete-btn')) {
                idToDelete = e.target.closest('.delete-btn').getAttribute('data-id');
                deleteModal.show();
            }

            // Quitar fila de talla
            if (e.target.closest('.remove-talla')) {
                e.target.closest('.talla-entry').remove();
                calcularTotalPiezas();
            }

            // Eliminar prenda catálogo
            const btnEliminarC = e.target.closest('.btn-eliminar-prenda');
            if (btnEliminarC) {
                const cid = btnEliminarC.getAttribute('data-id');
                if(confirm('¿Eliminar esta prenda del catálogo?')) {
                    const fd = new FormData();
                    fd.append('accion', 'eliminar');
                    fd.append('id', cid);
                    fetch('php/catalog_management.php', { method: 'POST', body: fd })
                    .then(r => r.json()).then(d => { if(d.success) document.getElementById(`prenda-${cid}`).remove(); });
                }
            }
        });

        // WhatsApp confirmar
        document.getElementById('wa-confirmar-link').addEventListener('mousedown', function() {
            const tel = this.dataset.tel;
            const msj = document.getElementById('wa-mensaje-pre').value;
            this.href = `https://wa.me/52${tel}?text=${encodeURIComponent(msj)}`;
        });

        // Eliminar pedido confirmar
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            fetch(`php/deleteOrder.php?id=${idToDelete}`, { method: 'DELETE' })
            .then(res => res.json()).then(data => { if (data.success) { deleteModal.hide(); cargarPedidos(); } });
        });

        // Guardar catálogo
        document.getElementById('formNuevaPrenda').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion', 'guardar');
            fetch('php/catalog_management.php', { method: 'POST', body: fd })
            .then(r => r.json()).then(d => { if(d.success) location.reload(); });
        });
    });
</script>
</body>
</html>