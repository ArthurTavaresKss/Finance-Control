<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $status = $_SESSION['status_importacao'] ?? '';
    unset($_SESSION['status_importacao']);

    $mostrarModal = !empty($status);
    $modalTitulo = '';
    $modalMensagem = '';

    if ($status === 'sucesso') {
        $modalTitulo = 'Sucesso!';
        $modalMensagem = 'Todas as transações recorrentes foram importadas.';
    } elseif ($status === 'erro_arquivo') {
        $modalTitulo = 'Erro no Arquivo';
        $modalMensagem = 'Selecione um arquivo CSV válido para o upload.';
    } elseif ($status === 'erro_processamento') {
        $modalTitulo = 'Importação Cancelada';
        $modalMensagem = 'Nenhum dado foi salvo. O arquivo contém erros de formatação, valores inválidos, dias fora do escopo (1 a 28) ou meses inválidos.';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Transações Recorrentes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Importar Transações Recorrentes via CSV</h1>
    
    <p style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb; line-height: 1.6; max-width: 650px;">
        <strong>⚠️ ATENÇÃO AOS REQUISITOS DAS TRANSAÇÕES RECORRENTES:</strong><br>
        • O arquivo deve estar no formato <strong>.CSV</strong> separado por ponto e vírgula (<strong>;</strong>).<br>
        • A primeira linha deve conter exatamente os seguintes cabeçalhos:<br>
        <code style="background: #fff; padding: 2px 5px; border: 1px solid #ccc; display: block; margin: 5px 0;">Tipo;Descrição;Valor;Categoria;Dia da Transação;Data de Início;Data de Término</code>
        • <strong>Regras estritas por coluna:</strong><br>
        - <em>Tipo:</em> Deve ser exatamente "Entrada" ou "Saída".<br>
        - <em>Valor:</em> Decimal no padrão brasileiro (Ex: 89,90 ou 1500,00).<br>
        - <em>Dia da Transação:</em> Um número inteiro entre <strong>1 e 28</strong>.<br>
        - <em>Data de Início / Término:</em> Formato <strong>MM/AAAA</strong> (Ex: 06/2026). O término pode ficar vazio.<br><br>
        <span style="text-decoration: underline;">Nota:</span> Se houver qualquer inconsistência ou erro em qualquer linha, <strong>toda a operação será abortada</strong> pelo banco de dados por segurança.
    </p>

    <form method="POST" action="processar_importacao_recorrentes.php" enctype="multipart/form-data">
        <p>
            <label for="arquivo_csv">Selecione o arquivo CSV:</label><br><br>
            <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
        </p>
        
        <p>
            <button type="button" onclick="window.location.href='../recorrentes.php'">Voltar</button>
            <button type="submit">Importar Recorrentes</button>
        </p>
    </form>

    <dialog id="modalStatus" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc; position: fixed; inset: 0; margin: auto; max-width: 400px; height: fit-content;">
        <h2><?= sanitizeInput($modalTitulo) ?></h2>
        <p><?= sanitizeInput($modalMensagem) ?></p>
        <button type="button" onclick="document.getElementById('modalStatus').close()">Fechar</button>
    </dialog>

    <?php if ($mostrarModal): ?>
        <script>
            document.getElementById('modalStatus').showModal();
        </script>
    <?php endif; ?>
</body>
</html>