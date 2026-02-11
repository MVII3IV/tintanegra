/**
 * events.js
 * Manejo de eventos del DOM, Inicialización y Clics
 * Requiere: utils.js, notifications.js, catalog.js, admin.js
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. DETECTAR ÉXITO Y CAPTURAR ID ---
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        if (urlParams.has('id')) {
            // Pasamos el ID a la variable global de admin.js
            // Asegúrate de que admin.js defina esta variable globalmente
            if(typeof idPedidoPendiente !== 'undefined') {
                 idPedidoPendiente = urlParams.get('id'); 
            }
        }

        const modalExito = new bootstrap.Modal(document.getElementById('successModal'));
        modalExito.show();

        setTimeout(() => {
            modalExito.hide();
        }, 2000);

        const nuevaUrl = window.location.pathname;
        window.history.replaceState({}, document.title, nuevaUrl);
    }

    // --- 2. ACTIVAR PREVIEWS DE IMÁGENES ---
    const activarPrevisualizacion = (inputId, containerId) => {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        
        if (input && container) {
            input.addEventListener('change', function() {
                if(typeof validarPesoArchivo === 'function') validarPesoArchivo(this);

                container.innerHTML = '';
                if (this.files && this.files.length > 0) {
                    Array.from(this.files).forEach(file => {
                        const div = document.createElement('div');
                        if (file.type === 'application/pdf') {
                            div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger shadow-sm" disabled><i class="bx bxs-file-pdf"></i> PDF</button>`;
                            container.appendChild(div);
                        } else if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                div.innerHTML = `<img src="${e.target.result}" class="rounded border shadow-sm" style="width:60px; height:60px; object-fit:cover;">`;
                                container.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            });
        }
    };

    activarPrevisualizacion('imagenes', 'imagenesPreview');
    activarPrevisualizacion('paletaColor', 'paletaColorPreview');
    activarPrevisualizacion('cotizacion', 'cotizacionPreview');


    // --- 3. INICIALIZACIÓN DE MODALES Y VARIABLES ---
    // Verificamos existencia antes de instanciar para evitar errores en consolas limpias
    const waModalEl = document.getElementById('waModal');
    const waModal = waModalEl ? new bootstrap.Modal(waModalEl) : null;
    
    const deleteModalEl = document.getElementById('deleteModal');
    const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
    
    const catalogoModalEl = document.getElementById('modalCatalogo');
    const catalogoModal = catalogoModalEl ? new bootstrap.Modal(catalogoModalEl) : null;
    
    let idToDelete = null;
    let idCatalogoToDelete = null; 

    // --- 4. CARGA INICIAL DE DATOS ---
    if(typeof cargarPedidos === 'function') cargarPedidos();
    if(typeof recargarCatalogoAjax === 'function') recargarCatalogoAjax();
    if(typeof addTallaEntry === 'function') addTallaEntry();


    // --- 5. EVENTOS DE BOTONES ESTÁTICOS ---
    const btnAddTalla = document.getElementById('addTalla');
    if (btnAddTalla) {
        btnAddTalla.addEventListener('click', () => {
            if(typeof addTallaEntry === 'function') addTallaEntry('', 1, '#000000', '', true);
        });
    }

    const buscador = document.getElementById('buscadorNombre');
    if (buscador) {
        buscador.addEventListener('input', (e) => {
            if(typeof cargarPedidos === 'function') cargarPedidos(e.target.value);
        });
    }

    const btnLista = document.getElementById('btnGenerarLista');
    if (btnLista) {
        btnLista.addEventListener('click', () => {
            if(typeof generarResumenCompra === 'function') generarResumenCompra(pedidosCargados);
        });
    }


    // --- 6. DELEGACIÓN DE EVENTOS GLOBAL ---
    document.addEventListener('click', (e) => {
        
        // A. Abrir Catálogo
        if (e.target.closest('.btn-open-catalogo')) {
            if(catalogoModal) catalogoModal.show();
        }

        // B. WhatsApp Manual
        const btnWA = e.target.closest('.btn-wa-preview');
        if (btnWA) {
            const link = btnWA.getAttribute('data-link');
            const urlObj = new URL(link);
            document.getElementById('wa-destinatario').innerText = btnWA.getAttribute('data-nombre');
            document.getElementById('wa-mensaje-pre').value = decodeURIComponent(urlObj.searchParams.get("text"));
            document.getElementById('wa-confirmar-link').dataset.tel = btnWA.getAttribute('data-tel').replace(/\D/g, '');
            if(waModal) waModal.show();
        }

        // C. Editar Pedido
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
                    if (p.tallas) {
                        p.tallas.forEach(t => {
                            if(typeof addTallaEntry === 'function') 
                                addTallaEntry(t.talla, t.cantidad, t.color, t.prenda_id || '', false);
                        });
                    }
                    
                    const renderPreview = (cid, content, isArray=false) => {
                        const c = document.getElementById(cid); if(!c) return; c.innerHTML='';
                        let items = [];
                        if (isArray) { try { items = (typeof content === 'string') ? JSON.parse(content) : content; } catch(e) { items = []; } } 
                        else if (content) { items = [content]; }
                        if(!Array.isArray(items)) items=[];
                        
                        items.forEach(url => {
                            if(!url) return;
                            const el = document.createElement('div');
                            if(url.toLowerCase().endsWith('.pdf')) el.innerHTML=`<a href="${url}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm"><i class='bx bxs-file-pdf'></i> PDF</a>`;
                            else el.innerHTML=`<a href="${url}" target="_blank"><img src="${url}" class="rounded border shadow-sm" style="width:60px; height:60px; object-fit:cover;"></a>`;
                            c.appendChild(el);
                        });
                    };
                    renderPreview('imagenesPreview', p.imagenes, true);
                    renderPreview('paletaColorPreview', p.paletaColor);
                    renderPreview('cotizacionPreview', p.cotizacion);

                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    document.getElementById('formHeader').innerText = "Editando Pedido #" + p.id;
                    document.getElementById('submitButton').innerText = "Actualizar Pedido";
                }
            });
        }

        // D. Botón Surtido
        const btnSurtido = e.target.closest('.btn-surtido');
        if (btnSurtido) {
            const id = btnSurtido.dataset.id;
            const nuevoEstado = parseInt(btnSurtido.dataset.estado) === 1 ? 0 : 1;
            const fd = new FormData(); fd.append('id', id); fd.append('estado', nuevoEstado);

            fetch('php/updateApparel.php', { method: 'POST', body: fd })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        btnSurtido.dataset.estado = nuevoEstado;
                        if (nuevoEstado === 1) {
                            btnSurtido.classList.replace('btn-light', 'btn-success');
                            btnSurtido.classList.remove('border');
                            btnSurtido.innerHTML = '<i class="bx bx-check-double"></i>';
                        } else {
                            btnSurtido.classList.replace('btn-success', 'btn-light');
                            btnSurtido.classList.add('border');
                            btnSurtido.innerHTML = '<i class="bx bx-check"></i>';
                        }
                    }
                });
        }

        // E. Eliminar Pedido
        const btnDelete = e.target.closest('.delete-btn');
        if (btnDelete) {
            idToDelete = btnDelete.getAttribute('data-id');
            if(deleteModal) deleteModal.show();
        }

        // F. Eliminar Talla
        if (e.target.closest('.remove-talla')) {
            e.target.closest('.talla-entry').remove();
            if(typeof calcularTotalPiezas === 'function') calcularTotalPiezas();
        }

        // G. Eliminar Prenda Catálogo
        const btnDelCat = e.target.closest('.btn-eliminar-prenda');
        if (btnDelCat) {
            idCatalogoToDelete = btnDelCat.getAttribute('data-id');
            const modalEl = document.getElementById('deleteCatalogoModal');
            if(modalEl) new bootstrap.Modal(modalEl).show();
        }

        // H. Confirmar Borrado Catálogo
        const btnConfCat = e.target.closest('#btnConfirmarBorrarCatalogo');
        if (btnConfCat && idCatalogoToDelete) {
             const fd = new FormData(); fd.append('accion', 'eliminar'); fd.append('id', idCatalogoToDelete);
             fetch('php/catalog_management.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { 
                 if(d.success) {
                    const modalEl = document.getElementById('deleteCatalogoModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modalInstance.hide();
                    
                    const row = document.getElementById(`prenda-${idCatalogoToDelete}`);
                    if(row) row.remove();
                    
                    if(typeof recargarCatalogoAjax === 'function') recargarCatalogoAjax(); 
                 } else alert('Error: ' + d.error);
             });
        }
        
        // I. Checkboxes
        if (e.target.id === 'checkAll') {
            document.querySelectorAll('.check-pedido').forEach(cb => cb.checked = e.target.checked);
            if(typeof actualizarBotonLista === 'function') actualizarBotonLista();
        }
        if (e.target.classList.contains('check-pedido')) {
            if(typeof actualizarBotonLista === 'function') actualizarBotonLista();
        }
    });


    // --- 7. CONFIRMACIONES ---

    const btnConfDelete = document.getElementById('confirmDeleteBtn');
    if (btnConfDelete) {
        btnConfDelete.addEventListener('click', () => {
            fetch(`php/deleteOrder.php?id=${idToDelete}`, { method: 'DELETE' }).then(r => r.json()).then(d => { 
                if (d.success) { 
                    if(deleteModal) deleteModal.hide(); 
                    if(typeof cargarPedidos === 'function') cargarPedidos(); 
                } 
            });
        });
    }

    const btnWaLink = document.getElementById('wa-confirmar-link');
    if(btnWaLink) {
        btnWaLink.addEventListener('mousedown', function() {
            const tel = this.dataset.tel;
            const msj = document.getElementById('wa-mensaje-pre').value;
            this.href = `https://wa.me/52${tel}?text=${encodeURIComponent(msj)}`;
        });
    }

    const formPrenda = document.getElementById('formNuevaPrenda');
    if(formPrenda) {
        formPrenda.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this); fd.append('accion', 'guardar');
            fetch('php/catalog_management.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { 
                if(d.success) { this.reset(); if(typeof recargarCatalogoAjax === 'function') recargarCatalogoAjax(); } 
                else alert('Error: ' + d.error);
            });
        });
    }

    
    
    // --- 8. VALIDACIÓN DEL FORMULARIO DE PEDIDO (VERSION RENOVADA) ---
    const pedidoForm = document.getElementById('pedidoForm');
    if (pedidoForm) {
        pedidoForm.addEventListener('submit', function(e) {
            const pedidoIdField = document.getElementById('pedidoId');
            const isEdit = pedidoIdField && pedidoIdField.value !== "";
            
            const hasImg = document.getElementById('imagenes').files.length > 0;
            const hasPaleta = document.getElementById('paletaColor').files.length > 0;
            const hasCotizacion = document.getElementById('cotizacion').files.length > 0;

            const prevImg = document.getElementById('imagenesPreview')?.children.length > 0;
            const prevPaleta = document.getElementById('paletaColorPreview')?.children.length > 0;
            const prevCotizacion = document.getElementById('cotizacionPreview')?.children.length > 0;

            const validImg = hasImg || (isEdit && prevImg);
            const validPaleta = hasPaleta || (isEdit && prevPaleta);
            const validCotizacion = hasCotizacion || (isEdit && prevCotizacion);

            let errores = [];
            if (!validImg) errores.push("Imágenes del Diseño");
            if (!validPaleta) errores.push("Paleta de Colores");
            if (!validCotizacion) errores.push("Archivo de Cotización");

            if (errores.length > 0) {
                e.preventDefault(); 
                
                const modalEl = document.getElementById('fileSizeModal');
                const title = document.getElementById('fileSizeTitle');
                const body = document.getElementById('fileSizeBody');

                if (modalEl && title && body) {
                    // Diseño Renovado
                    title.innerHTML = `<i class='bx bx-error-circle text-danger' style='font-size: 3rem;'></i><br>
                                       <span class="text-dark fw-bolder h5">¡Atención!</span>`;
                    
                    let listaErrores = errores.map(err => 
                        `<div class="d-flex align-items-center mb-2 justify-content-center text-danger">
                            <i class='bx bx-x-circle me-2'></i> <span>${err}</span>
                         </div>`
                    ).join('');

                    body.innerHTML = `
                        <p class="text-muted small mb-4">Para guardar este pedido es necesario que adjuntes los siguientes archivos:</p>
                        <div class="bg-light p-3 rounded-3 mb-2">
                            ${listaErrores}
                        </div>
                    `;

                    new bootstrap.Modal(modalEl).show();
                } else {
                    alert("Faltan archivos obligatorios:\n" + errores.join("\n"));
                }
            }
        });
    }

});