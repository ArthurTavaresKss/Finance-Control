<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    $usuario = getUserById($pdo, $idUsuario);

    $status = $_GET['status'] ?? '';

    $mostrarModal = false;
    $modalTitulo = '';
    $modalMensagem = '';

    if (!empty($status)) {
        $mostrarModal = true;

        switch ($status) {
            case 'username_existente':
                $modalTitulo = 'Ops! Usuário já existe';
                $modalMensagem = 'O nome de usuário escolhido já está em uso por outra pessoa. Por favor, tente outro.';
                break;

            case 'email_existente':
                $modalTitulo = 'E-mail indisponível';
                $modalMensagem = 'O endereço de e-mail informado já está cadastrado em outra conta.';
                break;

            case 'usuario_atualizado':
                $modalTitulo = 'Sucesso!';
                $modalMensagem = 'Seus dados de perfil foram atualizados com sucesso.';
                break;

            case 'erro_atualizacao':
                $modalTitulo = 'Erro no servidor';
                $modalMensagem = 'Não foi possível atualizar seus dados neste momento. Tente novamente mais tarde.';
                break;
                
            default:
                // Caso venha um status desconhecido, não mostra nada
                $mostrarModal = false;
                break;
        }
    }
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
                <input type="text" id="username" name="username" required placeholder="Seu nome de usuário"
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
    <dialog id="modalStatus" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
        <h2><?= htmlspecialchars($modalTitulo) ?></h2>
        <p><?= htmlspecialchars($modalMensagem) ?></p>
        <button type="button" onclick="document.getElementById('modalStatus').close()">Fechar</button>
    </dialog>
    <?php if ($mostrarModal): ?>
        <script>
            document.getElementById('modalStatus').showModal();
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('status');
                window.history.replaceState({ path: url.href }, '', url.href);
            }
        </script>
    <?php else: ?>
        <script>
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('status');
                window.history.replaceState({ path: url.href }, '', url.href);
            }
        </script>
    <?php endif; ?>
</body>
</html>