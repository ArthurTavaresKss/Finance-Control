<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/functions.php";

$error = isset($_SESSION['cadastro_error']) ? $_SESSION['cadastro_error'] : null;
unset($_SESSION['cadastro_error']);

$sucesso = isset($_SESSION['cadastro_sucesso']) ? $_SESSION['cadastro_sucesso'] : null;
unset($_SESSION['cadastro_sucesso']);

$old_inputs = isset($_SESSION['old_inputs']) ? $_SESSION['old_inputs'] : ['username' => '', 'email' => ''];
unset($_SESSION['old_inputs']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senha_confirmada = $_POST['senha_confirmada'];

    $_SESSION['old_inputs'] = [
        'username' => $username,
        'email' => $email
    ];

    try {
        $userByEmail = getUserByEmail($pdo, $email);
        $userByUsername = getUserByUsername($pdo, $username);

        if ($userByEmail) {
            $_SESSION['cadastro_error'] = "Email já cadastrado. Use outro Email.";
            redirect('cadastro');
        } elseif ($userByUsername) {
            $_SESSION['cadastro_error'] = "Nome de usuário já cadastrado. Crie outro nome.";
            redirect('cadastro');
        } elseif ($senha != $senha_confirmada) {
            $_SESSION['cadastro_error'] = "Senhas não coincidem. Tente novamente.";
            redirect('cadastro');
        } else {
            unset($_SESSION['old_inputs']);
            $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);
            insertUser($pdo, $username, $email, $senhaCriptografada);
            
            $_SESSION['cadastro_sucesso'] = true;
            redirect('cadastro');
        }
    } catch (PDOException $e) {
        handleDBException($e);
    } catch (Exception $e) {
        $_SESSION['cadastro_error'] = $e->getMessage();
        redirect('cadastro');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control - Cadastro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body id="login-page">

    <div id="login-wrapper" class="reverse">

        <!-- Painel do formulário: agora à esquerda -->
        <div class="form-panel">
            <div id="login-container">

                <div class="form-heading">
                    <span class="eyebrow">Criar conta</span>
                    <h2><?= $sucesso ? 'Conta criada' : 'Crie sua conta' ?></h2>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <p><?php echo sanitizeInput($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <p style="color: var(--emerald-dark); font-weight: 600; margin-bottom: 22px; position: relative; z-index: 1;">
                        Usuário cadastrado com sucesso!
                    </p>
                    <div class="form-footer" style="margin-top: 0;">
                        <a href="login">Voltar para o login</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="cadastro" class="login-form">
                        <div class="input-group">
                            <label for="username">Nome de usuário</label>
                            <input type="text" id="username" name="username" required placeholder="seu.usuario" value="<?= sanitizeInput($old_inputs['username'] ?? '') ?>" autocomplete="username">
                        </div>

                        <div class="input-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" required placeholder="nome@exemplo.com" value="<?= sanitizeInput($old_inputs['email'] ?? '') ?>" autocomplete="email">
                        </div>

                        <div class="input-group">
                            <label for="senha">Senha</label>
                            <input type="password" id="senha" name="senha" required placeholder="••••••••" autocomplete="new-password">
                        </div>

                        <div class="input-group">
                            <label for="senha_confirmada">Confirme a senha</label>
                            <input type="password" id="senha_confirmada" name="senha_confirmada" required placeholder="••••••••" autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn-submit">Criar conta</button>

                        <div class="form-footer">
                            <a href="login">Já tem uma conta? Entrar</a>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>

        <!-- Painel da marca: agora a direita -->
        <div class="brand-panel">

            <svg class="ticker-line" viewBox="0 0 600 800" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <polyline points="0,620 60,600 120,640 180,560 240,590 300,500 360,540 420,460 480,490 540,420 600,450"
                    fill="none" stroke="#16a05f" stroke-width="1.5" opacity="0.35" />
                <polyline points="0,700 60,690 120,710 180,680 240,695 300,660 360,675 420,640 480,655 540,615 600,630"
                    fill="none" stroke="#eef2f6" stroke-width="1" opacity="0.10" />
            </svg>

            <div class="brand-panel-top">
                <img src="assets/img/logo.png" alt="Finance Control" class="logo-mark">
            </div>

            <div class="brand-panel-mid">
                <h1>Gestão financeira com clareza e controle.</h1>
                <p>Centralize suas transações, e observe seus hábitos com o Finance Control.</p>
            </div>

            <div class="brand-panel-bottom">

            </div>

        </div>

    </div>

</body>
</html>