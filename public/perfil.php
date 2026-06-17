<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    $usuario = getUserById($pdo, $idUsuario);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
    <title>Perfil</title>
</head>
<body>
    <h1><?= $usuario['username'] ?></h1>
    <img src="assets/img/foto_perfil.jpg" alt="Foto de perfil" width="180" height="180" style="border-radius: 50%; object-fit: cover;">
    <h3><?= $usuario['email'] ?></h3>
    <button type="button" onclick="document.getElementById('modalEditarInformacoes').showModal()">
        Editar Informações
    </button>
    <button>Alterar Senha</button>
    <button>Excluir Conta</button>
    <br><br>
    <dialog id="modalEditarInformacoes" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
        <h2>Editar Informações</h2>
        
        <form method="POST" action="editPerfil/editar_perfil.php">
            <p>
                <label for="modal_usuario">Usuário:</label><br>
                <input type="text" id="modal_usuario" name="usuario" required placeholder="Seu nome de usuário"
                value="<?= $usuario['username'] ?>">
            </p>
            
            <p>
                <label for="modal_email">E-mail:</label><br>
                <input type="email" id="modal_email" name="email" required placeholder="seu@email.com"
                value="<?= $usuario['email'] ?>">
            </p>
            
            <p style="margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modalEditarInformacoes').close()">Cancelar</button>
                <button type="submit">Salvar</button>
            </p>
        </form>
    </dialog>
</body>
</html>