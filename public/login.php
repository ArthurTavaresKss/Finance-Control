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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo sanitizeInput($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value=<?= sanitizeInput($email ?? '') ?>><br><br>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required value=<?= sanitizeInput($senha ?? '') ?>><br><br>

        <button type="submit">Entrar</button> | <a href="cadastro.php">Não tem uma conta? Cadastre-se</a>
    </form>
</body>
</html>