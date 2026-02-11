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
                
                /* --- ESTO ARREGLA LOS CÍRCULOS INVISIBLES --- */
                /* Forzamos que los spans dentro de la tabla tengan tamaño y forma */
                td span[style*="background-color"] {
                    display: inline-block !important;
                    width: 20px !important; 
                    height: 20px !important;
                    border-radius: 50% !important;
                    border: 1px solid #999 !important;
                    vertical-align: middle;
                    margin-right: 8px;
                }
                
                /* Estilos auxiliares para el texto del color */
                .small { font-size: 0.9em; }
                .text-muted { color: #555; }
                .fw-bold { font-weight: bold; }
                .text-primary { color: #000; font-weight: 900; } /* En negro para imprimir mejor */
                
                /* Ocultamos cosas de bootstrap que no sirven aquí */
                .d-flex { display: block; } 
                .badge { border: 1px solid #333; padding: 2px 6px; border-radius: 4px; }

                .footer { margin-top: 40px; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="assets/images/tintanegra-black.png" class="logo" alt="Tinta Negra">
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

    setTimeout(() => {
        ventana.print();
        ventana.close();
    }, 500);
}


function generarResumenCompra() {
    const seleccionados = Array.from(document.querySelectorAll('.check-pedido:checked')).map(cb => cb.value);
    if (seleccionados.length === 0) return;

    const pedidosFiltrados = pedidosCargados.filter(p => seleccionados.includes(p.id.toString()));
    const consolidado = {};

    pedidosFiltrados.forEach(p => {
        if (p.tallas && p.tallas.length > 0) {
            p.tallas.forEach(t => {
                const clave = `${t.prenda_id}_${t.talla}_${t.color}`;
                if (!consolidado[clave]) {
                    consolidado[clave] = {
                        nombre: t.nombre_prenda || 'Prenda Desconocida',
                        talla: t.talla,
                        color: t.color,
                        cantidad: 0
                    };
                }
                consolidado[clave].cantidad += parseInt(t.cantidad);
            });
        }
    });

    let html = `<div class="table-responsive"><table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th style="text-align: left;">Prenda</th>
                <th style="text-align: left;">Color</th> <th>Talla</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>`;
    
    Object.values(consolidado).sort((a,b) => a.nombre.localeCompare(b.nombre)).forEach(item => {
        const nombreColor = obtenerNombreColor(item.color); // Obtenemos el nombre
        
        html += `<tr>
            <td class="fw-bold" style="text-align: left;">${item.nombre}</td>
            <td style="text-align: left;">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-block border rounded-circle shadow-sm" 
                          style="width:25px; height:25px; background-color:${item.color}; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    </span>
                    <span class="small fw-bold text-muted">${nombreColor}</span>
                </div>
            </td>
            <td><span class="badge bg-secondary fs-6">${item.talla}</span></td>
            <td class="fw-bold fs-5 text-primary">${item.cantidad}</td>
        </tr>`;
    });
    
    html += `</tbody></table></div>`;
    document.getElementById('listaCompraContent').innerHTML = html;
    
    const modalEl = document.getElementById('modalListaCompra');
    if(modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}


function imprimirListaProfesional() {
    // 1. Obtener la fecha actual formateada
    const fecha = new Date().toLocaleDateString('es-MX', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    // 2. Obtener el contenido de la tabla generada
    const tablaContenido = document.getElementById('listaCompraContent').innerHTML;

    // 3. Crear una ventana nueva al vuelo
    const ventana = window.open('', 'PRINT', 'height=600,width=800');

    // 4. Escribir el HTML profesional
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
                
                /* Estilos de la Tabla Profesional */
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
                th { background-color: #f8f9fa; border-bottom: 2px solid #333; padding: 12px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 12px; }
                td { border-bottom: 1px solid #ddd; padding: 10px 12px; vertical-align: middle; }
                
                /* Alineación específica */
                .text-end { text-align: right; }
                .text-center { text-align: center; }
                .fw-bold { font-weight: bold; }
                .fs-5 { font-size: 1.1rem; }
                
                /* Ajuste para los colores al imprimir */
                .color-circle {
                    display: inline-block; width: 20px; height: 20px; border-radius: 50%; border: 1px solid #999;
                    -webkit-print-color-adjust: exact; print-color-adjust: exact;
                }
                
                /* Pie de página */
                .footer { margin-top: 40px; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="assets/images/tintanegra-black.png" class="logo" alt="Tinta Negra">
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

    // 5. Ajustar el HTML inyectado para que se vea bien (Reemplazamos clases de bootstrap por nuestras clases simples)
    // Esto es un truco para que los círculos de color se impriman bien sin cargar todo Bootstrap
    let htmlLimpio = ventana.document.body.innerHTML;
    
    // Reemplazamos los spans de color para usar la clase .color-circle definida arriba
    // Buscamos el patrón del span original y le inyectamos la clase nueva
    ventana.document.close(); // Cierra el flujo de escritura
    ventana.focus(); // Enfoca la ventana

    // Espera un momento para que cargue la imagen del logo y luego imprime
    setTimeout(() => {
        ventana.print();
        ventana.close();
    }, 500);
}

function recargarCatalogoAjax() {
    fetch('php/catalog_management.php?accion=listar')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                // 1. Actualizar la "memoria" global del catálogo
                window.catalogoPrendas = data.catalogo || [];
                
                // 2. Actualizar la tabla visual dentro del modal (si está abierto)
                const tbody = document.getElementById('listaCatalogo');
                if(tbody) {
                    tbody.innerHTML = '';
                    data.catalogo.forEach(r => {
                        const row = document.createElement('tr');
                        row.id = `prenda-${r.id}`;
                        row.innerHTML = `
                            <td>${r.tipo_prenda}</td>
                            <td>${r.marca}</td>
                            <td>${r.modelo}</td>
                            <td class="text-muted small"><em>${r.descripcion || ''}</em></td>
                            <td><span class="badge-catalogo">${r.genero}</span></td>
                            <td>${fmtMoney(parseFloat(r.costo_base))}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-eliminar-prenda" data-id="${r.id}"><i class="bx bx-trash"></i></button>
                            </td>`;
                        tbody.appendChild(row);
                    });
                }

                // 3. ¡NUEVO! Actualizar los selectores de prendas YA visibles en la pantalla
                // Buscamos todos los selects de prendas que haya en el formulario
                const selectsExistentes = document.querySelectorAll('select[name="prenda_id[]"]');
                
                selectsExistentes.forEach(select => {
                    const valorSeleccionadoPrevio = select.value; // Guardamos lo que tenía seleccionado el usuario
                    
                    // Ordenamos alfabéticamente
                    const listaOrdenada = [...window.catalogoPrendas].sort((a, b) => a.tipo_prenda.localeCompare(b.tipo_prenda));
                    
                    // Reconstruimos las opciones
                    let htmlOpciones = `<option value="">-- Prenda --</option>`;
                    listaOrdenada.forEach(p => {
                        // Si es la prenda que acabamos de agregar o la que ya tenía seleccionada, la marcamos
                        const selected = (p.id == valorSeleccionadoPrevio) ? 'selected' : '';
                        const desc = p.descripcion ? ` - ${p.descripcion}` : '';
                        htmlOpciones += `<option value="${p.id}" ${selected}>${p.tipo_prenda} ${p.marca} (${p.modelo})${desc}</option>`;
                    });
                    
                    // Inyectamos las nuevas opciones
                    select.innerHTML = htmlOpciones;
                    
                    // Restauramos el valor seleccionado (si aún existe)
                    select.value = valorSeleccionadoPrevio;
                });
            }
        });
}