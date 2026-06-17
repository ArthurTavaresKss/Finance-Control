<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
    } else {
        redirect('../perfil.php');
    }
    
?>