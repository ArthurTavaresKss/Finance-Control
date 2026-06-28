<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect("transacoes");
}

$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
unset($_SESSION['login_error']);

if (!$error && ($_GET['status'] ?? '') === 'sessao_expirada') {
    $error = "Sua sessão expirou por inatividade. Faça login novamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "E-mail inválido.";
        redirect("login");
        exit;
    }

    try {
        $user = getUserByEmail($pdo, $email);

        if ($user && password_verify($senha, $user['senha']) && $user['ativo'] === 1) {
            session_regenerate_id(true);
            $_SESSION['idUsuario'] = $user['id'];
            $_SESSION['usernameUsuario'] = $user['username'];
            $_SESSION['ultimo_acesso'] = time();
            redirect("transacoes");
            exit;
        } else {
            $_SESSION['login_error'] = "Email ou senha inválidos.";
            redirect("login");
            exit;
        }

    } catch (PDOException $e) {
        handleDBException($e);
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        redirect('login');
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

        <div class="form-panel">
            <div id="login-container">

                <div class="form-heading">
                    <span class="eyebrow">Acesso ao painel</span>
                    <h2>Entrar na sua conta</h2>
                </div>

                <?php if (isset($error) && $error): ?>
                    <div class="error-message">
                        <p><?php echo sanitizeInput($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login" class="login-form">
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
                        <a href="cadastro">Novo por aqui? Crie sua conta</a>
                    </div>
                </form>

            </div>
        </div>

    </div>

</body>
</html>