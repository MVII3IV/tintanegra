/**
 * Lógica del Panel Administrativo Tinta Negra
 */

const fmtMoney = (n) => n.toLocaleString("es-MX", { style: "currency", currency: "MXN" });
let pedidosCargados = []; // Almacén global de pedidos

// --- AGREGAR ESTO AL INICIO DEL ARCHIVO (Junto a fmtMoney) ---
const LIMIT_MB = 1; // Límite de 10 MB

// Función para validar peso
function validarPesoArchivo(input) {
    if (input.files && input.files.length > 0) {
        for (const file of input.files) {
            const sizeMB = file.size / (1024 * 1024);
            if (sizeMB > LIMIT_MB) {
                alert(`⚠️ Archivo demasiado grande: "${file.name}"\n\nEl límite es de ${LIMIT_MB} MB.\nTu archivo pesa ${sizeMB.toFixed(2)} MB.`);
                input.value = ''; 
                return false;
            }
        }
    }
    return true;
}

// --- FUNCIONES AUXILIARES ---
function generarLinkWhatsApp(p) {
    if (!p.telefono) return '#'; 
    const telLimpio = p.telefono.replace(/\D/g, '');
    const host = window.location.hostname === 'localhost' ? 'http://localhost:8080' : 'https://www.tintanegra.mx';
    const urlPedido = `${host}/showOrder.php?id=${p.id}`;
    const saldo = (p.status === 'Entregada') ? 0 : (parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0));
    
    const mensaje = `Hola *${p.nombre}*, te saludamos de Tinta Negra.\n\nTu pedido *${p.id}* ha cambiado a: *${p.status.toUpperCase()}*.\n\nSaldo: ${fmtMoney(saldo)}.\nDetalles: ${urlPedido}`;
    return `https://wa.me/52${telLimpio}?text=${encodeURIComponent(mensaje)}`;
}

function calcularTotalPiezas() {
    let total = 0;
    document.querySelectorAll('input[name="cantidad[]"]').forEach(i => total += parseInt(i.value) || 0);
    document.getElementById('totalPiezasAdmin').innerText = total;
}

function actualizarBotonLista() {
    const checks = document.querySelectorAll('.check-pedido:checked');
    const btn = document.getElementById('btnGenerarLista');
    const contador = document.getElementById('contadorSeleccionados');
    
    if (btn) {
        if (checks.length > 0) {
            btn.style.display = 'inline-flex';
            contador.innerText = checks.length;
        } else {
            btn.style.display = 'none';
        }
    }
}

/**
 * Genera una ventana de impresión limpia y profesional
 */
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

// --- LÓGICA PRINCIPAL ---

function cargarPedidos(nombre = '') {
    fetch(`php/getOrderByName.php?nombre=${encodeURIComponent(nombre)}`)
        .then(res => res.json())
        .then(data => {
            let stats = { produccion: 0, entregaHoy: 0, porCobrar: 0, listos: 0 };
            const hoy = new Date().toISOString().split('T')[0];
            const resultadosDiv = document.getElementById('resultados');

            if (data.success && data.pedidos.length > 0) {
                pedidosCargados = data.pedidos;

                let html = `<div class="table-responsive"><table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th style="width: 40px;" class="text-center"><input type="checkbox" class="form-check-input cursor-pointer" id="checkAll"></th>
                            <th>Cliente / Detalle</th>
                            <th>Entrega</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead><tbody>`;
                
                data.pedidos.filter(p => p.status !== 'Entregada').forEach(p => {
                    if (p.status === 'En produccion') stats.produccion++;
                    if (p.status === 'Finalizada') stats.listos++;
                    if (p.fechaEntrega === hoy) stats.entregaHoy++;
                    const saldo = parseFloat(p.costo || 0) - parseFloat(p.anticipo || 0);
                    if (saldo > 0) stats.porCobrar += saldo;

                    let detallePrendas = '';
                    if (p.tallas && p.tallas.length > 0) {
                        detallePrendas = '<div class="mt-1">';
                        p.tallas.forEach(t => {
                            detallePrendas += `<span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 0.7rem; font-weight: 500;">
                                ${t.cantidad}x ${t.nombre_prenda || 'Prenda'} [${t.talla}]
                            </span>`;
                        });
                        detallePrendas += '</div>';
                    }

                    html += `<tr class="bg-white">
                        <td class="text-center"><input type="checkbox" class="form-check-input check-pedido cursor-pointer" value="${p.id}"></td>
                        <td>
                            <a href="showOrder.php?id=${p.id}" class="fw-bold text-dark text-decoration-none">${p.nombre}</a>
                            ${detallePrendas}
                        </td>
                        <td class="small">${p.fechaEntrega}</td>
                        <td class="fw-bold ${saldo > 0 ? 'text-warning' : 'text-success'}">$${saldo.toFixed(2)}</td>
                        <td><span class="badge rounded-pill bg-light text-dark border">${p.status}</span></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-success border-0 btn-wa-preview" data-nombre="${p.nombre}" data-tel="${p.telefono}" data-link="${generarLinkWhatsApp(p)}"><i class="bx bxl-whatsapp"></i></button>
                                <button class="btn btn-sm btn-light border edit-btn" data-id="${p.id}"><i class="bx bx-edit"></i></button>
                                <button class="btn btn-sm btn-light border delete-btn" data-id="${p.id}"><i class="bx bx-trash text-danger"></i></button>
                            </div>
                        </td></tr>`;
                });
                resultadosDiv.innerHTML = html + "</tbody></table></div>";
                
                document.getElementById('stat-produccion').innerText = stats.produccion;
                document.getElementById('stat-entrega').innerText = stats.entregaHoy;
                document.getElementById('stat-finalizados').innerText = stats.listos;
                document.getElementById('stat-cobro').innerText = '$' + stats.porCobrar.toLocaleString('es-MX');
                
                actualizarBotonLista();

            } else {
                pedidosCargados = [];
                resultadosDiv.innerHTML = '<p class="text-center py-4">Sin pedidos activos.</p>';
            }
        });
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

    // Hemos agregado '-webkit-print-color-adjust: exact' y 'print-color-adjust: exact' al span del color
    let html = `<div class="table-responsive"><table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th style="text-align: left;">Prenda</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>`;
    
    Object.values(consolidado).sort((a,b) => a.nombre.localeCompare(b.nombre)).forEach(item => {
        html += `<tr>
            <td class="fw-bold" style="text-align: left;">${item.nombre}</td>
            <td>
                <span class="d-inline-block border rounded-circle shadow-sm" 
                      style="width:25px; height:25px; background-color:${item.color}; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                </span>
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

// --- GESTIÓN DE TALLAS ---

function addTallaEntry(talla = '', cantidad = 1, color = '#000000', prendaId = '', isCopy = false) {
    const tallasContainer = document.getElementById('tallasContainer');
    
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
    
    const listaOrdenada = [...window.catalogoPrendas].sort((a, b) => a.tipo_prenda.localeCompare(b.tipo_prenda));

    let prendasHtml = `<option value="">-- Prenda --</option>`;
    listaOrdenada.forEach(p => {
        const selected = (p.id == prendaId) ? 'selected' : '';
        const desc = p.descripcion ? ` - ${p.descripcion}` : '';
        prendasHtml += `<option value="${p.id}" ${selected}>${p.tipo_prenda} ${p.marca} (${p.modelo})${desc}</option>`;
    });

    const tallasFijas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    let tallasHtml = `<option value="">-- Talla --</option>`;
    tallasFijas.forEach(t => {
        const selected = (t == talla) ? 'selected' : '';
        tallasHtml += `<option value="${t}" ${selected}>${t}</option>`;
    });

    div.innerHTML = `
        <select class="form-select form-select-sm" name="prenda_id[]" required style="flex: 2;">${prendasHtml}</select>
        <select class="form-select form-select-sm" name="talla[]" required style="flex: 1;">${tallasHtml}</select>
        <input type="number" class="form-control form-control-sm" name="cantidad[]" value="${cantidad}" min="1" style="width: 70px;">
        <input type="color" class="form-control form-control-color border-0" name="color[]" value="${color}" style="width: 40px;">
        <button type="button" class="btn btn-danger btn-sm remove-talla"><i class="bx bx-trash"></i></button>`;
    
    tallasContainer.appendChild(div);
    div.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotalPiezas);
    calcularTotalPiezas();
}

function recargarCatalogoAjax() {
    fetch('php/catalog_management.php?accion=listar')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                window.catalogoPrendas = data.catalogo;
                const tbody = document.getElementById('listaCatalogo');
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
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-eliminar-prenda" data-id="${r.id}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>`;
                    tbody.appendChild(row);
                });
            }
        });
}

// --- INICIALIZACIÓN ---

document.addEventListener('DOMContentLoaded', () => {
    const initModal = (id) => { const el = document.getElementById(id); return el ? new bootstrap.Modal(el) : null; };

    const waModal = initModal('waModal');
    const deleteModal = initModal('deleteModal');
    const confirmUpdateModal = initModal('confirmUpdateModal');
    const catalogoModal = initModal('modalCatalogo');
    let idToDelete = null;

    cargarPedidos();
    addTallaEntry();

    document.getElementById('addTalla').addEventListener('click', () => addTallaEntry('', 1, '#000000', '', true));
    document.getElementById('buscadorNombre').addEventListener('input', (e) => cargarPedidos(e.target.value));

    const btnLista = document.getElementById('btnGenerarLista');
    if(btnLista) btnLista.addEventListener('click', generarResumenCompra);

    document.addEventListener('click', (e) => {
        const btnWA = e.target.closest('.btn-wa-preview');
        if (btnWA) {
            const link = btnWA.getAttribute('data-link');
            const urlObj = new URL(link);
            document.getElementById('wa-destinatario').innerText = btnWA.getAttribute('data-nombre');
            document.getElementById('wa-mensaje-pre').value = decodeURIComponent(urlObj.searchParams.get("text"));
            document.getElementById('wa-confirmar-link').dataset.tel = btnWA.getAttribute('data-tel').replace(/\D/g, '');
            if(waModal) waModal.show();
        }

        const btnEdit = e.target.closest('.edit-btn');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            fetch(`php/editor.php?id=${id}`).then(res => res.json()).then(data => {
                if (data.success) {
                    const p = data.pedido;
                    document.getElementById('pedidoId').value = p.id;
                    document.getElementById('nombrePedido').value = p.nombre;
                    document.getElementById('telefono').value = p.telefono || '';
                    document.getElementById('status').value = p.status;
                    document.getElementById('fechaInicio').value = p.fechaInicio;
                    document.getElementById('fechaEntrega').value = p.fechaEntrega;
                    document.getElementById('costo').value = p.costo;
                    document.getElementById('anticipo').value = p.anticipo;
                    document.getElementById('instrucciones').value = p.instrucciones || '';
                    
                    const tallasContainer = document.getElementById('tallasContainer');
                    tallasContainer.innerHTML = '';
                    if (p.tallas && p.tallas.length > 0) {
                        p.tallas.forEach(t => {
                            addTallaEntry(t.talla, t.cantidad, t.color, t.prenda_id, false);
                        });
                    }
                    
                    document.getElementById('formHeader').innerText = "Editando Pedido #" + p.id;
                    document.getElementById('submitButton').innerText = "Actualizar Pedido";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }

        if (e.target.closest('.delete-btn')) {
            idToDelete = e.target.closest('.delete-btn').getAttribute('data-id');
            if(deleteModal) deleteModal.show();
        }

        if (e.target.closest('.remove-talla')) {
            e.target.closest('.talla-entry').remove();
            calcularTotalPiezas();
        }

        if (e.target.closest('.btn-eliminar-prenda')) {
            const cid = e.target.closest('.btn-eliminar-prenda').getAttribute('data-id');
            if(confirm('¿Eliminar esta prenda del catálogo?')) {
                const fd = new FormData(); fd.append('accion', 'eliminar'); fd.append('id', cid);
                fetch('php/catalog_management.php', { method: 'POST', body: fd })
                .then(r => r.json()).then(d => { 
                    if(d.success) {
                        document.getElementById(`prenda-${cid}`).remove();
                        recargarCatalogoAjax(); 
                    }
                });
            }
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.id === 'checkAll') {
            const checkboxes = document.querySelectorAll('.check-pedido');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            actualizarBotonLista();
        }
        if (e.target.classList.contains('check-pedido')) {
            actualizarBotonLista();
        }
    });

    const formPedido = document.getElementById('pedidoForm');
    if (formPedido) {
        formPedido.addEventListener('submit', function(e) {
            const id = document.getElementById('pedidoId').value;
            if (id && confirmUpdateModal) {
                e.preventDefault();
                confirmUpdateModal.show();
            }
        });
    }

    const btnConfirmar = document.getElementById('btnConfirmarUpdate');
    if(btnConfirmar) {
        btnConfirmar.addEventListener('click', function() { formPedido.submit(); });
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        fetch(`php/deleteOrder.php?id=${idToDelete}`, { method: 'DELETE' })
        .then(res => res.json()).then(data => { 
            if (data.success) { 
                if(deleteModal) deleteModal.hide(); 
                cargarPedidos(); 
            } 
        });
    });

    document.getElementById('formNuevaPrenda').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const fd = new FormData(form);
        fd.append('accion', 'guardar');
        
        fetch('php/catalog_management.php', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => { 
            if(d.success) {
                form.reset();
                recargarCatalogoAjax();
            } else {
                alert('Error: ' + d.error);
            }
        });
    });
    
    const btnWaLink = document.getElementById('wa-confirmar-link');
    if(btnWaLink) {
        btnWaLink.addEventListener('mousedown', function() {
            const tel = this.dataset.tel;
            const msj = document.getElementById('wa-mensaje-pre').value;
            this.href = `https://wa.me/52${tel}?text=${encodeURIComponent(msj)}`;
        });
    }
    
});

/**
 * NUEVA FUNCIÓN: Intenta adivinar el nombre del color
 */
/**
 * NUEVA FUNCIÓN INTELIGENTE: Encuentra el color conocido más cercano
 */
function obtenerNombreColor(hexInput) {
    // Si no hay input o es inválido, regresamos tal cual
    if (!hexInput || !hexInput.startsWith('#')) return hexInput;

    // 1. Función interna para convertir Hex a RGB
    const hexToRgb = (hex) => {
        const bigint = parseInt(hex.slice(1), 16);
        return { r: (bigint >> 16) & 255, g: (bigint >> 8) & 255, b: bigint & 255 };
    };

    // 2. Paleta Maestra de Colores (Agrega aquí todos los que quieras reconocer)
    const baseColors = [
        { hex: "#000000", name: "Negro" },
        { hex: "#ffffff", name: "Blanco" },
        { hex: "#ff0000", name: "Rojo" },
        { hex: "#dc143c", name: "Rojo Carmesí" },
        { hex: "#800000", name: "Vino" },
        { hex: "#0000ff", name: "Azul Rey" },
        { hex: "#000080", name: "Azul Marino" },
        { hex: "#87ceeb", name: "Azul Cielo" },
        { hex: "#ffff00", name: "Amarillo" },
        { hex: "#008000", name: "Verde" },
        { hex: "#006400", name: "Verde Botella" },
        { hex: "#808080", name: "Gris" },
        { hex: "#d3d3d3", name: "Gris Jaspe" }, // Gris claro común en playeras
        { hex: "#ffa500", name: "Naranja" },
        { hex: "#800080", name: "Morado" },
        { hex: "#ffc0cb", name: "Rosa" },
        { hex: "#ff1493", name: "Rosa Mexicano" }, // Fucsia
        { hex: "#f5f5dc", name: "Beige" },
        { hex: "#a52a2a", name: "Café" },
        { hex: "#40e0d0", name: "Turquesa" }
    ];

    // 3. Algoritmo de distancia (Busca el "vecino" más cercano)
    const inputRgb = hexToRgb(hexInput);
    let closestName = hexInput;
    let minDistance = Infinity;

    baseColors.forEach(base => {
        const baseRgb = hexToRgb(base.hex);
        // Distancia Euclidiana: Raíz cuadrada de la suma de las diferencias al cuadrado
        const dist = Math.sqrt(
            Math.pow(inputRgb.r - baseRgb.r, 2) +
            Math.pow(inputRgb.g - baseRgb.g, 2) +
            Math.pow(inputRgb.b - baseRgb.b, 2)
        );

        if (dist < minDistance) {
            minDistance = dist;
            closestName = base.name;
        }
    });

    // Opcional: Si quieres mostrar también el código original para referencia
    // return `${closestName} <span style="font-size:0.8em; opacity:0.5">(${hexInput})</span>`;
    
    return closestName;
}

/**
 * ACTUALIZADA: Generar Lista con nombre de color
 */
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

/**
 * ACTUALIZADA: Imprimir (Arregla círculos invisibles)
 */
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