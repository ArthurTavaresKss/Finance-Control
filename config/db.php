<?php
$host = 'localhost';
$db = 'financecontrol';
$user = 'financeAdmin';
$pass = 'GBwk4CbIfA38QR3pTK';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch(PDOException $e) {
    echo("ERRO DE CONEXÃO: " . $e->getMessage() . "<br>");
    die("Acho que algo está errado com o banco..");
}
?>