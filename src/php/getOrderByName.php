<?php
require_once 'config.php'; 

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'pedidos' => []];

// Verificamos si se recibió el parámetro 'nombre' (puede venir vacío)
if (isset($_GET['nombre'])) {
    
    $busqueda = trim($_GET['nombre']);
    $nombreBusqueda = '%' . $busqueda . '%';
    
    // Nuevo parámetro: si 'todo' es true, no filtramos por estado.
    // Esto lo usará el archivo pedidos.php
    $mostrarTodo = isset($_GET['todo']) && $_GET['todo'] === 'true';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($mostrarTodo) {
            // CONSULTA TOTAL: Para el historial completo
            $sql = "SELECT * FROM pedidos 
                    WHERE nombre LIKE :nombre 
                    ORDER BY id DESC";
        } else {
            // CONSULTA OPERATIVA: Ocultamos 'Entregada' para el panel admin.php
            $sql = "SELECT * FROM pedidos 
                    WHERE nombre LIKE :nombre 
                    AND status != 'Entregada' 
                    ORDER BY id DESC";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombreBusqueda]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($pedidos) {
            foreach ($pedidos as &$pedido) {
                // Decodificar JSON de tallas e imágenes para que el JS los use como objetos
                $pedido['tallas']   = !empty($pedido['tallas'])   ? json_decode($pedido['tallas'], true)   : [];
                $pedido['imagenes'] = !empty($pedido['imagenes']) ? json_decode($pedido['imagenes'], true) : [];
                
                // Aseguramos que los valores numéricos sean tratados como tales
                $pedido['costo'] = floatval($pedido['costo']);
                $pedido['anticipo'] = floatval($pedido['anticipo']);
            }

            $response['success'] = true;
            $response['pedidos'] = $pedidos;
        } else {
            $response['success'] = true;
            $response['message'] = 'No se encontraron registros';
            $response['pedidos'] = [];
        }

    } catch (PDOException $e) {
        $response['message'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $response['message'] = 'Parámetro de búsqueda no recibido';
}

echo json_encode($response);