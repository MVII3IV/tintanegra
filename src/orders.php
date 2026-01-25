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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos | Tinta Negra</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background-color: #f4f7f6; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .row-link { transition: background 0.2s; }
        .row-link:hover { background-color: #f8f9fa !important; }
        
        /* Estilo compacto para pedidos ya entregados */
        .row-entregada { opacity: 0.6; background-color: #fdfdfd !important; }

        .select-status-inline { 
            font-size: 0.75rem; padding: 4px 30px 4px 12px; border-radius: 20px; 
            font-weight: bold; cursor: pointer; border: 1px solid #ddd;
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 10px 10px;
        }

        .st-recibida { background-color: #e0f7fa; color: #006064; border-color: #b2ebf2; }
        .st-anticipo { background-color: #e8f0fe; color: #1a73e8; border-color: #d2e3fc; }
        .st-produccion { background-color: #fff9c4; color: #827717; border-color: #fff59d; }
        .st-finalizada { background-color: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
        .st-entregada { background-color: #f5f5f5; color: #424242; border-color: #e0e0e0; }

        /* Ajustes de Modal y Botones */
        #waModal .modal-body { text-align: left !important; direction: ltr !important; }
        #wa-mensaje-pre { text-align: left !important; direction: ltr !important; }
        .modal-header .btn-close { margin: 0; position: absolute; left: 15px; top: 20px; }
        .d-flex.justify-content-center.gap-2 .btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; padding: 0;
        }
        .bxl-whatsapp { transform: translateY(1px); font-size: 1.1rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4 shadow">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="admin.php">
            <i class="bx bx-arrow-back"></i> <span>REGRESAR A PANEL</span>
        </a>
        <span class="navbar-text">Historial de Pedidos</span>
    </div>
</nav>

<div class="container">
    <div class="table-container shadow-sm mb-5">
        <div class="row mb-4 g-3 align-items-center">
            <div class="col-md-5">
                <h4 class="fw-bold mb-0">Base de Datos General</h4>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <label class="form-check-label small fw-bold text-primary mb-0" for="soloPendientes" style="cursor:pointer;">
                        MOSTRAR SOLO PENDIENTES
                    </label>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="soloPendientes" checked style="cursor:pointer;">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select id="filtroEstado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="Recibida">Recibida</option>
                    <option value="Anticipo recibido">Anticipo recibido</option>
                    <option value="En produccion">En producción</option>
                    <option value="Finalizada">Finalizada</option>
                    <option value="Entregada">Entregada</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                    <input type="text" id="busquedaGlobal" class="form-control" placeholder="Buscar cliente o ID...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID / Cliente</th>
                        <th>Entrega</th>
                        <th>Costo / Saldo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="listaCompleta"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="waModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-success text-white border-0 position-relative" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title w-100 text-center"><i class="bx bxl-whatsapp me-2"></i> Previsualizar Notificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-start" dir="ltr">
                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Enviar a:</label>
                    <div id="wa-destinatario" class="fw-bold fs-5"></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Mensaje (Editable):</label>
                    <textarea id="wa-mensaje-pre" class="form-control bg-light rounded-3 border" style="font-size: 0.95rem; height: 150px; resize: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="wa-confirmar-link" class="btn btn-success px-4" style="border-radius: 10px;">Confirmar y Enviar</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4"><i class="bx bx-error-circle text-danger" style="font-size: 80px;"></i></div>
                <h4 class="fw-bold mb-3">¿Eliminar pedido?</h4>
                <p class="text-muted mb-4">Se borrarán permanentemente los datos y archivos del servidor.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4">Eliminar Ahora</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="statusToast" class="toast align-items-center text-white bg-dark border-0 shadow" role="alert">
        <div class="d-flex">
            <div id="toastMessage" class="toast-body">¡Operación exitosa!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });

    function generarLinkWhatsApp(p) {
        if (!p.telefono) return '#'; 
        const telLimpio = p.telefono.replace(/\D/g, '');
        if (!telLimpio) return '#';

        const host = window.location.hostname === 'localhost' 
                     ? 'http://localhost:8080/tinta_negra' 
                     : 'https://www.tintanegra.mx';
        
        const urlPedido = `${host}/showOrder.php?id=${p.id}`;
        let costo = parseFloat(p.costo || 0);
        let anticipo = parseFloat(p.anticipo || 0);
        let saldo = (p.status === 'Entregada') ? 0 : (costo - anticipo);

        let montoTexto = (p.status === 'Entregada') 
            ? "El pedido ha sido ENTREGADO y liquidado. Muchas gracias por tu confianza." 
            : (saldo > 0 ? `El saldo pendiente es de ${fmtMoney(saldo)}. Recuerda liquidar al recibir.` : "El pedido ya se encuentra liquidado.");

        const mensaje = `Hola *${p.nombre}*, te saludamos de Tinta Negra.\n\n` +
                        `Te informamos que tu pedido con folio *${p.id}* cambio su estado a: *${p.status.toUpperCase()}*.\n\n` +
                        `${montoTexto}\n\n` +
                        `Puedes consultar los detalles aqui:\n${urlPedido}`;
                    
        return `https://wa.me/52${telLimpio}?text=${encodeURIComponent(mensaje)}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const waModal = new bootstrap.Modal(document.getElementById('waModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        let idToDelete = null;

        function getStatusClass(status) {
            switch(status) {
                case 'Recibida': return 'st-recibida';
                case 'Anticipo recibido': return 'st-anticipo';
                case 'En produccion': return 'st-produccion';
                case 'Finalizada': return 'st-finalizada';
                case 'Entregada': return 'st-entregada';
                default: return '';
            }
        }

        function cargarTodo() {
            const busqueda = document.getElementById('busquedaGlobal').value.toLowerCase();
            const estadoFiltro = document.getElementById('filtroEstado').value;
            const soloPendientes = document.getElementById('soloPendientes').checked;

            fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(busqueda)}&todo=true`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('listaCompleta');
                tbody.innerHTML = '';

                if (data.success && data.pedidos.length > 0) {
                    const filtrados = data.pedidos.filter(p => {
                        const coincideEstado = estadoFiltro === "" || p.status === estadoFiltro;
                        const coincideBusqueda = p.nombre.toLowerCase().includes(busqueda) || p.id.toLowerCase().includes(busqueda);
                        
                        // Si el interruptor esta activo, filtramos lo entregado (a menos que el usuario busque por nombre)
                        const cumpleVista = soloPendientes ? (p.status !== 'Entregada') : true;

                        return coincideEstado && coincideBusqueda && cumpleVista;
                    });

                    filtrados.forEach(p => {
                        let costo = parseFloat(p.costo || 0);
                        let anticipo = parseFloat(p.anticipo || 0);
                        const esEntregado = (p.status === 'Entregada');
                        let saldo = esEntregado ? 0 : (costo - anticipo);

                        const linkWS = generarLinkWhatsApp(p);
                        const wsButton = (linkWS !== '#') 
                            ? `<button type="button" class="btn btn-sm btn-success border-0 btn-wa-preview" data-nombre="${p.nombre}" data-tel="${p.telefono}" data-link="${linkWS}"><i class="bx bxl-whatsapp"></i></button>`
                            : `<button class="btn btn-sm btn-light border-0 text-muted" disabled><i class="bx bxl-whatsapp"></i></button>`;

                        const tr = document.createElement('tr');
                        tr.className = `row-link ${esEntregado ? 'row-entregada' : ''}`;
                        tr.innerHTML = `
                            <td><div class="fw-bold">${p.nombre}</div><div class="small text-muted">ID: ${p.id}</div></td>
                            <td class="small">${p.fechaEntrega}</td>
                            <td><div class="small text-muted">${fmtMoney(costo)}</div><div class="fw-bold ${saldo > 0 ? 'text-warning' : 'text-success'}">${fmtMoney(saldo)}</div></td>
                            <td><select class="form-select form-select-sm select-status-inline ${getStatusClass(p.status)}" data-id="${p.id}">
                                <option value="Recibida" ${p.status === 'Recibida' ? 'selected' : ''}>RECIBIDA</option>
                                <option value="Anticipo recibido" ${p.status === 'Anticipo recibido' ? 'selected' : ''}>ANTICIPO RECIBIDO</option>
                                <option value="En produccion" ${p.status === 'En produccion' ? 'selected' : ''}>EN PRODUCCIÓN</option>
                                <option value="Finalizada" ${p.status === 'Finalizada' ? 'selected' : ''}>FINALIZADA</option>
                                <option value="Entregada" ${p.status === 'Entregada' ? 'selected' : ''}>ENTREGADA</option>
                            </select></td>
                            <td class="text-center"><div class="d-flex justify-content-center gap-2">${wsButton}<a href="showOrder.php?id=${p.id}" class="btn btn-sm btn-light border"><i class="bx bx-show"></i></a><button type="button" class="btn btn-sm btn-outline-danger delete-order-btn" data-id="${p.id}"><i class="bx bx-trash"></i></button></div></td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Sin registros.</td></tr>';
                }
            });
        }

        // WhatsApp Modal Logic
        document.addEventListener('click', function(e) {
            const btnWA = e.target.closest('.btn-wa-preview');
            if (btnWA) {
                const nombre = btnWA.getAttribute('data-nombre');
                const telefono = btnWA.getAttribute('data-tel');
                const link = btnWA.getAttribute('data-link');
                const urlObj = new URL(link);
                document.getElementById('wa-destinatario').innerText = `${nombre} (${telefono})`;
                document.getElementById('wa-mensaje-pre').value = decodeURIComponent(urlObj.searchParams.get("text"));
                document.getElementById('wa-confirmar-link').dataset.tel = telefono.replace(/\D/g, '');
                waModal.show();
            }
            if (e.target.closest('.delete-order-btn')) {
                idToDelete = e.target.closest('.delete-order-btn').dataset.id;
                deleteModal.show();
            }
        });

        document.getElementById('wa-confirmar-link').addEventListener('click', function(e) {
            e.preventDefault();
            const tel = this.dataset.tel;
            const mensajeFinal = document.getElementById('wa-mensaje-pre').value;
            window.open(`https://wa.me/52${tel}?text=${encodeURIComponent(mensajeFinal)}`, '_blank');
            waModal.hide();
        });

        // Event Listeners
        document.getElementById('busquedaGlobal').addEventListener('input', cargarTodo);
        document.getElementById('filtroEstado').addEventListener('change', cargarTodo);
        document.getElementById('soloPendientes').addEventListener('change', cargarTodo);

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('select-status-inline')) {
                const select = e.target;
                const params = new URLSearchParams();
                params.append('id', select.dataset.id);
                params.append('status', select.value);
                fetch('php/updateStatus.php', { method: 'POST', body: params }).then(() => cargarTodo());
            }
        });

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            fetch(`php/deleteOrder.php?id=${idToDelete}`, { method: 'DELETE' }).then(() => { deleteModal.hide(); cargarTodo(); });
        });

        window.onload = cargarTodo;
    });
</script>
</body>
</html>