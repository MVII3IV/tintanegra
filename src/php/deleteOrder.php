<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success'=>false, 'message'=>'Método no permitido']);
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo json_encode(['success'=>false, 'message'=>'No se proporcionó ID']);
    exit;
}

try {
    // 1. Obtener las rutas de los archivos antes de borrar el registro
    $stmtFile = $pdo->prepare("SELECT imagenes, paletaColor FROM pedidos WHERE id = ?");
    $stmtFile->execute([$id]);
    $pedido = $stmtFile->fetch();

    if ($pedido) {
        $filesToDelete = [];
        
        // Decodificar imágenes del diseño
        $imagenes = json_decode($pedido['imagenes'], true) ?: [];
        foreach ($imagenes as $ruta) { $filesToDelete[] = $ruta; }
        
        // Añadir paleta de color
        if ($pedido['paletaColor']) { $filesToDelete[] = $pedido['paletaColor']; }

        foreach ($filesToDelete as $rutaEnBD) {
            /**
             * LÓGICA DE RUTA ABSOLUTA:
             * 1. Quitamos cualquier "../" o "./" que pudiera tener la ruta guardada.
             * 2. Construimos la ruta desde la raíz del documento para que PHP no falle.
             */
            $rutaLimpia = ltrim($rutaEnBD, './'); 
            $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . '/' . $rutaLimpia;

            if (file_exists($rutaFisica)) {
                unlink($rutaFisica);
            }
        }

        // 2. Eliminar el registro de la base de datos
        $stmtDelete = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmtDelete->execute([$id]);

        if ($stmtDelete->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'El registro no se pudo eliminar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}