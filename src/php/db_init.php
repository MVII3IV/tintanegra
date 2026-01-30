<?php
require_once 'config.php';

try {
    // ==========================================
    // 1. TABLA PEDIDOS (Base del sistema)
    // ==========================================
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

    // ==========================================
    // 2. TABLA USUARIOS (Login)
    // ==========================================
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

    // ==========================================
    // 3. TABLA CATÁLOGO (Nueva funcionalidad)
    // ==========================================
    $sqlCatalogo = "CREATE TABLE IF NOT EXISTS catalogo_prendas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_prenda VARCHAR(100) NOT NULL, 
        marca VARCHAR(100),                
        modelo VARCHAR(100),               
        genero ENUM('Unisex', 'Dama', 'Niño') DEFAULT 'Unisex',
        costo_base DECIMAL(10, 2) DEFAULT 0.00,
        activo TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlCatalogo);
    echo "✅ Tabla 'catalogo_prendas' lista.<br>";

    // ==========================================
    // 4. MIGRACIONES (Actualizar campos faltantes)
    // ==========================================
    
    // Lista de columnas nuevas para 'pedidos'
    $nuevasColumnasPedidos = [
        "telefono"      => "ALTER TABLE pedidos ADD COLUMN telefono VARCHAR(20) AFTER nombre",
        "instrucciones" => "ALTER TABLE pedidos ADD COLUMN instrucciones TEXT AFTER tallas",
        "cotizacion"    => "ALTER TABLE pedidos ADD COLUMN cotizacion VARCHAR(255) AFTER paletaColor"
    ];

    foreach ($nuevasColumnasPedidos as $columna => $query) {
        $check = $pdo->query("SHOW COLUMNS FROM pedidos LIKE '$columna'");
        if ($check->rowCount() == 0) {
            $pdo->exec($query);
            echo "➕ Campo '$columna' añadido a PEDIDOS con éxito.<br>";
        } else {
            echo "ℹ️ Campo '$columna' ya existe en pedidos.<br>";
        }
    }

    // Lista de columnas nuevas para 'catalogo_prendas'
    $nuevasColumnasCatalogo = [
        "descripcion" => "ALTER TABLE catalogo_prendas ADD COLUMN descripcion TEXT AFTER modelo"
    ];

    foreach ($nuevasColumnasCatalogo as $columna => $query) {
        $check = $pdo->query("SHOW COLUMNS FROM catalogo_prendas LIKE '$columna'");
        if ($check->rowCount() == 0) {
            $pdo->exec($query);
            echo "➕ Campo '$columna' añadido a CATÁLOGO con éxito.<br>";
        } else {
            echo "ℹ️ Campo '$columna' ya existe en catálogo.<br>";
        }
    }

    echo "<hr>🚀 <strong>Sincronización de Base de Datos completada correctamente.</strong>";

} catch (PDOException $e) {
    die("❌ Error en la inicialización: " . $e->getMessage());
}
?>