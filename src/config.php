<?php
// config.php

$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$host = (strpos($docRoot, '/var/www/html') !== false) ? 'db' : 'localhost';

$dbname = "u182841428_tintanegra";
$user = "u182841428_admin";
$pass = "Tintanegra20855!";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
