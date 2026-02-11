/**
 * events.js
 * Manejo de eventos del DOM, Inicialización y Clics
 * Requiere: utils.js, notifications.js, catalog.js, admin.js
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. DETECTAR ÉXITO Y CAPTURAR ID ---
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const modalExito = new bootstrap.Modal(document.getElementById('successModal'));
        modalExito.show();

        setTimeout(() => {
            modalExito.hide();
        }, 2000);

        const nuevaUrl = window.location.pathname;
        window.history.replaceState({}, document.title, nuevaUrl);
    }

    // --- 2. ACTIVAR PREVIEWS DE IMÁGENES ---
    // Función local para manejar la vista previa en los inputs de archivo
    const activarPrevisualizacion = (inputId, containerId) => {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        
        if (input && container) {
            input.addEventListener('change', function() {
                // Validar peso primero (utils.js)
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

    // Activamos los previews
    activarPrevisualizacion('imagenes', 'imagenesPreview');
    activarPrevisualizacion('paletaColor', 'paletaColorPreview');
    activarPrevisualizacion('cotizacion', 'cotizacionPreview');


    // --- 3. INICIALIZACIÓN DE VARIABLES Y MODALES ---
    const waModal = new bootstrap.Modal(document.getElementById('waModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const catalogoModal = new bootstrap.Modal(document.getElementById('modalCatalogo'));
    let idToDelete = null;
    let idCatalogoToDelete = null; 

    // --- 4. CARGA INICIAL DE DATOS ---
    // Llamamos a las funciones que viven en admin.js y catalog.js
    if(typeof cargarPedidos === 'function') cargarPedidos();
    if(typeof recargarCatalogoAjax === 'function') recargarCatalogoAjax();
    if(typeof addTallaEntry === 'function') addTallaEntry();


    // --- 5. EVENTOS DE BOTONES ESTÁTICOS ---
    
    // Botón Agregar Talla (Formulario)
    const btnAddTalla = document.getElementById('addTalla');
    if (btnAddTalla) {
        btnAddTalla.addEventListener('click', () => {
            if(typeof addTallaEntry === 'function') addTallaEntry('', 1, '#000000', '', true);
        });
    }

    // Buscador
    const buscador = document.getElementById('buscadorNombre');
    if (buscador) {
        buscador.addEventListener('input', (e) => {
            if(typeof cargarPedidos === 'function') cargarPedidos(e.target.value);
        });
    }

    // Botón Generar Lista de Compra
    const btnLista = document.getElementById('btnGenerarLista');
    if (btnLista) {
        btnLista.addEventListener('click', () => {
            if(typeof generarResumenCompra === 'function') generarResumenCompra(pedidosCargados);
        });
    }


    // --- 6. DELEGACIÓN DE EVENTOS GLOBAL (Clics en la tabla) ---
    document.addEventListener('click', (e) => {
        
        // A. Abrir Catálogo
        if (e.target.closest('.btn-open-catalogo')) {
            catalogoModal.show();
        }

        // B. Botón WhatsApp Manual (Preview)
        const btnWA = e.target.closest('.btn-wa-preview');
        if (btnWA) {
            const link = btnWA.getAttribute('data-link');
            const urlObj = new URL(link);
            document.getElementById('wa-destinatario').innerText = btnWA.getAttribute('data-nombre');
            document.getElementById('wa-mensaje-pre').value = decodeURIComponent(urlObj.searchParams.get("text"));
            document.getElementById('wa-confirmar-link').dataset.tel = btnWA.getAttribute('data-tel').replace(/\D/g, '');
            waModal.show();
        }

        // C. Editar Pedido (Botón Lápiz)
        const btnEdit = e.target.closest('.edit-btn');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            // Llamamos a la función de carga de datos (debe estar en admin.js)
            // Si no usaste cargarDatosParaEditar, aquí va la lógica fetch inline:
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
                    
                    // Nota: Aquí podrías llamar a una función para refrescar previews si la tienes
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    document.getElementById('formHeader').innerText = "Editando Pedido #" + p.id;
                    document.getElementById('submitButton').innerText = "Actualizar Pedido";
                }
            });
        }

        // D. Botón Surtido (Toggle)
        const btnSurtido = e.target.closest('.btn-surtido');
        if (btnSurtido) {
            const id = btnSurtido.dataset.id;
            const nuevoEstado = parseInt(btnSurtido.dataset.estado) === 1 ? 0 : 1;
            const fd = new FormData();
            fd.append('id', id);
            fd.append('estado', nuevoEstado);

            fetch('php/updateApparel.php', { method: 'POST', body: fd })
                .then(r => r.json()).then(data => {
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

        // E. Eliminar Pedido (Papelera)
        const btnDelete = e.target.closest('.delete-btn');
        if (btnDelete) {
            idToDelete = btnDelete.getAttribute('data-id');
            deleteModal.show();
        }

        // F. Eliminar Talla (Botón rojo en formulario)
        if (e.target.closest('.remove-talla')) {
            e.target.closest('.talla-entry').remove();
            if(typeof calcularTotalPiezas === 'function') calcularTotalPiezas();
        }

        // G. Eliminar Prenda del Catálogo
        const btnDelCat = e.target.closest('.btn-eliminar-prenda');
        if (btnDelCat) {
            idCatalogoToDelete = btnDelCat.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('deleteCatalogoModal'));
            modal.show();
        }

        // H. Confirmar Borrado de Catálogo
        const btnConfCat = e.target.closest('#btnConfirmarBorrarCatalogo');
        if (btnConfCat && idCatalogoToDelete) {
             const fd = new FormData(); 
             fd.append('accion', 'eliminar'); 
             fd.append('id', idCatalogoToDelete);

             fetch('php/catalog_management.php', { method: 'POST', body: fd })
             .then(r => r.json()).then(d => { 
                 if(d.success) {
                    const modalEl = document.getElementById('deleteCatalogoModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modalInstance.hide();
                    
                    const row = document.getElementById(`prenda-${idCatalogoToDelete}`);
                    if(row) row.remove();
                    
                    if(typeof recargarCatalogoAjax === 'function') recargarCatalogoAjax(); 
                 } else {
                    alert('Error: ' + d.error);
                 }
             });
        }
        
        // I. Checkboxes de selección
        if (e.target.id === 'checkAll') {
            document.querySelectorAll('.check-pedido').forEach(cb => cb.checked = e.target.checked);
            if(typeof actualizarBotonLista === 'function') actualizarBotonLista();
        }
        if (e.target.classList.contains('check-pedido')) {
            if(typeof actualizarBotonLista === 'function') actualizarBotonLista();
        }
    });


    // --- 7. CONFIRMACIONES FINALES ---

    // Confirmar Borrar Pedido Principal
    const btnConfDelete = document.getElementById('confirmDeleteBtn');
    if (btnConfDelete) {
        btnConfDelete.addEventListener('click', () => {
            fetch(`php/deleteOrder.php?id=${idToDelete}`, { method: 'DELETE' })
            .then(res => res.json()).then(data => { 
                if (data.success) { 
                    deleteModal.hide(); 
                    if(typeof cargarPedidos === 'function') cargarPedidos(); 
                } 
            });
        });
    }

    // Confirmar Envío WhatsApp (Actualizar href)
    const btnWaLink = document.getElementById('wa-confirmar-link');
    if(btnWaLink) {
        btnWaLink.addEventListener('mousedown', function() {
            const tel = this.dataset.tel;
            const msj = document.getElementById('wa-mensaje-pre').value;
            this.href = `https://wa.me/52${tel}?text=${encodeURIComponent(msj)}`;
        });
    }

    // Guardar Nueva Prenda (Formulario Catálogo)
    const formPrenda = document.getElementById('formNuevaPrenda');
    if(formPrenda) {
        formPrenda.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('accion', 'guardar');
            
            fetch('php/catalog_management.php', { method: 'POST', body: fd })
            .then(r => r.json()).then(d => { 
                if(d.success) {
                    this.reset();
                    if(typeof recargarCatalogoAjax === 'function') recargarCatalogoAjax();
                } else {
                    alert('Error: ' + d.error);
                }
            });
        });
    }
});