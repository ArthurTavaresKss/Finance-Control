<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];
    $params = $_GET;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['modal_username'];
        $email = $_POST['modal_email'];
        $localUser = getUserById($pdo, $idUsuario);

        if ($username == $localUser['username'] && $email == $localUser['email']) {
            
            $params['status'] = 'usuario_inalterado';

        } else {
            $userByUsername = getUserByUsername($pdo, $username);
            $userByEmail = getUserByEmail($pdo, $email);

            if ($userByUsername && $userByUsername['id'] != $idUsuario) {
                $params['status'] = 'username_existente';
            } elseif ($userByEmail && $userByEmail['id'] != $idUsuario) {
                $params['status'] = 'email_existente';
            } else {
                $sucesso = updateUserUsernameAndEmailById($pdo, $idUsuario, $username, $email);
                $params['status'] = $sucesso ? 'usuario_atualizado' : 'erro_atualizacao';
            }
        }

        $queryString = http_build_query($params);
        redirect('../perfil?' . $queryString);

    } else {
        redirect('../perfil');
    }
    
?>