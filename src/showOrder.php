<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Estado de Pedido | Gestión de Taller</title>

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
        @media print {
            .btn, .admin-top-bar, .swiper-button-next, .swiper-button-prev, .swiper-pagination { display: none !important; }
            body { background: white !important; }
            .pedido-container { box-shadow: none !important; border: none !important; width: 100% !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-3">
    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <div class="admin-top-bar d-flex justify-content-between align-items-center bg-white p-2 px-3 shadow-sm mb-4">
            <a href="admin.php" class="btn btn-primary btn-sm">
                <i class="bx bx-arrow-back"></i> Regresar al Panel
            </a>
            <div class="d-none d-md-block text-center">
                <span class="badge bg-dark">MODO ADMINISTRADOR</span>
            </div>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-printer"></i> Imprimir Ficha
            </button>
        </div>
    <?php endif; ?>
</div>

<div class="pedido-container shadow-sm bg-white p-4 mx-auto mb-5" style="max-width: 900px; border-radius: 20px;">
    
    <div class="pedido-header text-center mb-4">
        <h2 class="fw-bold">Detalles de tu Pedido</h2>
        <p id="pedido-nombre" class="text-muted mb-0 h5"></p>
    </div>

    <div class="swiper mySwiper mb-4">
        <div class="swiper-wrapper" id="pedido-imagenes"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>

    <div class="text-center mb-4"> 
        <button class="btn btn-light border btn-sm" data-bs-toggle="modal" data-bs-target="#modalPaleta">
            <i class="bx bx-palette text-primary"></i> Ver paleta de colores
        </button>
    </div>

    <div class="pedido-timeline mb-5">
        <ul class="timeline-list d-flex justify-content-between list-unstyled px-0 mb-0">
            <li class="timeline-item text-center">
                <div class="timeline-circle shadow-sm"><i class="bx bx-receipt"></i></div>
                <div class="timeline-label small mt-2">Recibida</div>
            </li>
            <li class="timeline-item text-center">
                <div class="timeline-circle shadow-sm"><i class="bx bx-dollar"></i></div>
                <div class="timeline-label small mt-2">Anticipo</div>
            </li>
            <li class="timeline-item text-center">
                <div class="timeline-circle shadow-sm"><i class="bx bx-cog"></i></div>
                <div class="timeline-label small mt-2">Producción</div>
            </li>
            <li class="timeline-item text-center">
                <div class="timeline-circle shadow-sm"><i class="bx bx-check-double"></i></div>
                <div class="timeline-label small mt-2">Finalizada</div>
            </li>
            <li class="timeline-item text-center">
                <div class="timeline-circle shadow-sm"><i class="bx bx-package"></i></div>
                <div class="timeline-label small mt-2">Entregada</div>
            </li>
        </ul>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card-soft h-100 p-4 border shadow-sm bg-light">
                <div class="text-center mb-3 fw-bold border-bottom pb-2">FECHAS CLAVE</div>
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <small class="text-muted d-block">Inicio</small>
                        <span id="pedido-fecha-inicio" class="fw-bold"></span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Entrega</small>
                        <span id="pedido-fecha-entrega" class="fw-bold text-primary"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div id="contenedor-pagos" class="h-100">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="card-soft p-4 h-100 border shadow-sm bg-white">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Costo Total:</span> <span id="admin-costo-total" class="fw-bold"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-success">
                            <span>Pagado:</span> <span id="admin-anticipo-pagado" class="fw-bold"></span>
                        </div>
                        <hr class="my-2">
                        <div id="card-saldo" class="p-2 rounded-3 text-center text-white shadow-sm">
                            <small class="d-block opacity-75">Saldo Pendiente</small>
                            <h3 class="mb-0 fw-bold" id="admin-saldo-restante"></h3>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-soft h-100 p-4 border shadow-sm bg-white text-center">
                        <div class="d-flex justify-content-between mb-2"><span>Restante a Pagar</span><strong id="pedido-restante" class="text-danger"></strong></div>
                        <div class="progress" style="height: 12px; border-radius: 10px;">
                            <div id="barra-pago" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Saldar al momento de la entrega</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-soft p-4 border shadow-sm mb-4">
        <div class="fw-bold mb-3 border-bottom pb-2">TALLAS Y ESPECIFICACIONES</div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 text-center">
                <thead class="table-light"><tr><th>Talla</th><th>Cantidad</th><th>Color</th></tr></thead>
                <tbody id="pedido-tallas"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPaleta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header border-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center p-4">
                <img id="pedido-paleta" src="" class="img-fluid rounded shadow-sm" />
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVisor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0"><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0 text-center"><img id="img-visor" src="" style="max-height: 85vh; width: auto; object-fit: contain;"></div>
        </div>
    </div>
</div>

<script>
    const estados = ["Recibida", "Anticipo recibido", "En produccion", "Finalizada", "Entregada"];
    const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });

    const params = new URLSearchParams(window.location.search);
    const pedidoId = params.get("id");
    const container = document.querySelector(".pedido-container");

    if (!pedidoId) {
        container.innerHTML = '<h3 class="text-center text-danger py-5">ID de pedido no proporcionado</h3>';
    } else {
        fetch(`php/editor?id=${pedidoId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.pedido) {
                container.innerHTML = `<h3 class="text-center text-danger py-5">No se encontró el pedido</h3>`;
                return;
            }

            const p = data.pedido;
            const costo = parseFloat(p.costo || 0);
            const anticipo = parseFloat(p.anticipo || 0);
            const restante = Math.max(0, costo - anticipo);

            // 1. LLENAR DATOS DE ADMIN (Si existen)
            const elTotal = document.getElementById("admin-costo-total");
            if (elTotal) {
                elTotal.textContent = fmtMoney(costo);
                document.getElementById("admin-anticipo-pagado").textContent = fmtMoney(anticipo);
                document.getElementById("admin-saldo-restante").textContent = fmtMoney(restante);
                const cardS = document.getElementById("card-saldo");
                if (restante > 0) { cardS.classList.add("bg-danger"); cardS.classList.remove("bg-success"); }
                else { cardS.classList.add("bg-success"); cardS.classList.remove("bg-danger"); }
            }

            // 2. LLENAR DATOS GENERALES
            document.getElementById("pedido-nombre").textContent = p.nombre;
            document.getElementById("pedido-fecha-inicio").textContent = p.fechaInicio;
            document.getElementById("pedido-fecha-entrega").textContent = p.fechaEntrega;
            
            if (p.paletaColor) document.getElementById("pedido-paleta").src = p.paletaColor;

            // Lógica Timeline
            document.querySelectorAll(".timeline-item").forEach((item, index) => {
                if (index <= estados.indexOf(p.status)) item.classList.add("completed");
            });

            // 3. VISTA PÚBLICA (Si existen)
            const elRestante = document.getElementById("pedido-restante");
            if (elRestante) {
                elRestante.textContent = fmtMoney(restante);
                const pct = costo ? Math.min(100, Math.round((anticipo / costo) * 100)) : 0;
                document.getElementById("barra-pago").style.width = pct + "%";
            }

            // 4. TABLA TALLAS
            const tbody = document.getElementById("pedido-tallas");
            tbody.innerHTML = "";
            (p.tallas || []).forEach(t => {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td>${t.talla}</td><td>${t.cantidad}</td><td><span class="color-chip shadow-sm" style="background:${t.color}; display:inline-block; width:20px; height:20px; border-radius:50%; border:2px solid white;"></span></td>`;
                tbody.appendChild(tr);
            });

            // 5. CARRUSEL Y VISOR
            const wrap = document.getElementById("pedido-imagenes");
            wrap.innerHTML = "";
            (p.imagenes || []).forEach(src => {
                const slide = document.createElement("div");
                slide.className = "swiper-slide text-center";
                slide.innerHTML = `<img src="${src}" class="rounded shadow-sm" style="max-height: 300px; cursor: zoom-in;">`;
                slide.onclick = () => {
                    document.getElementById('img-visor').src = src;
                    new bootstrap.Modal(document.getElementById('modalVisor')).show();
                };
                wrap.appendChild(slide);
            });

            new Swiper(".mySwiper", {
                loop: true,
                pagination: { el: ".swiper-pagination", clickable: true },
                navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }
            });
        })
        .catch(err => {
            container.innerHTML = '<h3 class="text-center text-danger py-5">Error en el servidor</h3>';
        });
    }
</script>
</body>
</html>