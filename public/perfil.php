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

        case 'usuario_inalterado':
            $modalTitulo = 'Nenhuma alteração';
            $modalMensagem = 'Você não modificou nenhum dado do seu perfil.';
            break;

        case 'senhas_nao_coincidem':
            $modalTitulo = 'Senhas diferentes';
            $modalMensagem = 'A nova senha e a confirmação não são iguais. Digite-as novamente com atenção.';
            break;

        case 'senha_atual_incorreta':
            $modalTitulo = 'Senha atual incorreta';
            $modalMensagem = 'A senha atual informada não confere com a senha da sua conta.';
            break;

        case 'senha_igual_anterior':
            $modalTitulo = 'Nova senha idêntica';
            $modalMensagem = 'Sua nova senha não pode ser igual à senha que você já usa atualmente.';
            break;

        case 'senha_atualizada':
            $modalTitulo = 'Senha alterada!';
            $modalMensagem = 'Sua senha foi atualizada com sucesso. Utilize a nova senha no seu próximo login.';
            break;
        
        case 'senha_confirmacao_incorreta':
            $modalTitulo = 'Senha Incorreta';
            $modalMensagem = 'A senha informada não confere com a senha da sua conta.';
            break;

        case 'erro_atualizacao':
            $modalTitulo = 'Erro no servidor';
            $modalMensagem = 'Não foi possível salvar os dados neste momento. Tente novamente mais tarde.';
            break;
            
        default:
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
    <h1>Finance Control</h1>
    <nav>
        <a href="index.php">Transações</a> | 
        <a href="dashboards.php">Dashboards</a> | 
        <a href="perfil.php">Perfil</a> | 
        <a href="logout.php">Sair</a>
    </nav>
    <br>
    <h1><?= $usuario['username'] ?></h1>
    <img src="assets/img/foto_perfil.jpg" alt="Foto de perfil" width="180" height="180" style="border-radius: 50%; object-fit: cover;">
    <h3><?= $usuario['email'] ?></h3>
    <button type="button" onclick="document.getElementById('modalEditarInformacoes').showModal()">
        Editar Informações
    </button>
    <button type="button" onclick="document.getElementById('modalEditarSenha').showModal()">
        Alterar Senha
    </button>
    <button type="button" onclick="document.getElementById('modalConfirmarExclusao').showModal()">
        Excluir Conta
    </button>
    <br><br>
    <dialog id="modalEditarInformacoes" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
        <h2>Editar Informações</h2>
        
        <form method="POST" action="editPerfil/editar_perfil.php">
            <p>
                <label for="modal_username">Usuário:</label><br>
                <input type="text" id="modal_username" name="modal_username" required placeholder="Seu nome de usuário"
                value="<?= sanitizeInput($usuario['username']) ?>" autocomplete="name">
            </p>
            
            <p>
                <label for="modal_email">E-mail:</label><br>
                <input type="email" id="modal_email" name="modal_email" required placeholder="seu@email.com"
                value="<?= sanitizeInput($usuario['email']) ?>" autocomplete="username">
            </p>
            
            <p style="margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modalEditarInformacoes').close()">Cancelar</button>
                <button type="submit">Salvar</button>
            </p>
        </form>
    </dialog>
    <dialog id="modalEditarSenha" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
        <h2>Editar Senha</h2>
        
        <form method="POST" action="editPerfil/editar_senha.php">
            <input type="text" name="username_dummy" value="<?= sanitizeInput($usuario['email']) ?>" autocomplete="username" style="display:none;">

            <p>
                <label for="modal_senha_atual">Senha Atual:</label><br>
                <input type="password" id="modal_senha_atual" name="modal_senha_atual" required placeholder="Digite a sua senha atual" autocomplete="current-password">
            </p>
            
            <p>
                <label for="modal_senha_nova">Nova Senha:</label><br>
                <input type="password" id="modal_senha_nova" name="modal_senha_nova" required placeholder="Digite sua nova senha" autocomplete="new-password">
            </p>

            <p>
                <label for="modal_senha_nova_confirmada">Confirme a Nova Senha:</label><br>
                <input type="password" id="modal_senha_nova_confirmada" name="modal_senha_nova_confirmada" required placeholder="Confirme a sua nova senha" autocomplete="new-password">
            </p>
            
            <p style="margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modalEditarSenha').close()">Cancelar</button>
                <button type="submit">Salvar</button>
            </p>
        </form>
    </dialog>
    <dialog id="modalConfirmarExclusao" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
        <h2>Confirmar Exclusão de Conta</h2>
        
        <form method="POST" action="editPerfil/excluir_conta.php">

            <p>
                Tem certeza que deseja excluir sua conta?
            </p>

            <p>
                <label for="modal_senha_exclusao">Digite sua senha para confirmar:</label><br>
                <input type="password" id="modal_senha_exclusao" name="modal_senha_exclusao" required placeholder="Digite sua senha" autocomplete="current-password">
            </p>
            
            <p style="margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modalConfirmarExclusao').close()">Cancelar</button>
                <button type="submit">Confirmar</button>
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