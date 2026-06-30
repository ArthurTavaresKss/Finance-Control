<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
http_response_code(500); 
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) {
    $redirect = "transacoes";
} else {
    $redirect = "";
}
$error = $_SESSION['erro_db'] ?? "Erro de conexão desconhecido.";
unset($_SESSION['erro_db']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro no Banco de Dados - Finance Control</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body id="error-404-page">

    <div class="error-404-wrap">
        <div class="dialog-inner">

            <div class="error-404-hero">
                <div class="error-404-code" aria-hidden="true">500</div>
            </div>

            <h2>Conexão interrompida</h2>
            <p>Ops! Não conseguimos nos conectar ao banco de dados no momento. Nossa equipe já está verificando. Por favor, tente novamente mais tarde.</p>
            <p><strong>Log técnico:</strong> <?= htmlspecialchars($error) ?></p>

            <div class="error-404-actions">
                <button type="button" class="btn-primary" onclick="window.location.href='/<?php echo sanitizeInput($redirect); ?>'">Tentar novamente</button>
            </div>

        </div>
    </div>

</body>
</html>