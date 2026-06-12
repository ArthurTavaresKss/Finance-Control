<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/helpers/functions.php";
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
    <form method="POST" action="cadastro.php">
        <label for="nome">Usuário:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="nome">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="categoria">Senha:</label>
        <input type="password" id="senha" name="senha" required><br><br>

        <a href="login.php">Voltar</a> | <button type="submit">Criar conta</button>
    </form>
</body>
</html>