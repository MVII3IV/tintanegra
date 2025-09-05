<?php
header('Content-Type: application/json');

$host = "db";
$db = "tintanegra";
$user = "user";
$pass = "password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e){
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Procesar tallas
$tallas = [];
if(isset($_POST['talla']) && is_array($_POST['talla'])){
    foreach($_POST['talla'] as $i => $t){
        if($t){
            $tallas[] = [
                'talla' => $t,
                'cantidad' => (int)$_POST['cantidad'][$i],
                'color' => $_POST['color'][$i]
            ];
        }
    }
}

// Procesar imágenes
$imagenes = [];
if(isset($_FILES['imagenes'])){
    foreach($_FILES['imagenes']['tmp_name'] as $i => $tmp_name){
        $name = time().'_'.basename($_FILES['imagenes']['name'][$i]);
        move_uploaded_file($tmp_name, 'uploads/'.$name);
        $imagenes[] = 'uploads/'.$name;
    }
}

// Insertar en DB
$stmt = $pdo->prepare("INSERT INTO pedidos (nombre,status,fechaInicio,fechaEntrega,costo,anticipo,tallas,imagenes)
    VALUES (:nombre,:status,:fechaInicio,:fechaEntrega,:costo,:anticipo,:tallas,:imagenes)");

try{
    $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':status' => $_POST['status'],
        ':fechaInicio' => $_POST['fechaInicio'],
        ':fechaEntrega' => $_POST['fechaEntrega'],
        ':costo' => $_POST['costo'],
        ':anticipo' => $_POST['anticipo'],
        ':tallas' => json_encode($tallas, JSON_UNESCAPED_UNICODE),
        ':imagenes' => json_encode($imagenes, JSON_UNESCAPED_UNICODE)
    ]);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
}catch(Exception $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
