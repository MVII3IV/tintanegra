<?php
session_start(); 
header('Content-Type: text/html; charset=utf-8');
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
        
        /* Centrado perfecto para los botones de acción */
        .d-flex.justify-content-center.gap-1 .btn, 
        .d-flex.justify-content-center.gap-2 .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
        }

        /* Ajuste específico para el icono de WhatsApp */
        .bxl-whatsapp {
            transform: translateY(1px);
            font-size: 1.1rem;
        }

        /* Corregir posición de la X en los modales */
        .modal-header .btn-close {
            margin: 0;
            position: absolute;
            left: 15px; /* En RTL, 'left' es el extremo donde suele ir la X de cierre */
            top: 20px;
        }

        /* Darle espacio al título para que no choque con la X */
        .modal-title {
            width: 100%;
            text-align: center;
            padding-right: 0;
        }
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
            <div class="col-md-5">
                <h5 class="mb-0 fw-bold">Pedidos Activos</h5>
            </div>
            <div class="col-md-7">
                <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bx bx-search text-primary"></i>
                    </span>
                    <input type="text" id="buscadorNombre" class="form-control border-start-0 border-end-0 ps-0" placeholder="Buscar cliente...">
                    <a href="orders.php" class="btn btn-primary d-flex align-items-center gap-1 border-0" style="border-radius: 0 8px 8px 0; background-color: #0d6efd;">
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
                    <h5 class="mb-0 fw-bold">Detalle de Tallas</h5>
                    <small class="text-muted">Total acumulado: <span id="totalPiezasAdmin" class="fw-bold text-primary">0</span> piezas</small>
                </div>
                <button type="button" id="addTalla" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Añadir</button>
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

<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="bx bx-check-double me-2"></i> ¡Pedido guardado correctamente!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4"><i class="bx bx-error-circle text-danger" style="font-size: 80px;"></i></div>
                <h4 class="fw-bold mb-3">¿Confirmar eliminación?</h4>
                <p class="text-muted mb-4">Esta acción eliminará permanentemente el pedido y sus archivos. No podrás deshacerlo.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4">Eliminar Ahora</button>
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
                    <label class="small text-muted fw-bold text-uppercase">Mensaje (puedes editarlo aquí):</label>
                    <textarea id="wa-mensaje-pre" class="form-control bg-light rounded-3 border" 
                            style="font-size: 0.95rem; height: 150px; resize: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="wa-confirmar-link" target="_blank" class="btn btn-success px-4">Confirmar y Enviar</a>
            </div>
        </div>
    </div>
</div>

<script>
    const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });

    /**
     * Genera el enlace de WhatsApp con el mensaje predefinido.
     * Maneja casos de teléfono nulo o vacío para evitar errores de JS.
     */
    function generarLinkWhatsApp(p) {
        if (!p.telefono) return '#'; 
        const telLimpio = p.telefono.replace(/\D/g, '');
        if (!telLimpio) return '#';

        // Detección automática de URL
        const host = window.location.hostname === 'localhost' 
                    ? 'http://localhost:8080' 
                    : 'https://www.tintanegra.mx';
        
        const urlPedido = `${host}/showOrder.php?id=${p.id}`;

        let costo = parseFloat(p.costo || 0);
        let anticipo = parseFloat(p.anticipo || 0);
        let statusAct = p.status;
        let saldo = (statusAct === 'Entregada') ? 0 : (costo - anticipo);

        let montoTexto = "";
        if (statusAct === 'Entregada') {
            montoTexto = "El pedido ha sido ENTREGADO y liquidado. Muchas gracias por tu confianza.";
        } else if (saldo > 0) {
            montoTexto = `El saldo pendiente es de ${fmtMoney(saldo)}. Recuerda liquidar al recibir tu pedido.`;
        } else {
            montoTexto = "El pedido ya se encuentra liquidado por completo.";
        }
        
        // Redacción limpia y profesional
        const mensaje = `Hola *${p.nombre}*, te saludamos de Tinta Negra.\n\n` +
                        `Te informamos que tu pedido con folio *${p.id}* ha cambiado su estado a: *${statusAct.toUpperCase()}*.\n\n` +
                        `${montoTexto}\n\n` +
                        `Puedes consultar todos los detalles y el progreso en el siguiente enlace:\n${urlPedido}\n\n` +
                        `Cualquier duda, quedamos a tus órdenes.`;
                        
        return `https://wa.me/52${telLimpio}?text=${encodeURIComponent(mensaje)}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tallasContainer = document.getElementById('tallasContainer');
        const resultadosDiv = document.getElementById('resultados');
        const buscador = document.getElementById('buscadorNombre');
        const pedidoIdField = document.getElementById('pedidoId');
        const formHeader = document.getElementById('formHeader');
        const submitButton = document.getElementById('submitButton');
        
        // Inicialización de Modales
        const waModal = new bootstrap.Modal(document.getElementById('waModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let idToDelete = null;

        /**
         * CARGAR PEDIDOS Y ESTADÍSTICAS
         */
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

                            const linkWS = generarLinkWhatsApp(p);
                            const wsButton = (linkWS !== '#') 
                                ? `<button type="button" class="btn btn-sm btn-success border-0 btn-wa-preview" 
                                    data-nombre="${p.nombre}" data-tel="${p.telefono}" data-link="${linkWS}">
                                    <i class="bx bxl-whatsapp"></i></button>`
                                : `<button class="btn btn-sm btn-light border-0 text-muted" disabled><i class="bx bxl-whatsapp"></i></button>`;

                            html += `<tr class="bg-white">
                                <td><a href="showOrder.php?id=${p.id}" class="fw-bold text-dark text-decoration-none">${p.nombre}</a></td>
                                <td class="small">${p.fechaEntrega}</td>
                                <td class="fw-bold ${saldo > 0 ? 'text-warning' : 'text-success'}">$${saldo.toFixed(2)}</td>
                                <td><span class="badge rounded-pill bg-light text-dark border">${p.status}</span></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        ${wsButton}
                                        <a href="showOrder.php?id=${p.id}&print=true" target="_blank" class="btn btn-sm btn-light border" title="Imprimir"><i class="bx bx-printer"></i></a>
                                        <button type="button" class="btn btn-sm btn-light border edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-light border delete-btn" data-id="${p.id}"><i class="bx bx-trash text-danger"></i></button>
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
         * GESTIÓN DE EVENTOS CLICK (WhatsApp, Editar, Eliminar)
         */
        document.addEventListener('click', function(e) {
            // 1. Abrir Modal de WhatsApp (Previsualizar y Editar)
            const btnWA = e.target.closest('.btn-wa-preview');
            if (btnWA) {
                const nombre = btnWA.getAttribute('data-nombre');
                const telefono = btnWA.getAttribute('data-tel');
                const link = btnWA.getAttribute('data-link');
                
                // Extraer el mensaje para ponerlo en el textarea
                const urlObj = new URL(link);
                const mensajeOriginal = decodeURIComponent(urlObj.searchParams.get("text"));

                document.getElementById('wa-destinatario').innerText = `${nombre} (${telefono})`;
                document.getElementById('wa-mensaje-pre').value = mensajeOriginal;
                
                // Guardar el teléfono limpio en el dataset del botón confirmar
                document.getElementById('wa-confirmar-link').dataset.tel = telefono.replace(/\D/g, '');
                waModal.show();
            }

            // 2. Editar Pedido (Cargar datos al formulario)
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
                        
                        formHeader.innerText = "Editando Pedido #" + p.id;
                        submitButton.innerText = "Actualizar Pedido";
                        window.scrollTo({ top: document.getElementById('pedidoForm').offsetTop - 20, behavior: 'smooth' });
                    }
                });
            }

            // 3. Abrir Modal de Eliminación
            const btnDel = e.target.closest('.delete-btn');
            if (btnDel) {
                idToDelete = btnDel.getAttribute('data-id');
                deleteModal.show();
            }
        });

        /**
         * CONFIRMAR ENVÍO WHATSAPP (CAPTURAR EDICIÓN)
         */
        document.getElementById('wa-confirmar-link').addEventListener('mousedown', function() {
            const tel = this.dataset.tel;
            const mensajeFinal = document.getElementById('wa-mensaje-pre').value;
            
            // En lugar de window.open, actualizamos el href del propio botón
            // El navegador codificará el texto del textarea perfectamente al hacer clic
            this.href = `https://wa.me/52${tel}?text=${encodeURIComponent(mensajeFinal)}`;
        });

        document.getElementById('wa-confirmar-link').addEventListener('click', function() {
            // Cerramos el modal después de una pequeña espera para que el link funcione
            setTimeout(() => { waModal.hide(); }, 500);
        });

        /**
         * CONFIRMAR ELIMINACIÓN
         */
        confirmDeleteBtn.addEventListener('click', function() {
            if (idToDelete) {
                fetch(`php/deleteOrder.php?id=${idToDelete}`, { method: 'DELETE' })
                .then(res => res.json()).then(data => { 
                    if (data.success) { 
                        deleteModal.hide(); 
                        cargarPedidos(buscador.value); 
                    }
                });
            }
        });

        /**
         * FORMATEO DE TELÉFONO Y TALLAS
         */
        document.getElementById('telefono').addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
        });

        function calcularTotalPiezas() {
            const inputs = tallasContainer.querySelectorAll('input[name="cantidad[]"]');
            let total = 0;
            inputs.forEach(i => total += parseInt(i.value) || 0);
            document.getElementById('totalPiezasAdmin').innerText = total;
        }

        function addTallaEntry(talla = '', cantidad = 1, color = '#000000') {
            const div = document.createElement('div');
            div.className = 'talla-entry d-flex align-items-center gap-2 mb-2 bg-light p-2 rounded';
            div.innerHTML = `<input type="text" class="form-control" name="talla[]" placeholder="Ej: L" value="${talla}">
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

        // Toast de éxito si viene de createOrder.php
        if (window.location.search.includes('success=true')) {
            new bootstrap.Toast(document.getElementById('successToast')).show();
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Inicialización
        cargarPedidos();
        buscador.addEventListener('input', (e) => cargarPedidos(e.target.value));
    });
</script>

</body>
</html>