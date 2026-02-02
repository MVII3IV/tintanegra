<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $estado = $_POST['estado']; // 1 o 0

    $stmt = $pdo->prepare("UPDATE pedidos SET prendas_surtidas = ? WHERE id = ?");
    $result = $stmt->execute([$estado, $id]);

    echo json_encode(['success' => $result]);
}