<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Tinta Negra</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background-color: #f8f9fa; }
        .chart-container { 
            position: relative; 
            height: 60vh; 
            width: 100%; 
            background: white; 
            padding: 20px; 
            border-radius: 15px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="admin.php">
            <i class="bx bxs-dashboard me-2"></i> TINTA NEGRA
        </a>
        <div class="d-flex gap-2">
            <a href="admin.php" class="btn btn-outline-light btn-sm"><i class='bx bx-arrow-back'></i> Volver al Panel</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-0"><i class='bx bx-line-chart text-primary'></i> Reporte de Ingresos</h2>
            <p class="text-muted mb-0">Visualización del flujo de dinero y producción.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex align-items-center bg-white p-2 rounded-3 shadow-sm border">
                <label for="yearFilter" class="me-2 fw-bold small text-muted text-uppercase">Año:</label>
                <select id="yearFilter" class="form-select form-select-sm border-0 bg-light fw-bold text-primary" style="width: 100px; cursor: pointer;">
                    <option value="">Cargando...</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="chart-container" style="position: relative; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-dark" id="chartTitle">Ingresos Mensuales</h5>
                    
                    <div class="btn-group shadow-sm" role="group" aria-label="Filtro de Gráfica">
                        <input type="radio" class="btn-check" name="chartMode" id="modeVentas" autocomplete="off" checked>
                        <label class="btn btn-outline-primary btn-sm px-3" for="modeVentas">
                            <i class='bx bx-dollar'></i> Dinero
                        </label>

                        <input type="radio" class="btn-check" name="chartMode" id="modePrendas" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm px-3" for="modePrendas">
                            <i class='bx bxs-t-shirt'></i> Prendas
                        </label>

                        <input type="radio" class="btn-check" name="chartMode" id="modePedidos" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm px-3" for="modePedidos">
                            <i class='bx bx-shopping-bag'></i> Pedidos
                        </label>
                    </div>
                </div>

                <div style="height: 50vh;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4 g-3" id="statsResumen">
        </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/utils.js"></script> <script src="assets/js/analytics.js"></script>

</body>
</html>