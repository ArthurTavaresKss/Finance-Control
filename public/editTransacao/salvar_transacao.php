<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = ($_POST['tipo']);
        $descricao = trim($_POST['descricao']);
        $valor = ($_POST['valor']);
        $categoria = ($_POST['categoria']);
        if ($categoria === 'nova_categoria') {
            $categoria = trim($_POST['nova_categoria']);
        }
        $data_transacao = ($_POST['data_transacao']);
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = insertTransacao($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria, $data_transacao);
        if ($sucesso) {
            $_SESSION['status_transacao'] = 'transacao_adicionada';
        } else {
            $_SESSION['status_transacao'] = 'erro_transacao_adicionada';
        }
        redirect("../transacoes?");
    } else {
        redirect("../transacoes");
    }
?>