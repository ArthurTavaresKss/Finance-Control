<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) {
    $redirect = "transacoes";
} else {
    $redirect = "";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada - Finance Control</title>
    <link rel="stylesheet" href="/Finance-Control/assets/css/style.css">
</head>
<body id="error-404-page">

    <div class="error-404-wrap">
        <div class="dialog-inner">

            <div class="error-404-hero">
                <div class="error-404-code" aria-hidden="true">404</div>
            </div>

            <h2>Página não encontrada</h2>
            <p>Ops! O endereço que você acessou não existe ou foi movido. Verifique o link ou volte para o início.</p>

            <div class="error-404-actions">
                <button type="button" class="btn-primary" onclick="window.location.href='/Finance-Control/<?php echo htmlspecialchars($redirect); ?>'">Voltar</button>
            </div>

        </div>
    </div>

</body>
</html>