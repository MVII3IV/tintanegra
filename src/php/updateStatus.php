<?php
require_once 'config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($id && $status) {
        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);

            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Estado actualizado correctamente';
            } else {
                $response['message'] = 'No se realizaron cambios o el pedido no existe';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Error de BD: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Datos incompletos';
    }
}

echo json_encode($response);