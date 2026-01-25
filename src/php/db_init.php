<?php
require_once 'config.php';

try {
    // 1. Asegurar que la tabla pedidos existe con la estructura base y UTF8
    $sqlPedidos = "CREATE TABLE IF NOT EXISTS pedidos (
        id varchar(255) NOT NULL,
        nombre varchar(255) DEFAULT NULL,
        status varchar(50) DEFAULT NULL,
        fechaInicio date DEFAULT NULL,
        fechaEntrega date DEFAULT NULL,
        costo decimal(10,2) DEFAULT NULL,
        anticipo decimal(10,2) DEFAULT NULL,
        tallas json DEFAULT NULL,
        imagenes json DEFAULT NULL,
        paletaColor varchar(255) DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sqlPedidos);
    echo "✅ Tabla 'pedidos' lista.<br>";

    // 2. Asegurar que la tabla usuarios existe
    $sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id int(11) NOT NULL AUTO_INCREMENT,
        username varchar(50) NOT NULL,
        password_hash varchar(255) NOT NULL,
        email varchar(100) DEFAULT NULL,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlUsuarios);
    echo "✅ Tabla 'usuarios' lista.<br>";

    // 3. MIGRACIÓN: Agregar campos nuevos si no existen
    $nuevasColumnas = [
        "telefono"      => "ALTER TABLE pedidos ADD COLUMN telefono VARCHAR(20) AFTER nombre",
        "instrucciones" => "ALTER TABLE pedidos ADD COLUMN instrucciones TEXT AFTER tallas",
        "cotizacion"    => "ALTER TABLE pedidos ADD COLUMN cotizacion VARCHAR(255) AFTER paletaColor"
    ];

    foreach ($nuevasColumnas as $columna => $query) {
        $check = $pdo->query("SHOW COLUMNS FROM pedidos LIKE '$columna'");
        if ($check->rowCount() == 0) {
            $pdo->exec($query);
            echo "➕ Campo '$columna' añadido con éxito.<br>";
        } else {
            echo "ℹ️ El campo '$columna' ya existe, saltando...<br>";
        }
    }

    echo "🚀 **Sincronización completada con éxito.**";

} catch (PDOException $e) {
    die("❌ Error en la inicialización: " . $e->getMessage());
}