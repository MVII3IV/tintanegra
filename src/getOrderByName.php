<?php
require_once 'config.php'; 

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'pedidos' => []];

if (isset($_GET['nombre']) && !empty(trim($_GET['nombre']))) {
    $nombreBusqueda = '%' . trim($_GET['nombre']) . '%';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE nombre LIKE :nombre");
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
            $response['message'] = 'No se encontraron pedidos con ese nombre';
        }

    } catch (PDOException $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'No se proporcionó nombre';
}

echo json_encode($response);
