/**
 * admin.js (CORE)
 * Lógica del Panel Administrativo Tinta Negra
 */

let pedidosCargados = []; 

// 1. CAPTURAR ID DE LA URL (Antes de que se borre)
const paramsURL = new URLSearchParams(window.location.search);
let idPedidoPendiente = null;

if (paramsURL.has('success') && paramsURL.has('id')) {
    idPedidoPendiente = paramsURL.get('id');
}

function cargarPedidos(nombre = '') {
    fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(nombre)}`)
        .then(res => res.json())
        .then(data => {
            let stats = { produccion: 0, entregaHoy: 0, porCobrar: 0, listos: 0 };
            const hoy = new Date().toISOString().split('T')[0];
            const resultadosDiv = document.getElementById('resultados');

            if (data.success) { // Quitamos la condición de length > 0 para que siempre entre
                pedidosCargados = data.pedidos || [];

                // --- AGREGAR ESTO: ORDENAR POR FECHA DE ENTREGA (ASCENDENTE) ---
                pedidosCargados.sort((a, b) => {
                    // Convertimos las strings de fecha a objetos Date para comparar
                    const fechaA = new Date(a.fechaEntrega);
                    const fechaB = new Date(b.fechaEntrega);
                    return fechaA - fechaB; // El menor (fecha más cercana) va primero
                });
                
                // --- (AQUÍ VA TU CÓDIGO DE GENERAR LA TABLA - LO DEJAMOS IGUAL) ---
                let html = `<div class="table-responsive"><table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th style="width: 40px;" class="text-center"><input type="checkbox" class="form-check-input cursor-pointer" id="checkAll"></th>
                            <th>Cliente</th> <th>Entrega</th> <th>Saldo</th> <th>Estado</th> <th class="text-center">Surtido</th> <th class="text-center">Acciones</th>
                        </tr>
                    </thead><tbody>`;
                
                // Si no hay pedidos, mostrar mensaje, si sí, iterar
                if (pedidosCargados.length === 0) {
                    html += `<tr><td colspan="7" class="text-center py-4">Sin pedidos activos.</td></tr>`;
                } else {
                    pedidosCargados.filter(p => p.status !== 'Entregada').forEach(p => {
                        // ... (Tu lógica de contadores y filas existente) ...
                        if (p.status === 'En produccion') stats.produccion++;
                        if (p.status === 'Finalizada') stats.listos++;
                        if (p.fechaEntrega === hoy) stats.entregaHoy++;
                        const saldo = parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0);
                        if (saldo > 0) stats.porCobrar += saldo;

                        html += `<tr class="bg-white">
                            <td class="text-center"><input type="checkbox" class="form-check-input check-pedido" value="${p.id}"></td>
                            <td><a href="showOrder.php?id=${p.id}" class="fw-bold text-dark text-decoration-none d-block py-2">${p.nombre}</a></td>
                            <td class="small">${p.fechaEntrega}</td>
                            <td class="fw-bold ${saldo > 0 ? 'text-warning' : 'text-success'}">$${fmtMoney(saldo)}</td>
                            <td><span class="badge rounded-pill bg-light text-dark border">${p.status}</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-surtido ${p.prendas_surtidas == 1 ? 'btn-success' : 'btn-light border'}" data-id="${p.id}" data-estado="${p.prendas_surtidas}"><i class="bx ${p.prendas_surtidas == 1 ? 'bx-check-double' : 'bx-check'}"></i></button>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-success border-0 btn-wa-preview" data-nombre="${p.nombre}" data-tel="${p.telefono}" data-link="${generarLinkWhatsApp(p)}"><i class="bx bxl-whatsapp"></i></button>
                                    <button class="btn btn-sm btn-light border edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                    <button class="btn btn-sm btn-light border delete-btn" data-id="${p.id}"><i class="bx bx-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                resultadosDiv.innerHTML = html + "</tbody></table></div>";
                
                // Actualizar Stats UI
                document.getElementById('stat-produccion').innerText = stats.produccion;
                document.getElementById('stat-entrega').innerText = stats.entregaHoy;
                document.getElementById('stat-finalizados').innerText = stats.listos;
                document.getElementById('stat-cobro').innerText = fmtMoney(stats.porCobrar); // Usamos fmtMoney de utils
                
                if (typeof actualizarBotonLista === 'function') actualizarBotonLista();


                // =====================================================================
                // SOLUCIÓN: LÓGICA DE NOTIFICACIÓN CON "PLAN B"
                // =====================================================================
                if (idPedidoPendiente) {
                    
                    // INTENTO 1: Buscar en la lista cargada (Plan A)
                    const pReal = pedidosCargados.find(item => String(item.id) === String(idPedidoPendiente));
                    
                    if (pReal && pReal.status === 'Entregada') {
                        // Si está en la lista (raro, pero posible), lanzamos
                        lanzarModalConDelay(pReal);
                    } else {
                        // INTENTO 2: PLAN B (Búsqueda Individual)
                        // Como PHP filtró el pedido "Entregada", pedimos sus datos específicamente
                        fetch(`php/editor.php?id=${idPedidoPendiente}`)
                            .then(r => r.json())
                            .then(d => {
                                if (d.success && d.pedido && d.pedido.status === 'Entregada') {
                                    // ¡Lo encontramos individualmente! Lanzamos notificación
                                    lanzarModalConDelay(d.pedido);
                                }
                            });
                    }
                }
                // =====================================================================

            } else {
                pedidosCargados = [];
                resultadosDiv.innerHTML = '<p class="text-center py-4">Error al cargar datos.</p>';
            }
        });
}

// Función auxiliar para esperar al modal verde
function lanzarModalConDelay(pedido) {
    setTimeout(() => {
        if (typeof dispararNotificacionWhatsApp === 'function') {
            dispararNotificacionWhatsApp(pedido);
        }
        idPedidoPendiente = null; // Limpiamos variable
    }, 2200);
}

// --- GESTIÓN DE TALLAS ---
function addTallaEntry(talla = '', cantidad = 1, color = '#000000', prendaId = '', isCopy = false) {
    const tallasContainer = document.getElementById('tallasContainer');
    
    // Copiar datos de la última fila si es necesario
    if (isCopy === true && talla === '' && prendaId === '') {
        const filas = tallasContainer.querySelectorAll('.talla-entry');
        if (filas.length > 0) {
            const ultimaFila = filas[filas.length - 1];
            prendaId = ultimaFila.querySelector('select[name="prenda_id[]"]').value;
            talla = ultimaFila.querySelector('select[name="talla[]"]').value;
            color = ultimaFila.querySelector('input[name="color[]"]').value;
            cantidad = 1;
        }
    }
    
    const div = document.createElement('div');
    div.className = 'talla-entry d-flex align-items-center gap-2 mb-2 bg-light p-2 rounded';
    
    // --- GENERACIÓN DE OPCIONES (CON DESC) ---
    let opts = '<option value="">-- Prenda --</option>';
    
    // Ordenar alfabéticamente
    const catalogo = [...(window.catalogoPrendas || [])].sort((a, b) => a.tipo_prenda.localeCompare(b.tipo_prenda));

    catalogo.forEach(p => {
        const selected = (p.id == prendaId) ? 'selected' : '';
        // Validamos si existe descripción
        const descripcionTexto = p.descripcion ? ` - ${p.descripcion}` : '';
        
        // Construimos la opción con la descripción
        opts += `<option value="${p.id}" ${selected}>${p.tipo_prenda} ${p.marca} (${p.modelo})${descripcionTexto}</option>`;
    });

    // --- LISTADO DE TALLAS ACTUALIZADO ---
    const todasLasTallas = [
        '2-4 Años', '4-6 Años', '6-8 Años', '8-10 Años', '10-12 Años', // Niño
       // 'XS Juv', 'S Juv', 'M Juv', 'L Juv', 'XL Juv',                // Juvenil
        'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'                       // Adulto
    ];
    
    let tallasOpts = '<option value="">-- Talla --</option>';
    todasLasTallas.forEach(t => {
        const selected = (t === talla) ? 'selected' : '';
        tallasOpts += `<option value="${t}" ${selected}>${t}</option>`;
    });

    // HTML Final
    div.innerHTML = `
        <select class="form-select form-select-sm" name="prenda_id[]" style="flex:2" required>${opts}</select>
        <select class="form-select form-select-sm" name="talla[]" style="flex:1" required>${tallasOpts}</select>
        <input type="number" class="form-control form-control-sm" name="cantidad[]" value="${cantidad}" min="1" style="width:70px">
        <input type="color" class="form-control form-control-color border-0" name="color[]" value="${color}" style="width:40px">
        <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>`;
    
    tallasContainer.appendChild(div);
    
    // Listeners
    div.querySelector('input[name="cantidad[]"]').addEventListener('input', () => {
        if (typeof calcularTotalPiezas === 'function') calcularTotalPiezas();
    });
    
    if (typeof calcularTotalPiezas === 'function') calcularTotalPiezas();
}