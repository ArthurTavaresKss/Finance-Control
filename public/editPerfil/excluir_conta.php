<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];
    $params = $_GET;
    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $senha = $_POST['modal_senha_exclusao'];

        $localUser = getUserById($pdo, $idUsuario);


        if (!password_verify($senha, $localUser['senha'])) {
            
            $params['status'] = 'senha_confirmacao_incorreta';

        } else {
            $sucesso = UpdateUserActiveById($pdo, $idUsuario, 0);
        }
        
        if (!$sucesso) {
            $params['status'] = 'erro_atualizacao';
            $queryString = http_build_query($params);
            redirect('../perfil.php?' . $queryString);
        } else {
            redirect('../logout.php');
        }

    } else {
        redirect('../perfil.php');
    }
    
?>
