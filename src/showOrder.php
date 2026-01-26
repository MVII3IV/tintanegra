<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Estado de Pedido | Tinta Negra</title>

    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css" />
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css" />
    <link rel="stylesheet" href="assets/css/boxicons.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/orders.css" />
    
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <style>
        .admin-top-bar { border-radius: 10px; border: 1px solid #e0e0e0; }
        .card-soft { border-radius: 15px; transition: transform 0.2s; }
        .timeline-item.completed .timeline-circle { background-color: #0d6efd; color: white; }
        #card-saldo { transition: all 0.4s ease; }
        .instrucciones-box { background-color: #fff3cd; border-left: 5px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; font-style: italic; white-space: pre-wrap; }
        
        /* Botón de Cotización */
        .btn-cotizacion { background-color: #f8f9fa; border: 1px solid #ddd; color: #333; transition: all 0.3s; }
        .btn-cotizacion:hover { background-color: #e9ecef; border-color: #ccc; }

        @media print {
            .btn, .admin-top-bar, .swiper-button-next, .swiper-button-prev, .swiper-pagination, .navbar, .modal, .no-print { 
                display: none !important; 
            }
            body { background: white !important; font-size: 11pt; padding: 0; }
            .pedido-container { box-shadow: none !important; border: 1px solid #ddd !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 10px !important; }
            .card { border: none !important; box-shadow: none !important; }
            .swiper { height: auto !important; }
            .swiper-wrapper { display: flex !important; flex-wrap: wrap !important; gap: 10px !important; transform: none !important; }
            .swiper-slide { width: 45% !important; height: auto !important; display: block !important; }
            .swiper-slide img { max-height: 250px !important; width: auto !important; border: 1px solid #eee; }
            .d-print-block { display: block !important; }
            .img-paleta-print { max-height: 350px !important; margin: 0 auto; display: block; break-inside: avoid; }
            .instrucciones-box { background-color: #f9f9f9 !important; border: 1px solid #ccc !important; color: black !important; }
            .color-chip { border: 1px solid #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-3">
    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <div class="admin-top-bar d-flex justify-content-between align-items-center bg-white p-2 px-3 shadow-sm mb-4">
            <a href="admin.php" class="btn btn-primary btn-sm"><i class="bx bx-arrow-back"></i> Panel</a>
            <div class="d-none d-md-block text-center"><span class="badge bg-dark">ADMINISTRADOR</span></div>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bx bx-printer"></i> Imprimir Ficha</button>
        </div>
    <?php endif; ?>
</div>

<div class="pedido-container shadow-sm bg-white p-4 mx-auto mb-5" style="max-width: 900px; border-radius: 20px;">
    
    <div class="pedido-header text-center mb-4">
        <h2 class="fw-bold">Detalles de tu Pedido</h2>
        <p id="pedido-nombre" class="text-muted mb-2 h5"></p>
    </div>

    <div class="swiper mySwiper mb-4">
        <div class="swiper-wrapper" id="pedido-imagenes"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>

    <div class="d-flex justify-content-center gap-2 mb-4 no-print"> 
        <button class="btn btn-light border btn-sm" data-bs-toggle="modal" data-bs-target="#modalPaleta">
            <i class="bx bx-palette text-primary"></i> Paleta de colores
        </button>
        <div id="btn-cotizacion-container"></div>
    </div>

    <div class="d-none d-print-block mb-4">
        <div class="fw-bold mb-2 small text-uppercase text-muted border-bottom pb-1">Paleta de Colores Taller</div>
        <div class="text-center">
            <img id="pedido-paleta-print" src="" class="img-fluid rounded border img-paleta-print">
        </div>
    </div>

    <div class="pedido-timeline mb-5 no-print">
        <ul class="timeline-list d-flex justify-content-between list-unstyled px-0 mb-0">
            <li class="timeline-item text-center"><div class="timeline-circle shadow-sm"><i class="bx bx-receipt"></i></div><div class="timeline-label small mt-2">Recibida</div></li>
            <li class="timeline-item text-center"><div class="timeline-circle shadow-sm"><i class="bx bx-dollar"></i></div><div class="timeline-label small mt-2">Anticipo</div></li>
            <li class="timeline-item text-center"><div class="timeline-circle shadow-sm"><i class="bx bx-cog"></i></div><div class="timeline-label small mt-2">Producción</div></li>
            <li class="timeline-item text-center"><div class="timeline-circle shadow-sm"><i class="bx bx-check-double"></i></div><div class="timeline-label small mt-2">Finalizada</div></li>
            <li class="timeline-item text-center"><div class="timeline-circle shadow-sm"><i class="bx bx-package"></i></div><div class="timeline-label small mt-2">Entregada</div></li>
        </ul>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card-soft h-100 p-4 border shadow-sm bg-light">
                <div class="text-center mb-3 fw-bold border-bottom pb-2 text-uppercase small">Fechas Clave</div>
                <div class="d-flex justify-content-around text-center">
                    <div><small class="text-muted d-block">Inicio</small><span id="pedido-fecha-inicio" class="fw-bold"></span></div>
                    <div><small class="text-muted d-block">Entrega</small><span id="pedido-fecha-entrega" class="fw-bold text-primary"></span></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div id="contenedor-pagos" class="h-100">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="card-soft p-4 h-100 border shadow-sm bg-white">
                        <div class="d-flex justify-content-between mb-1 small text-muted"><span>Costo Total:</span> <span id="admin-costo-total"></span></div>
                        <div class="d-flex justify-content-between mb-1 small text-success"><span>Pagado:</span> <span id="admin-anticipo-pagado"></span></div>
                        <hr class="my-2">
                        <div id="card-saldo" class="p-2 rounded-3 text-center shadow-sm">
                            <small class="d-block opacity-75">Saldo Pendiente</small>
                            <h3 class="mb-0 fw-bold" id="admin-saldo-restante"></h3>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-soft h-100 p-4 border shadow-sm bg-white text-center">
                        <div class="d-flex justify-content-between mb-2"><span>Saldo Pendiente</span><strong id="pedido-restante" class="text-warning"></strong></div>
                        <div class="progress" style="height: 12px; border-radius: 10px;"><div id="barra-pago" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div></div>
                        <small class="text-muted mt-2 d-block">Saldar al momento de la entrega</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="seccion-instrucciones" class="mb-4 d-none">
        <div class="fw-bold mb-2 small text-uppercase text-muted"><i class="bx bx-info-circle"></i> Instrucciones de taller</div>
        <div class="instrucciones-box shadow-sm" id="pedido-instrucciones"></div>
    </div>

    <div class="card-soft p-4 border shadow-sm mb-4">
        <div class="fw-bold mb-3 border-bottom pb-2">TALLAS Y ESPECIFICACIONES</div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr><th>Talla</th><th>Cantidad</th><th>Color</th></tr>
                </thead>
                <tbody id="pedido-tallas"></tbody>
                <tfoot class="table-light border-top">
                    <tr><td class="fw-bold">Total piezas</td><td id="total-piezas" class="fw-bold text-primary" style="font-size: 1.1rem;">0</td><td></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPaleta" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content shadow"><div class="modal-header border-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center p-4"><img id="pedido-paleta" src="" class="img-fluid rounded shadow-sm" /></div></div></div></div>

<script>
    const estados = ["Recibida", "Anticipo recibido", "En produccion", "Finalizada", "Entregada"];
    const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });
    const params = new URLSearchParams(window.location.search);
    const pedidoId = params.get("id");

    if (pedidoId) {
        fetch(`php/editor.php?id=${pedidoId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            const p = data.pedido;

            document.getElementById("pedido-nombre").textContent = p.nombre;
            document.getElementById("pedido-fecha-inicio").textContent = p.fechaInicio;
            document.getElementById("pedido-fecha-entrega").textContent = p.fechaEntrega;

            // Lógica de Saldos
            let costo = parseFloat(p.costo || 0);
            let anticipo = parseFloat(p.anticipo || 0);
            if (p.status === "Entregada") anticipo = costo;
            const restante = Math.max(0, costo - anticipo);

            if (document.getElementById("admin-costo-total")) {
                document.getElementById("admin-costo-total").textContent = fmtMoney(costo);
                document.getElementById("admin-anticipo-pagado").textContent = fmtMoney(anticipo);
                document.getElementById("admin-saldo-restante").textContent = fmtMoney(restante);
                const cardS = document.getElementById("card-saldo");
                cardS.className = "p-2 rounded-3 text-center shadow-sm " + (restante > 0 ? "bg-warning text-dark" : "bg-success text-white");
            } else {
                document.getElementById("pedido-restante").textContent = fmtMoney(restante);
                const pct = (anticipo / costo) * 100;
                document.getElementById("barra-pago").style.width = pct + "%";
            }

            // Timeline
            document.querySelectorAll(".timeline-item").forEach((item, index) => {
                if (index <= estados.indexOf(p.status)) item.classList.add("completed");
            });

            // Instrucciones
            if (p.instrucciones) {
                document.getElementById("seccion-instrucciones").classList.remove("d-none");
                document.getElementById("pedido-instrucciones").textContent = p.instrucciones;
            }

            // Tallas
            const tbody = document.getElementById("pedido-tallas");
            let totalP = 0;
            (p.tallas || []).forEach(t => {
                totalP += parseInt(t.cantidad);
                tbody.innerHTML += `<tr><td>${t.talla}</td><td>${t.cantidad}</td><td><span class="color-chip" style="background:${t.color}; display:inline-block; width:20px; height:20px; border-radius:50%; border:1px solid #ddd;"></span></td></tr>`;
            });
            document.getElementById("total-piezas").textContent = totalP;

            // Paleta
            if (p.paletaColor) {
                document.getElementById("pedido-paleta").src = p.paletaColor;
                document.getElementById("pedido-paleta-print").src = p.paletaColor;
            }

            // Imágenes Swiper
            const wrap = document.getElementById("pedido-imagenes");
            (p.imagenes || []).forEach(src => {
                wrap.innerHTML += `<div class="swiper-slide"><img src="${src}" class="rounded shadow-sm" style="max-height: 300px; width:auto;"></div>`;
            });

            new Swiper(".mySwiper", { 
                loop: true, 
                pagination: { el: ".swiper-pagination" },
                navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }
            });

            // LÓGICA DE COTIZACIÓN (PDF o Imagen)
            if (p.cotizacion) {
                const cotContainer = document.getElementById("btn-cotizacion-container");
                const esPdf = p.cotizacion.toLowerCase().endsWith('.pdf');
                const icon = esPdf ? 'bxs-file-pdf text-danger' : 'bx-image text-success';
                const label = esPdf ? 'Ver Cotización (PDF)' : 'Ver Cotización (Imagen)';
                
                cotContainer.innerHTML = `
                    <a href="${p.cotizacion}" target="_blank" class="btn btn-sm btn-cotizacion shadow-sm">
                        <i class="bx ${icon}"></i> ${label}
                    </a>`;
            }

            if (params.has('print')) {
                setTimeout(() => { window.print(); }, 800);
            }
        });
    }
</script>
</body>
</html>