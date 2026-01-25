<?php
require_once 'config.php';
require_once 'id-generator.php'; 

// 1. CONFIGURACIÓN DE DIRECTORIOS
$uploadDir = '../uploads/';
$colorPalettesDir = '../uploads/color_palettes/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($colorPalettesDir)) mkdir($colorPalettesDir, 0777, true);

// 2. GESTIÓN DE IDENTIFICADOR (Crucial para el nombre de archivos)
$pedidoId = $_POST['pedido_id'] ?? null;
$esEdicion = !empty($pedidoId);

if (!$esEdicion) {
    // Si es nuevo, generamos el ID de una vez para usarlo en los nombres de archivos
    $pedidoId = generateFunnyOrderId();
}

// 3. RECUPERAR DATOS PREVIOS (Si es edición, para limpieza de archivos)
$currentPedidoImages = [];
$currentPaletaColorPath = null;

if ($esEdicion) {
    $stmt = $pdo->prepare("SELECT imagenes, paletaColor FROM pedidos WHERE id = :id");
    $stmt->execute([':id' => $pedidoId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $currentPedidoImages = json_decode($existing['imagenes'], true) ?? [];
        $currentPaletaColorPath = $existing['paletaColor'];
    }
}

// 4. PROCESAR TALLAS
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

// 5. PROCESAR IMÁGENES DEL DISEÑO
$imagenes = [];
$newImagesUploaded = false;

if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['tmp_name'])) {
    foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp_name) {
        if (!empty($tmp_name) && $_FILES['imagenes']['error'][$i] == UPLOAD_ERR_OK) {
            
            $ext = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
            // Nombre profesional: ID_TIMESTAMP_RANDOM.EXT
            $fileName = $pedidoId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($tmp_name, $targetFilePath)) {
                $imagenes[] = "uploads/" . $fileName; // Guardamos ruta limpia
                $newImagesUploaded = true;
            }
        }
    }
}

// Lógica de reemplazo de imágenes
if ($newImagesUploaded && $esEdicion) {
    foreach ($currentPedidoImages as $oldImage) {
        $fullOldPath = __DIR__ . "/../" . ltrim($oldImage, './');
        if (file_exists($fullOldPath)) unlink($fullOldPath);
    }
} elseif (!$newImagesUploaded && $esEdicion) {
    $imagenes = $currentPedidoImages;
}

// 6. PROCESAR PALETA DE COLORES
$paletaColorPath = null;
$newPaletaUploaded = false;

if (isset($_FILES['paletaColor']) && $_FILES['paletaColor']['error'] == UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['paletaColor']['name'], PATHINFO_EXTENSION);
    $fileName = "PALETA_" . $pedidoId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    
    $targetFilePath = $colorPalettesDir . $fileName;
    if (move_uploaded_file($_FILES['paletaColor']['tmp_name'], $targetFilePath)) {
        $paletaColorPath = "uploads/color_palettes/" . $fileName;
        $newPaletaUploaded = true;
    }
}

// Lógica de reemplazo de paleta
if ($newPaletaUploaded && $esEdicion && $currentPaletaColorPath) {
    $fullOldPaleta = __DIR__ . "/../" . ltrim($currentPaletaColorPath, './');
    if (file_exists($fullOldPaleta)) unlink($fullOldPaleta);
} elseif (!$newPaletaUploaded && $esEdicion) {
    $paletaColorPath = $currentPaletaColorPath;
}

// 7. GUARDAR EN BASE DE DATOS
try {
    $params = [
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
    ];

    if ($esEdicion) {
        $sql = "UPDATE pedidos SET nombre=:nombre, status=:status, fechaInicio=:fechaInicio, 
                fechaEntrega=:fechaEntrega, costo=:costo, anticipo=:anticipo, tallas=:tallas, 
                imagenes=:imagenes, paletaColor=:paletaColor WHERE id=:id";
    } else {
        $sql = "INSERT INTO pedidos (id, nombre, status, fechaInicio, fechaEntrega, costo, anticipo, tallas, imagenes, paletaColor) 
                VALUES (:id, :nombre, :status, :fechaInicio, :fechaEntrega, :costo, :anticipo, :tallas, :imagenes, :paletaColor)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Redirección final
    $url = "../showOrder?id=" . $pedidoId . ($esEdicion ? "&updated=true" : "&created=true");
    header("Location: $url");
    exit;

} catch (Exception $e) {
    echo "Error crítico: " . $e->getMessage();
    exit;
}