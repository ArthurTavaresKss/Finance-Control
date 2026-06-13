<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['idUsuario'])) {
    redirect("/Finance-Control/login.php");
    exit;
}
?>