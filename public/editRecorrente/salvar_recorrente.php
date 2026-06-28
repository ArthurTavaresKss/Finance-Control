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
        $dia_formatado = str_pad($dia_transacao, 2, "0", STR_PAD_LEFT);
        
        // --- VALIDAÇÃO DA DATA DE INÍCIO VERÍDICA ---
        if (empty($_POST['data_transacao_inicio']) || !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $_POST['data_transacao_inicio'])) {
            $_SESSION['status_recorrente'] = 'data_termino_maior'; // Dispara o modal de erro de data
            redirect("../recorrentes");
            exit;
        }

        $partes_inicio = explode('/', $_POST['data_transacao_inicio']);
        $mes_inicio = $partes_inicio[0];
        $ano_inicio = $partes_inicio[1];
        $data_transacao_inicio = $ano_inicio . '-' . $mes_inicio . '-' . $dia_formatado;

        if (!empty($_POST['data_transacao_termino'])) {
            
            if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $_POST['data_transacao_termino'])) {
                $_SESSION['status_recorrente'] = 'data_termino_maior';
                redirect("../recorrentes");
                exit;
            }

            $partes_termino = explode('/', $_POST['data_transacao_termino']);
            $mes_termino = $partes_termino[0];
            $ano_termino = $partes_termino[1];
            $data_transacao_termino = $ano_termino . '-' . $mes_termino . '-' . $dia_formatado;
            
            if (strtotime($data_transacao_termino) <= strtotime($data_transacao_inicio)) {
                $_SESSION['status_recorrente'] = 'data_termino_maior';
                redirect("../recorrentes");
                exit;
            }
        } else {
            $data_transacao_termino = null;
        }

        $sucesso = insertRecurring($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria, $dia_transacao, $data_transacao_inicio, $data_transacao_termino);
        
        if ($sucesso) {
            $_SESSION['status_recorrente'] = 'recorrente_adicionada';
        } else {
            $_SESSION['status_recorrente'] = 'erro_recorrente_adicionada';
        }
        
        redirect("../recorrentes");
    } else {
        redirect("../recorrentes");
    }
?>