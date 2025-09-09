<?php
require_once 'config.php';
require_once 'id-generator.php'; // Incluye el nuevo generador de ID

// Directorios de subida
$uploadDir = 'uploads/';
$colorPalettesDir = 'uploads/color_palettes/';

// Asegurarse de que los directorios existan
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
if (!is_dir($colorPalettesDir)) {
    mkdir($colorPalettesDir, 0777, true);
}

// Inicializar variables
$pedidoId = $_POST['pedido_id'] ?? null; // Obtener el ID del pedido si existe
$currentPedidoImages = [];
$currentPaletaColorPath = null;

// Si estamos editando, cargar las imágenes actuales para posible eliminación
if ($pedidoId) {
    $stmt = $pdo->prepare("SELECT imagenes, paletaColor FROM pedidos WHERE id = :id");
    $stmt->execute([':id' => $pedidoId]);
    $existingPedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingPedido) {
        $currentPedidoImages = json_decode($existingPedido['imagenes'], true) ?? [];
        $currentPaletaColorPath = $existingPedido['paletaColor'];
    }
}


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
$newImagesUploaded = false;
if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['tmp_name'])) {
    foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp_name) {
        if (!empty($tmp_name) && $_FILES['imagenes']['error'][$i] == UPLOAD_ERR_OK) {
            $fileName = time().'_'.uniqid().'_'.basename($_FILES['imagenes']['name'][$i]); // Añadir uniqid para mayor unicidad
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($tmp_name, $targetFilePath)) {
                $imagenes[] = $targetFilePath;
                $newImagesUploaded = true;
            }
        }
    }
}

// Si se subieron nuevas imágenes para el pedido, eliminar las antiguas
if ($newImagesUploaded && $pedidoId && !empty($currentPedidoImages)) {
    foreach ($currentPedidoImages as $oldImage) {
        if (file_exists($oldImage)) {
            unlink($oldImage);
        }
    }
}
// Si no se subieron nuevas imágenes, pero estamos editando, mantenemos las existentes
if (!$newImagesUploaded && $pedidoId) {
    $imagenes = $currentPedidoImages;
}


// Procesar imagen de paleta de colores (una sola)
$paletaColorPath = null;
$newPaletaColorUploaded = false;
if (isset($_FILES['paletaColor']) && $_FILES['paletaColor']['error'] == UPLOAD_ERR_OK) {
    $fileName = time().'_'.uniqid().'_'.basename($_FILES['paletaColor']['name']); // Añadir uniqid
    $targetFilePath = $colorPalettesDir . $fileName;
    if (move_uploaded_file($_FILES['paletaColor']['tmp_name'], $targetFilePath)) {
        $paletaColorPath = $targetFilePath;
        $newPaletaColorUploaded = true;
    }
}

// Si se subió una nueva imagen de paleta, eliminar la antigua
if ($newPaletaColorUploaded && $pedidoId && $currentPaletaColorPath && file_exists($currentPaletaColorPath)) {
    unlink($currentPaletaColorPath);
}
// Si no se subió una nueva paleta, pero estamos editando, mantenemos la existente
if (!$newPaletaColorUploaded && $pedidoId) {
    $paletaColorPath = $currentPaletaColorPath;
}


try {
    if ($pedidoId) {
        // Modo edición: UPDATE
        $stmt = $pdo->prepare("UPDATE pedidos SET
            nombre = :nombre,
            status = :status,
            fechaInicio = :fechaInicio,
            fechaEntrega = :fechaEntrega,
            costo = :costo,
            anticipo = :anticipo,
            tallas = :tallas,
            imagenes = :imagenes,
            paletaColor = :paletaColor
            WHERE id = :id");

        $stmt->execute([
            ':nombre'       => htmlspecialchars($_POST['nombre']),
            ':status'       => htmlspecialchars($_POST['status']),
            ':fechaInicio'  => $_POST['fechaInicio'],
            ':fechaEntrega' => $_POST['fechaEntrega'],
            ':costo'        => (float)$_POST['costo'],
            ':anticipo'     => (float)$_POST['anticipo'],
            ':tallas'       => json_encode($tallas, JSON_UNESCAPED_UNICODE),
            ':imagenes'     => json_encode($imagenes, JSON_UNESCAPED_UNICODE),
            ':paletaColor'  => $paletaColorPath,
            ':id'           => $pedidoId
        ]);

        header("Location: showOrder.html?id=$pedidoId&updated=true");
        exit;

    } else {
        // Modo creación: INSERT
        $newPedidoId = generateFunnyOrderId(); // Genera el nuevo ID único

        $stmt = $pdo->prepare("INSERT INTO pedidos
            (id, nombre, status, fechaInicio, fechaEntrega, costo, anticipo, tallas, imagenes, paletaColor)
            VALUES (:id, :nombre, :status, :fechaInicio, :fechaEntrega, :costo, :anticipo, :tallas, :imagenes, :paletaColor)");

        $stmt->execute([
            ':id'           => $newPedidoId, // Usa el nuevo ID
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

        // Ya no necesitas lastInsertId() porque generamos el ID
        header("Location: showOrder.html?id=$newPedidoId&created=true");
        exit;
    }

} catch (Exception $e) {
    // Para depuración, puedes mostrar el error o loggearlo
    echo "Error: " . $e->getMessage();
    // En un entorno de producción, es mejor redirigir a una página de error o mostrar un mensaje genérico.
    // header("Location: error.html?msg=" . urlencode($e->getMessage()));
    exit;
}