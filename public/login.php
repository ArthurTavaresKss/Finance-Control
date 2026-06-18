<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $senha = $_POST['senha'];

    $user = getUserByEmail($pdo, $email);

    if ($user && password_verify($senha, $user['senha']) && $user['ativo'] === 1) {
        session_regenerate_id(true);
        $_SESSION['idUsuario'] = $user['id'];
        $_SESSION['usernameUsuario'] = $user['username'];
        redirect("index.php");
        exit;
    } else {
        $error = "Email ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body id="login-page">

    <div id="login-wrapper">

        <!-- Painel esquerdo: marca -->
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

        <!-- Painel direito: formulário -->
        <div class="form-panel">
            <div id="login-container">

                <div class="form-heading">
                    <span class="eyebrow">Acesso ao painel</span>
                    <h2>Entrar na sua conta</h2>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <p><?php echo sanitizeInput($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="login-form">
                    <div class="input-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required placeholder="nome@exemplo.com" value="<?= sanitizeInput($email ?? '') ?>" autocomplete="username">
                    </div>

                    <div class="input-group">
                        <label for="senha">Senha de acesso</label>
                        <input type="password" id="senha" name="senha" required placeholder="••••••••" autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn-submit">Acessar painel</button>

                    <div class="form-footer">
                        <a href="cadastro.php">Novo por aqui? Crie sua conta</a>
                    </div>
                </form>

            </div>
        </div>

    </div>

</body>
</html>