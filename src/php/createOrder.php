<?php
require_once 'config.php';
require_once 'id-generator.php'; 

$uploadDir = '../uploads/';
$colorPalettesDir = '../uploads/color_palettes/';
$cotizacionesDir = '../uploads/cotizaciones/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($colorPalettesDir)) mkdir($colorPalettesDir, 0777, true);
if (!is_dir($cotizacionesDir)) mkdir($cotizacionesDir, 0777, true);

$pedidoId = $_POST['pedido_id'] ?? null;
$esEdicion = !empty($pedidoId);

if (!$esEdicion) {
    $pedidoId = generateId();
}

// RECUPERAR DATOS PREVIOS PARA LIMPIEZA
$currentPedidoImages = [];
$currentPaletaColorPath = null;
$currentCotizacionPath = null;

if ($esEdicion) {
    $stmt = $pdo->prepare("SELECT imagenes, paletaColor, cotizacion FROM pedidos WHERE id = :id");
    $stmt->execute([':id' => $pedidoId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $currentPedidoImages = json_decode($existing['imagenes'], true) ?? [];
        $currentPaletaColorPath = $existing['paletaColor'];
        $currentCotizacionPath = $existing['cotizacion'];
    }
}

// --- CORRECCIÓN AQUÍ: PROCESAR TALLAS Y PRENDA_ID ---
$tallas = [];
// Verificamos si existen los arrays enviados por el formulario
if (isset($_POST['talla']) && is_array($_POST['talla'])) {
    foreach ($_POST['talla'] as $i => $t) {
        // Guardamos si hay talla O si se seleccionó una prenda
        if (!empty($t) || !empty($_POST['prenda_id'][$i])) {
            $tallas[] = [
                'prenda_id' => $_POST['prenda_id'][$i] ?? 0, // <--- ESTO FALTABA PARA GUARDAR LA SELECCIÓN
                'talla'    => htmlspecialchars($t),
                'cantidad' => (int)$_POST['cantidad'][$i],
                'color'    => htmlspecialchars($_POST['color'][$i])
            ];
        }
    }
}

// PROCESAR IMÁGENES DISEÑO
$imagenes = [];
$newImagesUploaded = false;
if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['tmp_name'])) {
    foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp_name) {
        if (!empty($tmp_name) && $_FILES['imagenes']['error'][$i] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
            $fileName = $pedidoId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            if (move_uploaded_file($tmp_name, $uploadDir . $fileName)) {
                $imagenes[] = "uploads/" . $fileName;
                $newImagesUploaded = true;
            }
        }
    }
}
if ($newImagesUploaded && $esEdicion) {
    foreach ($currentPedidoImages as $old) {
        $path = __DIR__ . "/../" . ltrim($old, './');
        if (file_exists($path)) unlink($path);
    }
} elseif (!$newImagesUploaded && $esEdicion) { $imagenes = $currentPedidoImages; }

// PROCESAR PALETA
$paletaPath = $currentPaletaColorPath;
if (isset($_FILES['paletaColor']) && $_FILES['paletaColor']['error'] == UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['paletaColor']['name'], PATHINFO_EXTENSION);
    $fileName = "PALETA_" . $pedidoId . "_" . time() . "." . $ext;
    if (move_uploaded_file($_FILES['paletaColor']['tmp_name'], $colorPalettesDir . $fileName)) {
        if ($esEdicion && $currentPaletaColorPath && file_exists(__DIR__."/../".ltrim($currentPaletaColorPath, './'))) {
            unlink(__DIR__."/../".ltrim($currentPaletaColorPath, './'));
        }
        $paletaPath = "uploads/color_palettes/" . $fileName;
    }
}

// PROCESAR COTIZACIÓN
$cotizacionPath = $currentCotizacionPath;
if (isset($_FILES['cotizacion']) && $_FILES['cotizacion']['error'] == UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['cotizacion']['name'], PATHINFO_EXTENSION);
    $fileName = "COTIZ_" . $pedidoId . "_" . time() . "." . $ext;
    if (move_uploaded_file($_FILES['cotizacion']['tmp_name'], $cotizacionesDir . $fileName)) {
        if ($esEdicion && $currentCotizacionPath && file_exists(__DIR__."/../".ltrim($currentCotizacionPath, './'))) {
            unlink(__DIR__."/../".ltrim($currentCotizacionPath, './'));
        }
        $cotizacionPath = "uploads/cotizaciones/" . $fileName;
    }
}

try {
    $params = [
        ':nombre'       => htmlspecialchars($_POST['nombre']),
        ':telefono'     => htmlspecialchars($_POST['telefono']),
        ':status'       => htmlspecialchars($_POST['status']),
        ':fechaInicio'  => $_POST['fechaInicio'],
        ':fechaEntrega' => $_POST['fechaEntrega'],
        ':costo'        => (float)$_POST['costo'],
        ':anticipo'     => (float)$_POST['anticipo'],
        ':tallas'       => json_encode($tallas, JSON_UNESCAPED_UNICODE), // Ahora incluye 'prenda_id'
        ':instrucciones'=> htmlspecialchars($_POST['instrucciones']),
        ':imagenes'     => json_encode($imagenes, JSON_UNESCAPED_UNICODE),
        ':paletaColor'  => $paletaPath,
        ':cotizacion'   => $cotizacionPath,
        ':id'           => $pedidoId
    ];

    if ($esEdicion) {
        $sql = "UPDATE pedidos SET nombre=:nombre, telefono=:telefono, status=:status, fechaInicio=:fechaInicio, 
                fechaEntrega=:fechaEntrega, costo=:costo, anticipo=:anticipo, tallas=:tallas, instrucciones=:instrucciones,
                imagenes=:imagenes, paletaColor=:paletaColor, cotizacion=:cotizacion WHERE id=:id";
    } else {
        $sql = "INSERT INTO pedidos (id, nombre, telefono, status, fechaInicio, fechaEntrega, costo, anticipo, tallas, instrucciones, imagenes, paletaColor, cotizacion) 
                VALUES (:id, :nombre, :telefono, :status, :fechaInicio, :fechaEntrega, :costo, :anticipo, :tallas, :instrucciones, :imagenes, :paletaColor, :cotizacion)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Redirigir de vuelta al panel de administración (admin.php) en lugar de showOrder
    header("Location: ../admin.php?success=true"); 
    exit;
} catch (Exception $e) { echo "Error: " . $e->getMessage(); exit; }
?>