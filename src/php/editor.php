<?php
require_once 'config.php'; 

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'pedido' => null];

try {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'No ID']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Not found']);
        exit;
    }

    $tallas = !empty($pedido['tallas']) ? json_decode($pedido['tallas'], true) : [];
    
    // --- ESTA ES LA PARTE QUE TE FALTABA ---
    // Buscar el nombre real de cada prenda en el catálogo
    foreach ($tallas as &$t) {
        if (!empty($t['prenda_id'])) {
            $st = $pdo->prepare("SELECT tipo_prenda, marca, modelo, descripcion FROM catalogo_prendas WHERE id = ?");
            $st->execute([$t['prenda_id']]);
            $cat = $st->fetch(PDO::FETCH_ASSOC);
            if ($cat) {
                // Construimos el nombre completo: "Playera Gildan 5000 (Algodón pesado)"
                $desc = !empty($cat['descripcion']) ? " (" . $cat['descripcion'] . ")" : "";
                $t['tipo_prenda'] = $cat['tipo_prenda'] . " " . $cat['marca'] . " " . $cat['modelo'] . $desc;
            } else {
                $t['tipo_prenda'] = "Prenda no encontrada";
            }
        }
    }
    
    $pedido['tallas'] = $tallas;
    $pedido['imagenes'] = !empty($pedido['imagenes']) ? json_decode($pedido['imagenes'], true) : [];

    echo json_encode(['success' => true, 'pedido' => $pedido]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>