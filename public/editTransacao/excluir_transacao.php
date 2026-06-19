<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $params = [];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idTransacao = ($_GET['id']);
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = deleteTransactionByUserIdAndId($pdo, $idUsuario, $idTransacao);
        if ($sucesso) {
            $params['status'] = 'transacao_deletada';
        } else {
            $params['status'] = 'erro_transacao_deletada';
        }
        $queryString = http_build_query($params);
        redirect("../index.php?" . $queryString);
    } else {
        redirect("../index.php");
    }
?>