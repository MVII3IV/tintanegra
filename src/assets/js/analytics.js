// src/assets/js/analytics.js

let chartInstance = null;
let analyticsData = null;
let currentMode = 'ventas'; // Estado inicial

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenueChart');
    const yearSelect = document.getElementById('yearFilter');

    // 1. Carga inicial
    if (ctx) {
        loadAnalyticsData(); 
    }

    // 2. Evento: Cambio de Año
    if (yearSelect) {
        yearSelect.addEventListener('change', (e) => {
            const selectedYear = e.target.value;
            loadAnalyticsData(selectedYear);
        });
    }

    // 3. Eventos: Cambio de Modo (Dinero vs Prendas vs Pedidos)
    document.getElementById('modeVentas').addEventListener('change', () => {
        currentMode = 'ventas';
        if (analyticsData) updateChart(ctx);
    });
    document.getElementById('modePrendas').addEventListener('change', () => {
        currentMode = 'prendas';
        if (analyticsData) updateChart(ctx);
    });
    // --- NUEVO LISTENER ---
    document.getElementById('modePedidos').addEventListener('change', () => {
        currentMode = 'pedidos';
        if (analyticsData) updateChart(ctx);
    });
});

// Función Principal: Cargar datos del servidor
function loadAnalyticsData(year = '') {
    const url = year ? `php/get_analytics.php?year=${year}` : 'php/get_analytics.php';

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                analyticsData = data;
                populateYearDropdown(data.available_years, data.year);
                updateChart(document.getElementById('revenueChart'));
                renderCards(data);
            } else {
                console.error("Error API:", data.error);
            }
        })
        .catch(err => console.error(err));
}

function populateYearDropdown(years, currentYear) {
    const select = document.getElementById('yearFilter');
    if (!select) return;
    select.innerHTML = ''; 
    years.forEach(y => {
        const option = document.createElement('option');
        option.value = y;
        option.innerText = y;
        if (parseInt(y) === parseInt(currentYear)) option.selected = true;
        select.appendChild(option);
    });
}

function updateChart(ctx) {
    if (chartInstance) {
        chartInstance.destroy();
    }

    let label, dataValues, colorBg, colorBorder, yPrefix;
    const tituloEl = document.getElementById('chartTitle');
    const yearLabel = analyticsData.year || '';

    // Configuración según el modo seleccionado
    if (currentMode === 'ventas') {
        label = `Ingresos ($) - ${yearLabel}`;
        dataValues = analyticsData.ventas;
        colorBg = 'rgba(54, 162, 235, 0.6)'; // Azul
        colorBorder = 'rgba(54, 162, 235, 1)';
        yPrefix = '$';
        if(tituloEl) tituloEl.innerText = `Ingresos Mensuales ${yearLabel}`;
    } else if (currentMode === 'prendas') {
        label = `Prendas (Unidades) - ${yearLabel}`;
        dataValues = analyticsData.prendas;
        colorBg = 'rgba(255, 99, 132, 0.6)'; // Rojo
        colorBorder = 'rgba(255, 99, 132, 1)';
        yPrefix = '';
        if(tituloEl) tituloEl.innerText = `Volumen de Prendas ${yearLabel}`;
    } else {
        // --- NUEVO MODO: PEDIDOS ---
        label = `Pedidos Entregados - ${yearLabel}`;
        dataValues = analyticsData.pedidos;
        colorBg = 'rgba(75, 192, 192, 0.6)'; // Verde Azulado (Teal)
        colorBorder = 'rgba(75, 192, 192, 1)';
        yPrefix = '';
        if(tituloEl) tituloEl.innerText = `Frecuencia de Pedidos ${yearLabel}`;
    }

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: analyticsData.labels,
            datasets: [{
                label: label,
                data: dataValues,
                backgroundColor: colorBg,
                borderColor: colorBorder,
                borderWidth: 1,
                borderRadius: 5,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let val = context.parsed.y;
                            if (currentMode === 'ventas') {
                                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val);
                            } else if (currentMode === 'pedidos') {
                                return val + ' pedidos';
                            } else {
                                return val + ' pzas';
                            }
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        callback: function(value) {
                            // Formato eje Y
                            if(currentMode === 'ventas') return '$' + (value >= 1000 ? value/1000 + 'k' : value);
                            return value; // Para pedidos y prendas solo el número
                        },
                        stepSize: currentMode === 'pedidos' ? 1 : undefined // Para pedidos forzamos enteros si son pocos
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

function renderCards(data) {
    const container = document.getElementById('statsResumen');
    if (!container) return;

    const totalVentas = data.ventas.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
    const totalPrendas = data.prendas.reduce((a, b) => parseInt(a) + parseInt(b), 0);
    const totalPedidos = data.pedidos.reduce((a, b) => parseInt(a) + parseInt(b), 0);
    const ticketPromedio = totalPedidos > 0 ? (totalVentas / totalPedidos) : 0;
    const money = (n) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

    container.innerHTML = `
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-5 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted text-uppercase small fw-bold mb-1">Ingresos Anuales</h6><h3 class="fw-bold text-dark mb-0">${money(totalVentas)}</h3></div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success"><i class='bx bx-money fs-3'></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-5 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted text-uppercase small fw-bold mb-1">Ticket Promedio</h6><h3 class="fw-bold text-dark mb-0">${money(ticketPromedio)}</h3><small class="text-success" style="font-size: 0.7rem;">Por pedido</small></div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary"><i class='bx bx-purchase-tag-alt fs-3'></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-5 border-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted text-uppercase small fw-bold mb-1">Prendas Totales</h6><h3 class="fw-bold text-dark mb-0">${totalPrendas}</h3><small class="text-muted" style="font-size: 0.7rem;">Unidades</small></div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger"><i class='bx bxs-t-shirt fs-3'></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-5 border-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted text-uppercase small fw-bold mb-1">Total Pedidos</h6><h3 class="fw-bold text-dark mb-0">${totalPedidos}</h3></div>
                    <div class="bg-dark bg-opacity-10 p-3 rounded-circle text-dark"><i class='bx bx-shopping-bag fs-3'></i></div>
                </div>
            </div>
        </div>
    `;
}