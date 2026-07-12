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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control - Perfil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body id="app-page">

    <header class="app-topbar">
        <svg class="ticker-line" viewBox="0 0 600 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <polyline points="0,150 60,140 120,160 180,120 240,135 300,90 360,110 420,70 480,85 540,55 600,65"
                fill="none" stroke="#16a05f" stroke-width="1.5" opacity="0.35" />
        </svg>

        <div class="app-brand">
            <img src="assets/img/logo.png" alt="Finance Control" class="logo-mark">
        </div>

        <nav class="app-nav">
            <a href="transacoes">Transações</a>
            <a href="recorrentes">Transações Recorrentes</a>
            <a href="predefinicoes">Predefinições</a>
            <a href="dashboards">Dashboards</a>
            <a href="perfil" class="active">Perfil</a>
            <span class="app-nav-divider"></span>
            <a href="logout" class="logout">Sair</a>
        </nav>
    </header>

    <main class="app-content">

        <div class="app-page-header">
            <div>
                <span class="eyebrow">Sua conta</span>
                <h2>Perfil</h2>
            </div>
        </div>

        <div class="app-card">
            <div class="profile-header-card">
                <img src="assets/img/foto_perfil.jpg" alt="Foto de perfil" class="profile-avatar">

                <div class="profile-header-info">
                    <h2><?= sanitizeInput($usuario['username']) ?></h2>
                    <p><?= sanitizeInput($usuario['email']) ?></p>
                </div>

                <div class="profile-header-actions">
                    <button type="button" class="btn-primary" onclick="document.getElementById('modalEditarInformacoes').showModal()">
                        Editar informações
                    </button>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('modalEditarSenha').showModal()">
                        Alterar senha
                    </button>
                </div>

                <div class="profile-danger-zone">
                    <span>Excluir sua conta é uma ação permanente e não pode ser desfeita.</span>
                    <button type="button" class="btn-text-danger" onclick="document.getElementById('modalConfirmarExclusao').showModal()">
                        Excluir conta
                    </button>
                </div>
            </div>
        </div>

    </main>

    <dialog id="modalEditarInformacoes" class="app-dialog">
        <div class="dialog-inner">
            <h2>Editar Informações</h2>

            <form method="POST" action="editPerfil/editar_perfil">
                <div class="input-group">
                    <label for="modal_username">Usuário</label>
                    <input type="text" id="modal_username" name="modal_username" required placeholder="Seu nome de usuário"
                        value="<?= sanitizeInput($usuario['username']) ?>" autocomplete="name">
                </div>

                <div class="input-group">
                    <label for="modal_email">E-mail</label>
                    <input type="email" id="modal_email" name="modal_email" required placeholder="seu@email.com"
                        value="<?= sanitizeInput($usuario['email']) ?>" autocomplete="username">
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('modalEditarInformacoes').close()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="modalEditarSenha" class="app-dialog">
        <div class="dialog-inner">
            <h2>Editar Senha</h2>

            <form method="POST" action="editPerfil/editar_senha">
                <input type="text" name="username_dummy" value="<?= sanitizeInput($usuario['email']) ?>" autocomplete="username" style="display:none;">

                <div class="input-group">
                    <label for="modal_senha_atual">Senha atual</label>
                    <input type="password" id="modal_senha_atual" name="modal_senha_atual" required placeholder="Digite a sua senha atual" autocomplete="current-password">
                </div>

                <div class="input-group">
                    <label for="modal_senha_nova">Nova senha</label>
                    <input type="password" id="modal_senha_nova" name="modal_senha_nova" required placeholder="Digite sua nova senha" autocomplete="new-password">
                </div>

                <div class="input-group">
                    <label for="modal_senha_nova_confirmada">Confirme a nova senha</label>
                    <input type="password" id="modal_senha_nova_confirmada" name="modal_senha_nova_confirmada" required placeholder="Confirme a sua nova senha" autocomplete="new-password">
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('modalEditarSenha').close()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="modalConfirmarExclusao" class="app-dialog">
        <div class="dialog-inner">
            <h2>Confirmar Exclusão de Conta</h2>

            <form method="POST" action="editPerfil/excluir_conta">
                <p style="font-size: 13.5px; color: var(--ink-soft); line-height: 1.6; margin-bottom: 18px; position: relative; z-index: 1;">
                    Tem certeza que deseja excluir sua conta? Essa ação é permanente e não pode ser desfeita.
                </p>

                <div class="input-group">
                    <label for="modal_senha_exclusao">Digite sua senha para confirmar</label>
                    <input type="password" id="modal_senha_exclusao" name="modal_senha_exclusao" required placeholder="Digite sua senha" autocomplete="current-password">
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('modalConfirmarExclusao').close()">Cancelar</button>
                    <button type="submit" class="btn-danger">Confirmar exclusão</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="modalStatus" class="app-dialog">
        <div class="dialog-inner status-dialog-body">
            <h2><?= sanitizeInput($modalTitulo) ?></h2>
            <p><?= sanitizeInput($modalMensagem) ?></p>
            <div class="dialog-actions" style="justify-content: center;">
                <button type="button" class="btn-primary" onclick="document.getElementById('modalStatus').close()">Fechar</button>
            </div>
        </div>
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
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</html>