<?php
// php/catalog_management.php
require_once 'config.php';

// Establecer cabecera JSON desde el inicio
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    
    if ($_POST['accion'] === 'guardar') {
        $tipo    = $_POST['tipo_prenda'] ?? '';
        $marca   = $_POST['marca'] ?? '';
        $modelo  = $_POST['modelo'] ?? '';
        $genero  = $_POST['genero'] ?? 'Unisex';
        $costo   = $_POST['costo_base'] ?? 0;

        if (!empty($tipo) && !empty($marca) && !empty($modelo)) {
            try {
                $sql = "INSERT INTO catalogo_prendas (tipo_prenda, marca, modelo, genero, costo_base, activo) 
                        VALUES (:t, :ma, :mo, :g, :c, 1)";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([
                    ':t'  => htmlspecialchars($tipo),
                    ':ma' => htmlspecialchars($marca),
                    ':mo' => htmlspecialchars($modelo),
                    ':g'  => $genero,
                    ':c'  => (float)$costo
                ]);
                echo json_encode(['success' => $res]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Campos obligatorios vacíos']);
        }
        exit;
    }

    if ($_POST['accion'] === 'eliminar') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            try {
                // Eliminación lógica: ponemos activo en 0
                $sql = "UPDATE catalogo_prendas SET activo = 0 WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([':id' => $id]);
                echo json_encode(['success' => $res]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
        }
        exit;
    }
}

// Si llega aquí sin entrar en los if anteriores
echo json_encode(['success' => false, 'error' => 'Acción no permitida']);
exit;