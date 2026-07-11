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
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = insertPredefinition($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria);
        if ($sucesso) {
            $_SESSION['status_predefinicao'] = 'predefinicao_adicionada';
        } else {
            $_SESSION['status_predefinicao'] = 'erro_predefinicao_adicionada';
        }
        redirect("../predefinicoes?");
    } else {
        redirect("../predefinicoes");
    }
?>