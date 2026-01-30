<?php
require_once 'config.php';
header('Content-Type: application/json');

// ACCIÓN: Listar (Para recargar la tabla sin F5)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'listar') {
    try {
        $stmt = $pdo->query("SELECT * FROM catalogo_prendas WHERE activo = 1 ORDER BY id DESC");
        echo json_encode(['success' => true, 'catalogo' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ACCIONES: Guardar y Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    
    // --- GUARDAR NUEVA PRENDA ---
    if ($_POST['accion'] === 'guardar') {
        $tipo    = $_POST['tipo_prenda'] ?? '';
        $marca   = $_POST['marca'] ?? '';
        $modelo  = $_POST['modelo'] ?? '';
        $desc    = $_POST['descripcion'] ?? ''; // <--- NUEVO CAMPO CAPTURADO
        $genero  = $_POST['genero'] ?? 'Unisex';
        $costo   = $_POST['costo_base'] ?? 0;

        if (!empty($tipo) && !empty($marca) && !empty($modelo)) {
            try {
                // Insertamos incluyendo la descripción
                $sql = "INSERT INTO catalogo_prendas (tipo_prenda, marca, modelo, descripcion, genero, costo_base, activo) 
                        VALUES (:t, :ma, :mo, :d, :g, :c, 1)";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([
                    ':t'  => htmlspecialchars($tipo),
                    ':ma' => htmlspecialchars($marca),
                    ':mo' => htmlspecialchars($modelo),
                    ':d'  => htmlspecialchars($desc), // <--- SE GUARDA AQUÍ
                    ':g'  => $genero,
                    ':c'  => (float)$costo
                ]);
                echo json_encode(['success' => $res]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
        }
        exit;
    }

    // --- ELIMINAR PRENDA ---
    if ($_POST['accion'] === 'eliminar') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE catalogo_prendas SET activo = 0 WHERE id = :id");
                echo json_encode(['success' => $stmt->execute([':id' => $id])]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Acción no permitida']);
?>