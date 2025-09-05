<?php
header('Content-Type: application/json');

$host = "db"; // contenedor MySQL
$dbname = "tintanegra";
$user = "user";
$pass = "password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        echo json_encode(['error' => 'No se proporcionó ID']);
        exit;
    }

    // Traer info del pedido
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['error' => 'Pedido no encontrado']);
        exit;
    }

    // Decodificar JSON de tallas e imágenes
    $pedido['tallas'] = isset($pedido['tallas']) ? json_decode($pedido['tallas'], true) : [];
    $pedido['imagenes'] = isset($pedido['imagenes']) ? json_decode($pedido['imagenes'], true) : [];

    echo json_encode($pedido);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
