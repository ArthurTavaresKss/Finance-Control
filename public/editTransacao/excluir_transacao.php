<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idTransacao = ($_GET['id']);
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = deleteTransactionByUserIdAndId($pdo, $idUsuario, $idTransacao);
        if ($sucesso) {
            $_SESSION['status_transacao'] = 'transacao_deletada';
        } else {
            $_SESSION['status_transacao'] = 'erro_transacao_deletada';
        }
        redirect("../transacoes?");
    } else {
        redirect("../transacoes");
    }
?>