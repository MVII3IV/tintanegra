<?php
session_start(); 
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once 'php/config.php'; 

// Cargamos el catálogo para pasarlo a JS
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
        
        td .d-flex.justify-content-center.gap-1 .btn, 
        td .d-flex.justify-content-center.gap-2 .btn {
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

        <div class="row g-3 mb-4 align-items-center">
    
            <div class="col-md-6">
                <div class="input-group shadow-sm bg-white rounded-3 overflow-hidden border">
                    <span class="input-group-text bg-white border-0 text-muted ps-3">
                        <i class="bx bx-search fs-5"></i>
                    </span>
                    <input type="text" id="buscadorNombre" class="form-control border-0 py-2" placeholder="Buscar cliente, pedido o teléfono...">
                </div>
            </div>

            <div class="col-md-6 d-flex justify-content-md-end justify-content-between gap-2">
                
                <button type="button" id="btnGenerarLista" class="btn btn-warning shadow-sm align-items-center gap-2" style="display: none; border-radius: 8px;">
                    <i class="bx bx-cart-alt fs-5"></i> 
                    <span class="d-none d-sm-inline fw-bold">Lista Compra</span>
                    <span id="contadorSeleccionados" class="badge bg-dark rounded-pill">0</span>
                </button>

                <a href="orders.php" class="btn btn-white border shadow-sm text-dark d-flex align-items-center gap-2" style="border-radius: 8px; background: #fff;">
                    <i class="bx bx-list-ul fs-5 text-primary"></i> 
                    <span class="d-none d-sm-inline fw-medium">Ver Historial</span>
                </a>
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
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-dark btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCatalogo">
                        <i class="bx bx-closet"></i> Administrar Catálogo
                    </button>
                    <button type="button" id="addTalla" class="btn btn-primary btn-sm shadow-sm">
                        <i class="bx bx-plus"></i> Añadir Prenda
                    </button>
                </div>
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

<?php include 'includes/modals.php'; ?>

<script>
    // Pasamos variables PHP a JavaScript globalmente antes de cargar el archivo externo
    window.catalogoPrendas = <?= json_encode($prendasCatalogo) ?>;
</script>
<script src="assets/js/admin.js"></script>

</body>
</html>