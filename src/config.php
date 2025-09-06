<?php
// config.php

$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$host = (strpos($docRoot, '/var/www/html') !== false) ? 'db' : 'localhost';

$dbname  = "u182841428_tintanegra";
$user    = "u182841428_admin";
$pass    = "Tintanegra20855!";
$charset = "utf8mb4"; // 👈 aquí defines charset

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset"; // 👈 usa $dbname
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
