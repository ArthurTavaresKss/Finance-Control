<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idUsuario = $_SESSION['idUsuario'];
        
        $tipo = $_POST['tipo'];
        $descricao = trim($_POST['descricao']);
        $valor = $_POST['valor'];
        
        $categoria = $_POST['categoria'];
        if ($categoria === 'nova_categoria') {
            $categoria = trim($_POST['nova_categoria']);
        }
        
        $dia_transacao = (int)$_POST['dia_transacao'];
        $data_transacao_inicio = $_POST['data_transacao_inicio'];

        $data_transacao_termino = !empty($_POST['data_transacao_termino']) ? $_POST['data_transacao_termino'] : null;

        $sucesso = insertRecurring($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria, $dia_transacao, $data_transacao_inicio, $data_transacao_termino);
        
        if ($sucesso) {
            $_SESSION['status_recorrente'] = 'recorrente_adicionada';
        } else {
            $_SESSION['status_recorrente'] = 'erro_recorrente_adicionada';
        }
        
        redirect("../recorrentes.php");
    } else {
        redirect("../recorrentes.php");
    }
?>