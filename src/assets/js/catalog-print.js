/**
 * catalog-print.js
 * Funciones para generar la lista de compra, imprimir y manejar el catálogo
 */

// --- 1. FUNCIÓN PARA IMPRIMIR ---
function imprimirListaProfesional() {
    const fecha = new Date().toLocaleDateString('es-MX', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    const tablaContenido = document.getElementById('listaCompraContent').innerHTML;
    const ventana = window.open('', 'PRINT', 'height=600,width=800');

    ventana.document.write(`
        <html>
        <head>
            <title>Lista de Compra - Tinta Negra</title>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .logo { max-width: 150px; margin-bottom: 10px; }
                h1 { font-size: 24px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
                .meta { font-size: 12px; color: #666; margin-top: 5px; }
                
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
                th { background-color: #f8f9fa; border-bottom: 2px solid #333; padding: 12px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 12px; }
                td { border-bottom: 1px solid #ddd; padding: 10px 12px; vertical-align: middle; }
                
                /* --- ESTILOS PARA EL TOTAL (NUEVO) --- */
                tfoot tr { background-color: #f0f0f0; border-top: 2px solid #333; }
                tfoot td { font-size: 1.1em; padding: 15px 12px; font-weight: bold; }

                /* Estilos para impresión de colores */
                .rounded-circle {
                    display: inline-block !important;
                    width: 18px !important;
                    height: 18px !important;
                    border-radius: 50% !important;
                    border: 1px solid #999 !important;
                    margin-right: 5px;
                    vertical-align: middle;
                }
                @media print {
                    .rounded-circle {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    /* Forzar fondo gris del total en impresión */
                    tfoot tr {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        background-color: #f0f0f0 !important;
                    }
                }
                
                .text-end { text-align: right; }
                .text-center { text-align: center; }
                .fw-bold { font-weight: bold; }
                .text-primary { color: #000; font-weight: 900; }
                .text-muted { color: #555; }
                .small { font-size: 0.85em; }
                .d-flex { display: flex; align-items: center; }
                
                .badge { border: 1px solid #ccc; padding: 2px 5px; border-radius: 4px; font-size: 0.8em; }
                .footer { margin-top: 40px; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="assets/images/tintanegra-black.png" class="logo" alt="Tinta Negra" onerror="this.style.display='none'">
                <h1>Lista de Compra Consolidada</h1>
                <div class="meta">Generado el: ${fecha}</div>
            </div>
            ${tablaContenido}
            <div class="footer">Documento de uso interno - Tinta Negra Panel Administrativo</div>
        </body>
        </html>
    `);

    ventana.document.close(); 
    ventana.focus(); 
    setTimeout(() => { ventana.print(); ventana.close(); }, 500);
}

// --- NUEVA FUNCIÓN AUXILIAR: CALCULAR TOTAL ---
function actualizarTotalVisual() {
    const tbody = document.querySelector('#tablaResumenCompra tbody');
    const celdaTotal = document.getElementById('celdaTotalCompra');
    
    if (!tbody || !celdaTotal) return;

    let sumaTotal = 0;
    // Recorremos la columna 3 (donde está la cantidad)
    tbody.querySelectorAll('tr').forEach(row => {
        const cantidadTexto = row.cells[3].innerText;
        const cantidad = parseInt(cantidadTexto) || 0;
        sumaTotal += cantidad;
    });

    celdaTotal.innerText = sumaTotal;
}




// --- 2. FUNCIÓN GENERAR TABLA ---
function generarResumenCompra(pedidosCargados) {
    const seleccionados = Array.from(document.querySelectorAll('.check-pedido:checked')).map(cb => cb.value);
    if (seleccionados.length === 0) { alert("Selecciona al menos un pedido."); return; }

    const pedidosFiltrados = pedidosCargados.filter(p => seleccionados.includes(p.id.toString()));
    const consolidado = {};

    pedidosFiltrados.forEach(p => {
        if (p.tallas) {
            let tallasArray = p.tallas;
            if (typeof tallasArray === 'string') { try { tallasArray = JSON.parse(tallasArray); } catch(e) { tallasArray = []; } }

            if(Array.isArray(tallasArray)) {
                tallasArray.forEach(t => {
                    const clave = `${t.prenda_id}_${t.talla}_${t.color}`;
                    
                    if (!consolidado[clave]) {
                        // Reconstrucción de nombre para estandarizar
                        let nombreEstandarizado = null;
                        if (window.catalogoPrendas) {
                            const cat = window.catalogoPrendas.find(x => x.id == t.prenda_id);
                            if (cat) {
                                const desc = cat.descripcion ? ` - ${cat.descripcion}` : '';
                                nombreEstandarizado = `${cat.tipo_prenda} ${cat.marca} (${cat.modelo})${desc}`;
                            }
                        }
                        if (!nombreEstandarizado) nombreEstandarizado = t.nombre_prenda || 'Prenda Desconocida';

                        consolidado[clave] = {
                            nombre: nombreEstandarizado, 
                            talla: t.talla,
                            color: t.color,
                            cantidad: 0
                        };
                    }
                    consolidado[clave].cantidad += parseInt(t.cantidad || 0);
                });
            }
        }
    });

    let html = `<div class="table-responsive">
        <table class="table table-bordered align-middle text-center" id="tablaResumenCompra">
        <thead class="table-dark">
            <tr>
                <th style="text-align: left;">Prenda / Descripción</th>
                <th style="text-align: left;">Color</th> 
                <th>Talla</th>
                <th>Total</th>
                <th style="width: 50px;">Check</th>
            </tr>
        </thead>
        <tbody>`;
    
    const listaItems = Object.values(consolidado).sort((a,b) => a.nombre.localeCompare(b.nombre));

    if (listaItems.length === 0) {
        html += `<tr><td colspan="5" class="py-4 text-muted">No hay prendas registradas.</td></tr>`;
    } else {
        listaItems.forEach(item => {
            const nombreColor = (typeof obtenerNombreColor === 'function') ? obtenerNombreColor(item.color) : item.color;
            html += `<tr>
                <td class="fw-bold" style="text-align: left;">${item.nombre}</td>
                <td style="text-align: left;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle border shadow-sm" style="width:25px; height:25px; background-color:${item.color};"></span>
                        <span class="small fw-bold text-muted">${nombreColor}</span>
                    </div>
                </td>
                <td><span class="badge bg-secondary fs-6">${item.talla}</span></td>
                <td class="fw-bold fs-5 text-primary">${item.cantidad}</td>
                <td class="text-center"><div style="width:20px; height:20px; border:2px solid #ccc; display:inline-block; border-radius: 4px;"></div></td>
            </tr>`;
        });
    }
    
    // --- AGREGAMOS EL PIE DE TABLA (TOTAL) ---
    html += `</tbody>
        <tfoot class="bg-light">
            <tr>
                <td colspan="3" class="text-end fw-bold text-uppercase">Total Piezas:</td>
                <td class="fw-bold fs-4 text-dark" id="celdaTotalCompra">0</td>
                <td></td>
            </tr>
        </tfoot>
    </table></div>`;
    
    document.getElementById('listaCompraContent').innerHTML = html;
    
    // Calculamos total inicial
    actualizarTotalVisual();
    
    const modalEl = document.getElementById('modalListaCompra');
    if(modalEl) new bootstrap.Modal(modalEl).show();
}






// --- 3. RECARGAR CATÁLOGO ---
function recargarCatalogoAjax() {
    fetch('php/catalog_management.php?accion=listar')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                window.catalogoPrendas = data.catalogo || [];
                const tbody = document.getElementById('listaCatalogo');
                if(tbody) {
                    tbody.innerHTML = '';
                    data.catalogo.forEach(r => {
                        const row = document.createElement('tr');
                        row.id = `prenda-${r.id}`;
                        const precio = (typeof fmtMoney === 'function') ? fmtMoney(parseFloat(r.costo_base)) : r.costo_base;
                        row.innerHTML = `<td>${r.tipo_prenda}</td><td>${r.marca}</td><td>${r.modelo}</td>
                            <td class="text-muted small"><em>${r.descripcion || ''}</em></td>
                            <td><span class="badge-catalogo">${r.genero}</span></td><td>${precio}</td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0 btn-eliminar-prenda" data-id="${r.id}"><i class="bx bx-trash"></i></button></td>`;
                        tbody.appendChild(row);
                    });
                }
                document.querySelectorAll('select[name="prenda_id[]"]').forEach(select => {
                    const val = select.value;
                    let opts = `<option value="">-- Prenda --</option>`;
                    [...window.catalogoPrendas].sort((a,b)=>a.tipo_prenda.localeCompare(b.tipo_prenda)).forEach(p => {
                        const sel = (p.id == val) ? 'selected' : '';
                        const desc = p.descripcion ? ` - ${p.descripcion}` : '';
                        opts += `<option value="${p.id}" ${sel}>${p.tipo_prenda} ${p.marca} (${p.modelo})${desc}</option>`;
                    });
                    select.innerHTML = opts; select.value = val;
                });
            }
        });
}

// --- 4. LÓGICA DE EXTRAS Y SUMA ---
document.addEventListener('DOMContentLoaded', () => {
    const extraSelect = document.getElementById('extraPrendaSelect');
    if (extraSelect && window.catalogoPrendas) {
        const sortedCatalog = [...window.catalogoPrendas].sort((a, b) => a.tipo_prenda.localeCompare(b.tipo_prenda));
        sortedCatalog.forEach(p => {
            const option = document.createElement('option');
            const desc = p.descripcion ? ` - ${p.descripcion}` : '';
            const textoCompleto = `${p.tipo_prenda} ${p.marca} (${p.modelo})${desc}`;
            option.value = textoCompleto;
            option.innerText = textoCompleto;
            extraSelect.appendChild(option);
        });
    }

    const btnAdd = document.getElementById('btnAddExtraItem');
    if (btnAdd) {
        btnAdd.addEventListener('click', () => {
            const prendaNombre = document.getElementById('extraPrendaSelect').value;
            const talla = document.getElementById('extraTallaSelect').value;
            const cantidadInput = document.getElementById('extraCantidad');
            const cantidad = parseInt(cantidadInput.value);
            const colorHex = document.getElementById('extraColorInput').value;

            if (!prendaNombre || !talla || isNaN(cantidad) || cantidad < 1) { alert("Verifique los datos"); return; }

            const container = document.getElementById('listaCompraContent');
            let tbody = container.querySelector('table tbody');
            
            // Si la tabla no existe, la creamos CON EL FOOTER
            if (!tbody) {
                container.innerHTML = `<div class="table-responsive"><table class="table table-bordered align-middle text-center" id="tablaResumenCompra">
                    <thead class="table-dark"><tr>
                        <th style="text-align: left;">Prenda / Descripción</th>
                        <th style="text-align: left;">Color</th><th>Talla</th><th>Total</th><th style="width: 50px;">Check</th>
                    </tr></thead>
                    <tbody></tbody>
                    <tfoot class="bg-light"><tr><td colspan="3" class="text-end fw-bold text-uppercase">Total Piezas:</td><td class="fw-bold fs-4 text-dark" id="celdaTotalCompra">0</td><td></td></tr></tfoot>
                    </table></div>`;
                tbody = container.querySelector('tbody');
            }

            let nombreColor = colorHex;
            if (typeof obtenerNombreColor === 'function') nombreColor = obtenerNombreColor(colorHex);

            let filaEncontrada = null;
            tbody.querySelectorAll('tr').forEach(row => {
                const txtPrenda = row.cells[0].textContent
                                     .replace(/EXTRA/g, '').replace(/\+/g, '') // Corrección de reemplazo global
                                     .replace(/\s+/g, ' ').trim();
                const txtColor = row.cells[1].textContent.trim();
                const txtTalla = row.cells[2].textContent.trim();

                if (txtPrenda === prendaNombre.trim() && txtColor === nombreColor && txtTalla === talla) {
                    filaEncontrada = row;
                }
            });

            if (filaEncontrada) {
                const celda = filaEncontrada.cells[3];
                celda.innerText = parseInt(celda.innerText) + cantidad;
                celda.classList.add('text-primary');
                
                if (!filaEncontrada.cells[0].innerHTML.includes('bg-warning')) {
                    filaEncontrada.cells[0].innerHTML += ` <span class="badge bg-warning text-dark ms-2" style="font-size:0.65rem;">+EXTRA</span>`;
                }
                
                const bg = filaEncontrada.style.backgroundColor;
                filaEncontrada.style.backgroundColor = '#fff3cd';
                setTimeout(() => filaEncontrada.style.backgroundColor = bg, 500);
            } else {
                const tr = document.createElement('tr');
                tr.className = "bg-warning bg-opacity-10";
                tr.innerHTML = `
                    <td class="fw-bold" style="text-align: left;">${prendaNombre} <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">EXTRA</span></td>
                    <td style="text-align: left;"><div class="d-flex align-items-center gap-2"><span class="rounded-circle border shadow-sm" style="width:25px; height:25px; background-color:${colorHex};"></span><span class="small fw-bold text-muted">${nombreColor}</span></div></td>
                    <td><span class="badge bg-secondary fs-6">${talla}</span></td>
                    <td class="fw-bold fs-5 text-primary">${cantidad}</td>
                    <td class="text-center"><div style="width:20px; height:20px; border:2px solid #ccc; display:inline-block; border-radius: 4px;"></div></td>
                `;
                tbody.appendChild(tr);
            }
            cantidadInput.value = 1;

            // --- ACTUALIZAR TOTAL AL FINAL ---
            actualizarTotalVisual();
        });
    }
});