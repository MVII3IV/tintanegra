<?php
require_once 'config.php';

// Procesar tallas
$tallas = [];
if (isset($_POST['talla']) && is_array($_POST['talla'])) {
    foreach ($_POST['talla'] as $i => $t) {
        if (!empty($t)) { 
            $tallas[] = [
                'talla'    => htmlspecialchars($t),
                'cantidad' => (int)$_POST['cantidad'][$i],
                'color'    => htmlspecialchars($_POST['color'][$i])
            ];
        }
    }
}

// Procesar imágenes del pedido (múltiples)
$imagenes = [];
if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['tmp_name'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp_name) {
        if (!empty($tmp_name) && $_FILES['imagenes']['error'][$i] == UPLOAD_ERR_OK) {
            $fileName = time().'_'.basename($_FILES['imagenes']['name'][$i]);
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($tmp_name, $targetFilePath)) {
                $imagenes[] = $targetFilePath;
            }
        }
    }
}

// Procesar imagen de paleta de colores (una sola)
$paletaColorPath = null;
if (isset($_FILES['paletaColor']) && $_FILES['paletaColor']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/color_palettes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = time().'_'.basename($_FILES['paletaColor']['name']);
    $targetFilePath = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['paletaColor']['tmp_name'], $targetFilePath)) {
        $paletaColorPath = $targetFilePath;
    }
}

// Insertar en DB
$stmt = $pdo->prepare("INSERT INTO pedidos 
    (nombre, status, fechaInicio, fechaEntrega, costo, anticipo, tallas, imagenes, paletaColor)
    VALUES (:nombre, :status, :fechaInicio, :fechaEntrega, :costo, :anticipo, :tallas, :imagenes, :paletaColor)");

try {
    $stmt->execute([
        ':nombre'       => htmlspecialchars($_POST['nombre']),
        ':status'       => htmlspecialchars($_POST['status']),
        ':fechaInicio'  => $_POST['fechaInicio'],
        ':fechaEntrega' => $_POST['fechaEntrega'],
        ':costo'        => (float)$_POST['costo'],
        ':anticipo'     => (float)$_POST['anticipo'],
        ':tallas'       => json_encode($tallas, JSON_UNESCAPED_UNICODE),
        ':imagenes'     => json_encode($imagenes, JSON_UNESCAPED_UNICODE),
        ':paletaColor'  => $paletaColorPath
    ]);

    $id = $pdo->lastInsertId();

    // Redirigir a orders.html con el id recién creado
    header("Location: orders.html?id=$id");
    exit;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
