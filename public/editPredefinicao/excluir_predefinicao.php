<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idPredefinicao = ($_GET['id']);
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = deletePredefinitionByUserIdAndId($pdo, $idUsuario, $idPredefinicao);
        if ($sucesso) {
            $_SESSION['status_predefinicao'] = 'predefinicao_deletada';
        } else {
            $_SESSION['status_predefinicao'] = 'erro_predefinicao_deletada';
        }
        redirect("../predefinicoes?");
    } else {
        redirect("../predefinicoes");
    }
?>