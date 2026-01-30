<?php
require_once 'config.php'; 

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'pedidos' => []];

if (isset($_GET['nombre'])) {
    
    $busqueda = trim($_GET['nombre']);
    $nombreBusqueda = '%' . $busqueda . '%';
    $mostrarTodo = isset($_GET['todo']) && $_GET['todo'] === 'true';

    try {
        if ($mostrarTodo) {
            $sql = "SELECT * FROM pedidos WHERE nombre LIKE :nombre ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM pedidos WHERE nombre LIKE :nombre AND status != 'Entregada' ORDER BY id DESC";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombreBusqueda]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($pedidos) {
            foreach ($pedidos as &$pedido) {
                // 1. Decodificar JSON de tallas
                $tallasArray = !empty($pedido['tallas']) ? json_decode($pedido['tallas'], true) : [];
                
                // 2. ENRIQUECER CON DATOS DEL CATÁLOGO
                foreach ($tallasArray as &$t) {
                    if (!empty($t['prenda_id'])) {
                        // Buscamos los datos incluyendo la DESCRIPCIÓN
                        $stmtCat = $pdo->prepare("SELECT tipo_prenda, marca, modelo, descripcion FROM catalogo_prendas WHERE id = :id");
                        $stmtCat->execute([':id' => $t['prenda_id']]);
                        $infoPrenda = $stmtCat->fetch(PDO::FETCH_ASSOC);

                        if ($infoPrenda) {
                            // Construimos el nombre completo para el JS
                            // Ej: "Playera Yazbek C0300 (Cuello Redondo)"
                            $desc = !empty($infoPrenda['descripcion']) ? " (" . $infoPrenda['descripcion'] . ")" : "";
                            $t['nombre_prenda'] = $infoPrenda['tipo_prenda'] . " " . $infoPrenda['marca'] . " " . $infoPrenda['modelo'] . $desc;
                        } else {
                            $t['nombre_prenda'] = "Prenda eliminada del catálogo";
                        }
                    } else {
                        // Si es un pedido viejo sin ID, usamos lo que tenga guardado en texto o un genérico
                        $t['nombre_prenda'] = !empty($t['tipo_prenda']) ? $t['tipo_prenda'] : "Prenda manual / anterior";
                    }
                }

                $pedido['tallas'] = $tallasArray;
                $pedido['imagenes'] = !empty($pedido['imagenes']) ? json_decode($pedido['imagenes'], true) : [];
                $pedido['costo'] = floatval($pedido['costo']);
                $pedido['anticipo'] = floatval($pedido['anticipo']);
            }

            $response['success'] = true;
            $response['pedidos'] = $pedidos;
        } else {
            $response['success'] = true;
            $response['pedidos'] = [];
        }

    } catch (PDOException $e) {
        $response['message'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $response['message'] = 'Parámetro de búsqueda no recibido';
}

echo json_encode($response);
?>