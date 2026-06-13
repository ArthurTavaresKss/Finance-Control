<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/functions.php";

$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $senha = $_POST['senha'];
    $senha_confirmada = $_POST['senha_confirmada'];

    $userByEmail = getUserByEmail($pdo, $email);
    $userByUsername = getUserByUsername($pdo, $username);

    if ($userByEmail) {
        $error = "Email já cadastrado. Use outro Email.";
    } elseif ($userByUsername) {
        $error = "Nome de usuário já cadastrado. Crie outro nome.";
    } elseif ($senha != $senha_confirmada) {
        $error = "Senhas não coincidem. Tente novamente.";
    } else {
        $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);
        insertUser($pdo, $username, $email, $senhaCriptografada);
        $sucesso = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar-se</title>
</head>
<body>
    <h1>Cadastrar-se</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo sanitizeInput($error); ?></p>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <p style="color: green;">Usuário cadastrado com sucesso!</p>
        <br><br>
        <a href="login.php">Voltar</a>
    <?php else: ?>
    <form method="POST" action="cadastro.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required value=<?= sanitizeInput($username ?? '') ?>><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value=<?= sanitizeInput($email ?? '') ?>><br><br>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required><br><br>

        <label for="senha_confirmada">Confirme a senha:</label>
        <input type="password" id="senha_confirmada" name="senha_confirmada" required><br><br>

        <a href="login.php">Voltar</a> | <button type="submit">Criar conta</button>
    </form>
    <?php endif; ?>
</body>
</html>