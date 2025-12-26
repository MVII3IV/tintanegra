<?php
require_once 'config.php'; 

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'pedidos' => []];

// Eliminamos la restricción de !empty para que permita buscar todo si está vacío
if (isset($_GET['nombre'])) {
    // Si hay nombre buscamos por filtro, si está vacío usamos '%' para traer todos
    $busqueda = trim($_GET['nombre']);
    $nombreBusqueda = '%' . $busqueda . '%';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // La consulta con LIKE '%' traerá todos los registros si el input está vacío
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE nombre LIKE :nombre ORDER BY id DESC");
        $stmt->execute([':nombre' => $nombreBusqueda]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($pedidos) {
            foreach ($pedidos as &$pedido) {
                // Decodificar JSON en tallas e imágenes
                $pedido['tallas']   = !empty($pedido['tallas'])   ? json_decode($pedido['tallas'], true)   : [];
                $pedido['imagenes'] = !empty($pedido['imagenes']) ? json_decode($pedido['imagenes'], true) : [];
            }

            $response['success'] = true;
            $response['pedidos'] = $pedidos;
        } else {
            $response['success'] = true; // Sigue siendo un éxito técnico, solo que la lista está vacía
            $response['message'] = 'No se encontraron pedidos';
            $response['pedidos'] = [];
        }

    } catch (PDOException $e) {
        $response['message'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $response['message'] = 'Parámetro de búsqueda no recibido';
}

echo json_encode($response);