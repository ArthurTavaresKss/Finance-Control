<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idTransacao = ($_GET['id']);
        $idUsuario = $_SESSION['idUsuario'];

        $sucesso = deleteTransactionByUserIdAndId($pdo, $idUsuario, $idTransacao);
        redirect("index.php");
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
        <p style="color:green;">Transação excluída com sucesso!</p>
    <?php else: ?>
        <p style="color:red;">Erro na exclusão da transação. Tente novamente.</p>
    <?php endif; ?>

<a href="index.php">Voltar</a>
</body>
</html>