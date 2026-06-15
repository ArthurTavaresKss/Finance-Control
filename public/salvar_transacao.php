<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = ($_POST['tipo']);
        $descricao = trim($_POST['descricao']);
        $valor = ($_POST['valor']);
        $categoria = ($_POST['categoria']);
        if ($categoria === 'nova_categoria') {
            $categoria = trim($_POST['nova_categoria']);
        }
        $data_transacao = ($_POST['data_transacao']);
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = insertTransacao($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria, $data_transacao);
    } else {
        redirect("index.php");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Transação</title>
</head>
<body>
    <?php if ($sucesso): ?>
        <p style="color:green;">Transação cadastrada com sucesso!</p>
    <?php else: ?>
        <p style="color:red;">Erro no cadastro da transação. Tente novamente.</p>
    <?php endif; ?>

<a href="index.php">Voltar</a>
</body>
</html>