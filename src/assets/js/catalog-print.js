/**
 * catalog-print.js
 * Funciones para generar la lista de compra, imprimir y manejar el catálogo
 */

function imprimirListaProfesional() {
    // 1. Obtener fecha actual formateada
    const fecha = new Date().toLocaleDateString('es-MX', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    // 2. Obtener el HTML de la tabla
    const tablaContenido = document.getElementById('listaCompraContent').innerHTML;
    
    // 3. Abrir ventana de impresión
    const ventana = window.open('', 'PRINT', 'height=600,width=800');

    // 4. Escribir el documento completo
    ventana.document.write(`
        <html>
        <head>
            <title>Lista de Compra - Tinta Negra</title>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; padding: 20px; }
                
                /* Encabezado */
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .logo { max-width: 150px; margin-bottom: 10px; }
                h1 { font-size: 24px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
                .meta { font-size: 12px; color: #666; margin-top: 5px; }
                
                /* Tabla */
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
                th { background-color: #f8f9fa; border-bottom: 2px solid #333; padding: 12px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 12px; }
                td { border-bottom: 1px solid #ddd; padding: 10px 12px; vertical-align: middle; }
                
                /* ESTILOS CRÍTICOS PARA COLORES */
                .d-flex { display: flex; align-items: center; }
                .gap-2 { gap: 0.5rem; }
                
                .rounded-circle {
                    display: inline-block !important;
                    width: 18px !important;
                    height: 18px !important;
                    border-radius: 50% !important;
                    border: 1px solid #999 !important;
                    margin-right: 5px;
                }
                
                /* Forzar impresión de fondos en Chrome/Edge/Safari */
                @media print {
                    .rounded-circle {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    th {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important; 
                    }
                }
                
                /* Utilidades de Texto */
                .text-end { text-align: right; }
                .text-center { text-align: center; }
                .fw-bold { font-weight: bold; }
                .text-primary { color: #000; font-weight: 900; }
                .text-muted { color: #555; }
                .small { font-size: 0.85em; }
                
                /* Badges (Etiquetas) */
                .badge { 
                    border: 1px solid #ccc; 
                    padding: 2px 6px; 
                    border-radius: 4px; 
                    font-size: 0.8em; 
                    background: #fff;
                    color: #000;
                }

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

            <div class="footer">
                Documento de uso interno - Tinta Negra Panel Administrativo
            </div>
        </body>
        </html>
    `);

    ventana.document.close(); 
    ventana.focus(); 

    // Pequeño delay para asegurar que carguen estilos/imágenes antes de abrir el diálogo
    setTimeout(() => {
        ventana.print();
        ventana.close();
    }, 500);
}




function generarResumenCompra(pedidosCargados) {
    // 1. Identificar qué pedidos están seleccionados
    const seleccionados = Array.from(document.querySelectorAll('.check-pedido:checked')).map(cb => cb.value);
    
    // Si no hay nada seleccionado, no hacemos nada
    if (seleccionados.length === 0) {
        alert("Por favor selecciona al menos un pedido de la lista.");
        return;
    }

    // 2. Filtrar los datos de los pedidos seleccionados
    const pedidosFiltrados = pedidosCargados.filter(p => seleccionados.includes(p.id.toString()));
    const consolidado = {};

    // 3. Iterar y Consolidar (Sumar cantidades)
    pedidosFiltrados.forEach(p => {
        if (p.tallas) { // Verificamos que exista tallas
            // Intentamos parsear si viene como string, si ya es objeto lo usamos directo
            let tallasArray = p.tallas;
            if (typeof tallasArray === 'string') {
                try { tallasArray = JSON.parse(tallasArray); } catch(e) { tallasArray = []; }
            }

            if(Array.isArray(tallasArray)) {
                tallasArray.forEach(t => {
                    // Normalizamos clave única para agrupar
                    const clave = `${t.prenda_id}_${t.talla}_${t.color}`;
                    
                    if (!consolidado[clave]) {
                        // --- CORRECCIÓN CLAVE AQUÍ ---
                        // En lugar de confiar en t.nombre_prenda, intentamos reconstruirlo desde el catálogo actual
                        // para que coincida con el formato de los "Extras".
                        let nombreEstandarizado = null;

                        if (window.catalogoPrendas) {
                            const prendaCat = window.catalogoPrendas.find(x => x.id == t.prenda_id);
                            if (prendaCat) {
                                // Reconstruimos el nombre con el formato ESTÁNDAR
                                const desc = prendaCat.descripcion ? ` - ${prendaCat.descripcion}` : '';
                                nombreEstandarizado = `${prendaCat.tipo_prenda} ${prendaCat.marca} (${prendaCat.modelo})${desc}`;
                            }
                        }

                        // Si no encontramos la prenda en el catálogo actual (quizás se borró), usamos el nombre histórico
                        if (!nombreEstandarizado) {
                            nombreEstandarizado = t.nombre_prenda || 'Prenda Desconocida';
                        }
                        
                        consolidado[clave] = {
                            nombre: nombreEstandarizado, 
                            talla: t.talla,
                            color: t.color,
                            cantidad: 0
                        };
                    }
                    // Sumamos
                    consolidado[clave].cantidad += parseInt(t.cantidad || 0);
                });
            }
        }
    });

    // 4. Generar HTML de la Tabla
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
    
    // Convertimos el objeto a array para ordenar alfabéticamente por nombre
    const listaItems = Object.values(consolidado).sort((a,b) => a.nombre.localeCompare(b.nombre));

    if (listaItems.length === 0) {
        html += `<tr><td colspan="5" class="py-4 text-muted">No hay prendas registradas en los pedidos seleccionados.</td></tr>`;
    } else {
        listaItems.forEach(item => {
            // Obtenemos nombre del color (usando tu función utils.js si existe)
            const nombreColor = (typeof obtenerNombreColor === 'function') ? obtenerNombreColor(item.color) : item.color;
            
            html += `<tr>
                <td class="fw-bold" style="text-align: left;">${item.nombre}</td>
                <td style="text-align: left;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle border shadow-sm" 
                              style="width:25px; height:25px; background-color:${item.color}; display:inline-block;">
                        </span>
                        <span class="small fw-bold text-muted">${nombreColor}</span>
                    </div>
                </td>
                <td><span class="badge bg-secondary fs-6">${item.talla}</span></td>
                <td class="fw-bold fs-5 text-primary">${item.cantidad}</td>
                <td class="text-center">
                    <div style="width:20px; height:20px; border:2px solid #ccc; display:inline-block; border-radius: 4px;"></div>
                </td>
            </tr>`;
        });
    }
    
    html += `</tbody></table></div>`;
    
    // 5. Inyectar y Mostrar Modal
    document.getElementById('listaCompraContent').innerHTML = html;
    
    const modalEl = document.getElementById('modalListaCompra');
    if(modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}






function recargarCatalogoAjax() {
    fetch('php/catalog_management.php?accion=listar')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                // 1. Actualizar la "memoria" global
                window.catalogoPrendas = data.catalogo || [];
                
                // 2. Actualizar la tabla visual dentro del modal de administración (si existe)
                const tbody = document.getElementById('listaCatalogo');
                if(tbody) {
                    tbody.innerHTML = '';
                    data.catalogo.forEach(r => {
                        const row = document.createElement('tr');
                        row.id = `prenda-${r.id}`;
                        
                        // Formateo de precio seguro
                        const costoFmt = (typeof fmtMoney === 'function') 
                                         ? fmtMoney(parseFloat(r.costo_base)) 
                                         : `$${parseFloat(r.costo_base).toFixed(2)}`;

                        row.innerHTML = `
                            <td>${r.tipo_prenda}</td>
                            <td>${r.marca}</td>
                            <td>${r.modelo}</td>
                            <td class="text-muted small"><em>${r.descripcion || ''}</em></td>
                            <td><span class="badge-catalogo">${r.genero}</span></td>
                            <td>${costoFmt}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-eliminar-prenda" data-id="${r.id}"><i class="bx bx-trash"></i></button>
                            </td>`;
                        tbody.appendChild(row);
                    });
                }
                
                // 3. Actualizar todos los SELECTS de prendas que ya estén visibles en el formulario
                const selectsExistentes = document.querySelectorAll('select[name="prenda_id[]"]');
                selectsExistentes.forEach(select => {
                    const valPrev = select.value; // Guardamos selección actual
                    let htmlOpts = `<option value="">-- Prenda --</option>`;
                    
                    // Reconstruimos las opciones ordenadas
                    [...window.catalogoPrendas].sort((a, b) => a.tipo_prenda.localeCompare(b.tipo_prenda)).forEach(p => {
                        const selected = (p.id == valPrev) ? 'selected' : '';
                        const desc = p.descripcion ? ` - ${p.descripcion}` : '';
                        htmlOpts += `<option value="${p.id}" ${selected}>${p.tipo_prenda} ${p.marca} (${p.modelo})${desc}</option>`;
                    });
                    
                    select.innerHTML = htmlOpts;
                    select.value = valPrev; // Restauramos selección
                });
            }
        });
}

// --- LÓGICA EXTRAS Y SUMA INTELIGENTE ---
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Llenar el Select de Prendas Extras (al abrir la página)
    const extraSelect = document.getElementById('extraPrendaSelect');
    if (extraSelect && window.catalogoPrendas) {
        const sortedCatalog = [...window.catalogoPrendas].sort((a, b) => a.tipo_prenda.localeCompare(b.tipo_prenda));
        sortedCatalog.forEach(p => {
            const option = document.createElement('option');
            const desc = p.descripcion ? ` - ${p.descripcion}` : '';
            // El valor será el texto completo para facilitar la inserción en la tabla
            option.value = `${p.tipo_prenda} ${p.marca} (${p.modelo})${desc}`;
            option.innerText = option.value;
            extraSelect.appendChild(option);
        });
    }

    // 2. Botón "Agregar" con Lógica de Suma
    const btnAdd = document.getElementById('btnAddExtraItem');
    
    if (btnAdd) {
        btnAdd.addEventListener('click', () => {
            // Obtener valores del formulario
            const prendaNombre = document.getElementById('extraPrendaSelect').value;
            const talla = document.getElementById('extraTallaSelect').value;
            const cantidadInput = document.getElementById('extraCantidad');
            const cantidad = parseInt(cantidadInput.value);
            const colorHex = document.getElementById('extraColorInput').value;

            // Validaciones básicas
            if (!prendaNombre) { alert("Selecciona una prenda"); return; }
            if (!talla) { alert("Selecciona una talla"); return; }
            if (isNaN(cantidad) || cantidad < 1) { alert("Cantidad inválida"); return; }

            // Referencia a la tabla
            const container = document.getElementById('listaCompraContent');
            let tbody = container.querySelector('table tbody');

            // Si la tabla no existe (caso raro), la creamos
            if (!tbody) {
                container.innerHTML = `<div class="table-responsive"><table class="table table-bordered align-middle text-center" id="tablaResumenCompra">
                    <thead class="table-dark"><tr>
                        <th style="text-align: left;">Prenda / Descripción</th>
                        <th style="text-align: left;">Color</th><th>Talla</th><th>Total</th><th style="width: 50px;">Check</th>
                    </tr></thead><tbody></tbody></table></div>`;
                tbody = container.querySelector('tbody');
            }

            // Obtener nombre del color
            let nombreColor = colorHex;
            if (typeof obtenerNombreColor === 'function') nombreColor = obtenerNombreColor(colorHex);

            // --- AQUÍ EMPIEZA LA BÚSQUEDA ---
            let filaEncontrada = null;
            const filas = tbody.querySelectorAll('tr');

            filas.forEach(row => {
                // Limpieza agresiva de texto para comparar
                // 1. Prenda: Quitamos la palabra "EXTRA" y cualquier badge HTML
                const textoPrendaRow = row.cells[0].textContent
                                          .replace(/EXTRA/g, '').replace(/\+/g, '')
                                          .replace(/\s+/g, ' ') // Eliminar dobles espacios
                                          .trim();
                
                // 2. Color: El texto está visible en la celda
                const textoColorRow = row.cells[1].textContent.trim();
                
                // 3. Talla
                const textoTallaRow = row.cells[2].textContent.trim();

                // Comparación exacta
                if (textoPrendaRow === prendaNombre.trim() && 
                    textoColorRow === nombreColor && 
                    textoTallaRow === talla) {
                    filaEncontrada = row;
                }
            });

            if (filaEncontrada) {
                // --- ESCENARIO A: YA EXISTE -> SUMAR ---
                const celdaCant = filaEncontrada.cells[3];
                const nuevaCant = parseInt(celdaCant.innerText) + cantidad;
                
                // Actualizar número
                celdaCant.innerText = nuevaCant;
                celdaCant.classList.add('text-primary'); // Ponerlo azul para resaltar

                // Agregar etiqueta visual "+EXTRA" si no la tiene
                const celdaNombre = filaEncontrada.cells[0];
                if (!celdaNombre.innerHTML.includes('badge bg-warning')) {
                    // Solo agregamos el badge si no existe ya
                    celdaNombre.innerHTML += ` <span class="badge bg-warning text-dark ms-2" style="font-size:0.65rem;">+EXTRA</span>`;
                }

                // Efecto visual (flash amarillo)
                filaEncontrada.style.transition = 'background-color 0.3s';
                const originalBg = filaEncontrada.style.backgroundColor;
                filaEncontrada.style.backgroundColor = '#fff3cd'; // Flash
                setTimeout(() => filaEncontrada.style.backgroundColor = originalBg, 600);

            } else {
                // --- ESCENARIO B: NO EXISTE -> CREAR NUEVA FILA ---
                const tr = document.createElement('tr');
                tr.className = "bg-warning bg-opacity-10"; // Fondo amarillo suave
                tr.innerHTML = `
                    <td class="fw-bold" style="text-align: left;">
                        ${prendaNombre} <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">EXTRA</span>
                    </td>
                    <td style="text-align: left;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle border shadow-sm" 
                                  style="width:25px; height:25px; background-color:${colorHex}; display:inline-block;">
                            </span>
                            <span class="small fw-bold text-muted">${nombreColor}</span>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary fs-6">${talla}</span></td>
                    <td class="fw-bold fs-5 text-primary">${cantidad}</td>
                    <td class="text-center"><div style="width:20px; height:20px; border:2px solid #ccc; display:inline-block; border-radius: 4px;"></div></td>
                `;
                tbody.appendChild(tr);
            }

            // Resetear cantidad a 1 para la siguiente
            cantidadInput.value = 1;
        });
    }
});