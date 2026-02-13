<?php
// src/php/get_analytics.php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // 1. Determinar qué año consultar
    $anioSolicitado = isset($_GET['year']) ? intval($_GET['year']) : (int)date('Y');
    
    // 2. Obtener lista de años disponibles (CAMBIO: BASADO EN FECHA INICIO)
    // Buscamos los años basándonos en cuándo se REGISTRARON los pedidos concretados
    $stmtYears = $pdo->query("SELECT DISTINCT YEAR(fechaInicio) as anio FROM pedidos WHERE fechaInicio IS NOT NULL AND status = 'Entregada' ORDER BY anio DESC");
    $aniosDisponibles = $stmtYears->fetchAll(PDO::FETCH_COLUMN);

    // Si la base de datos está vacía, al menos ponemos el año actual
    if (empty($aniosDisponibles)) {
        $aniosDisponibles = [$anioSolicitado];
    }
    // Asegurarnos que el año solicitado esté en la lista
    if (!in_array($anioSolicitado, $aniosDisponibles)) {
        array_unshift($aniosDisponibles, $anioSolicitado);
        rsort($aniosDisponibles); 
    }

    // 3. Generar esqueleto de meses
    $mesesES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    $mesesData = [];
    for ($m = 1; $m <= 12; $m++) {
        $claveMes = $anioSolicitado . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
        $nombreMes = $mesesES[$m]; 
        
        $mesesData[$claveMes] = [
            'label' => $nombreMes, 
            'ventas' => 0,
            'pedidos' => 0,
            'prendas' => 0
        ];
    }

    // 4. Consultar datos (CAMBIO: USAR FECHA INICIO)
    // Ahora agrupamos por el mes de REGISTRO, pero mantenemos el status 'Entregada'
    // para contar solo dinero real seguro.
    $query = "
        SELECT 
            DATE_FORMAT(fechaInicio, '%Y-%m') as mes_anio,  /* <--- AGRUPAR POR REGISTRO */
            costo,
            tallas
        FROM pedidos 
        WHERE YEAR(fechaInicio) = :anio                     /* <--- FILTRAR AÑO DE REGISTRO */
        AND status = 'Entregada'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':anio', $anioSolicitado);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Procesar datos
    foreach ($resultados as $row) {
        $key = $row['mes_anio'];
        if (isset($mesesData[$key])) {
            $mesesData[$key]['ventas'] += (float)$row['costo'];
            $mesesData[$key]['pedidos']++;

            $tallas = json_decode($row['tallas'], true);
            if (is_array($tallas)) {
                // Verificar si viene como string JSON o array directo
                if (count($tallas) > 0 && is_string($tallas[0])) { 
                     // Caso raro doble encoding, pero por seguridad
                }
                
                foreach ($tallas as $item) {
                    $qty = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;
                    $mesesData[$key]['prendas'] += $qty;
                }
            }
        }
    }

    // 6. Preparar respuesta
    $labels = []; $ventas = []; $pedidos = []; $prendas = [];
    foreach ($mesesData as $mes) {
        $labels[] = $mes['label'];
        $ventas[] = $mes['ventas'];
        $pedidos[] = $mes['pedidos'];
        $prendas[] = $mes['prendas'];
    }

    echo json_encode([
        'success' => true,
        'year' => $anioSolicitado,
        'available_years' => $aniosDisponibles,
        'labels' => $labels,
        'ventas' => $ventas,
        'pedidos' => $pedidos,
        'prendas' => $prendas
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>