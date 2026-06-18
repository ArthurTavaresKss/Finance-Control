<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];
    $params = $_GET;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $senhaAtual = $_POST['modal_senha_atual'];
        $senhaNova = $_POST['modal_senha_nova'];
        $senhaNovaConfirmada = $_POST['modal_senha_nova_confirmada'];

        $localUser = getUserById($pdo, $idUsuario);

        if ($senhaNova !== $senhaNovaConfirmada) {
            
            $params['status'] = 'senhas_nao_coincidem';

        } elseif (!password_verify($senhaAtual, $localUser['senha'])) {
            
            $params['status'] = 'senha_atual_incorreta';

        } elseif (password_verify($senhaNova, $localUser['senha'])) {

            $params['status'] = 'senha_igual_anterior';

        } else {
            $novaSenhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
            $sucesso = UpdateUserPasswordById($pdo, $idUsuario, $novaSenhaHash);
            $params['status'] = $sucesso ? 'senha_atualizada' : 'erro_atualizacao';
        }
        

        $queryString = http_build_query($params);
        redirect('../perfil.php?' . $queryString);

    } else {
        redirect('../perfil.php');
    }
    
?>