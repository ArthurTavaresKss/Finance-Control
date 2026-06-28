<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idRecorrente = $_GET['id'];
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = deleteRecurringByUserIdAndId($pdo, $idUsuario, $idRecorrente);
        
        if ($sucesso) {
            $_SESSION['status_recorrente'] = 'recorrente_deletada';
        } else {
            $_SESSION['status_recorrente'] = 'erro_recorrente_deletada';
        }
        
        redirect("../recorrentes");
    } else {
        redirect("../recorrentes");
    }
?>