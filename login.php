<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/helpers/functions.php";
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
    <form method="POST" action="login.php">
        <label for="nome">Usuário:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="categoria">Senha:</label>
        <input type="password" id="senha" name="senha" required><br><br>

        <button type="submit">Entrar</button> | <a href="cadastro.php">Não tem uma conta? Cadastre-se</a>
    </form>
</body>
</html>