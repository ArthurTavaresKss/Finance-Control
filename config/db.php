<?php
$host = getenv('DB_HOST') ?: 'localhost'; 
$db   = 'financecontrol'; 
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
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
    @session_start();
    $_SESSION['erro_db'] = $e->getMessage(); 
    
    require_once __DIR__ . '/../public/500.php'; 
    die();
}