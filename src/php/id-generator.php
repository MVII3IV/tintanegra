<?php

function generateId() {
    // Obtenemos el año actual (2) y el día del año (001-366)
    // Ej: Para hoy sería algo como "26025"
    $prefix = date('y') . date('z'); 

    // Generamos un hash único basado en el tiempo exacto en microsegundos
    // y lo acortamos a 4 caracteres alfanuméricos en mayúsculas
    $uniqueSeed = uniqid();
    $shortHash = strtoupper(substr(md5($uniqueSeed), 0, 4));

    // El resultado será algo como: 26025-A1B2
    $id = "{$prefix}-{$shortHash}";

    return $id;
}

?>
