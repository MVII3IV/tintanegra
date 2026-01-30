<div class="modal fade" id="modalCatalogo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title text-white">Catálogo de Prendas Base</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formNuevaPrenda" class="row g-3 mb-4 pb-4 border-bottom">
                    <div class="col-md-2"><label class="small fw-bold">Tipo</label><input type="text" class="form-control form-control-sm" name="tipo_prenda" placeholder="Ej: Playera" required></div>
                    <div class="col-md-2"><label class="small fw-bold">Marca</label><input type="text" class="form-control form-control-sm" name="marca" placeholder="Ej: Gildan" required></div>
                    <div class="col-md-2"><label class="small fw-bold">Modelo</label><input type="text" class="form-control form-control-sm" name="modelo" placeholder="Ej: 5000" required></div>
                    <div class="col-md-3"><label class="small fw-bold">Descripción</label><input type="text" class="form-control form-control-sm" name="descripcion" placeholder="Ej: Cuello redondo"></div>
                    <div class="col-md-1"><label class="small fw-bold">Género</label><select class="form-select form-select-sm px-1" name="genero"><option value="Unisex">Unisex</option><option value="Dama">Dama</option><option value="Niño">Niño</option></select></div>
                    <div class="col-md-1"><label class="small fw-bold">Costo</label><input type="number" step="0.01" class="form-control form-control-sm px-1" name="costo_base" placeholder="0.00"></div>
                    <div class="col-md-1 d-flex align-items-end"><button type="submit" class="btn btn-dark btn-sm w-100"><i class="bx bx-save"></i></button></div>
                </form>
                <div class="table-responsive" style="max-height: 350px;">
                    <table class="table table-sm align-middle">
                        <thead class="table-light"><tr><th>Prenda</th><th>Marca</th><th>Modelo</th><th>Descripción</th><th>Género</th><th>Costo</th><th class="text-end">Acciones</th></tr></thead>
                        <tbody id="listaCatalogo">
                            <?php 
                            // Ojo: $pdo debe estar disponible en admin.php antes de incluir este archivo
                            $stmtC = $pdo->query("SELECT * FROM catalogo_prendas WHERE activo = 1 ORDER BY id DESC");
                            while($r = $stmtC->fetch()): ?>
                            <tr id="prenda-<?= $r['id'] ?>">
                                <td><?= htmlspecialchars($r['tipo_prenda']) ?></td>
                                <td><?= htmlspecialchars($r['marca']) ?></td>
                                <td><?= htmlspecialchars($r['modelo']) ?></td>
                                <td class="text-muted small"><em><?= htmlspecialchars($r['descripcion'] ?? '') ?></em></td>
                                <td><span class="badge-catalogo"><?= $r['genero'] ?></span></td>
                                <td>$<?= number_format($r['costo_base'], 2) ?></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger border-0 btn-eliminar-prenda" data-id="<?= $r['id'] ?>"><i class="bx bx-trash"></i></button></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-warning text-dark border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bx bx-error me-2"></i>Confirmar Cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center"><p class="mb-0 fs-5">¿Estás seguro de que deseas actualizar la información de este pedido?</p></div>
            <div class="modal-footer border-0 p-3 justify-content-center" dir="ltr">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarUpdate" class="btn btn-dark px-4">Sí, Actualizar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4"><i class="bx bx-error-circle text-danger" style="font-size: 80px;"></i></div>
                <h4 class="fw-bold mb-3">¿Confirmar eliminación?</h4>
                <p class="text-muted mb-4">Esta acción eliminará permanentemente el pedido y sus archivos.</p>
                <div class="d-flex gap-3 justify-content-center mt-4" dir="ltr">
                    <button type="button" class="btn btn-light px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 fw-bold text-white shadow-sm" style="white-space: nowrap; min-width: 140px;">Eliminar Ahora</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="waModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-success text-white border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bx bxl-whatsapp me-2"></i> Previsualizar Notificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3"><label class="small text-muted fw-bold text-uppercase">Enviar a:</label><div id="wa-destinatario" class="fw-bold fs-5"></div></div>
                <div class="mb-3"><label class="small text-muted fw-bold text-uppercase">Mensaje:</label><textarea id="wa-mensaje-pre" class="form-control bg-light rounded-3 border" style="font-size: 0.95rem; height: 150px; resize: none;"></textarea></div>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="wa-confirmar-link" target="_blank" class="btn btn-success px-4">Confirmar y Enviar</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalListaCompra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bx bx-list-check me-2"></i>Lista de Compra Consolidada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="listaCompraContent"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bx bx-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>
</div>