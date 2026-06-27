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
        $modalMensagem = 'Transações importadas com sucesso.';
    } elseif ($status === 'erro_arquivo') {
        $modalTitulo = 'Erro';
        $modalMensagem = 'Por favor, selecione um arquivo CSV válido.';
    } elseif ($status === 'erro_processamento') {
        $modalTitulo = 'Erro';
        $modalMensagem = 'Houve um erro ao processar o arquivo.';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Transações</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Importar Transações via CSV</h1>
    
    <form method="POST" action="processar_importacao_transacoes.php" enctype="multipart/form-data">
        <p style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb; line-height: 1.6; max-width: 600px;">
            <strong>⚠️ ATENÇÃO AOS REQUISITOS DO ARQUIVO:</strong><br>
            • O arquivo deve estar obrigatoriamente no formato <strong>.CSV</strong> separado por ponto e vírgula (<strong>;</strong>).<br>
            • A primeira linha do arquivo deve ser rigorosamente o cabeçalho:<br>
            <code style="background: #fff; padding: 2px 5px; border: 1px solid #ccc; display: block; margin: 5px 0;">Tipo;Descrição;Valor;Categoria;Data da Transação</code>
            • <strong>Campos aceitos:</strong><br>
            - <em>Tipo:</em> Deve ser exatamente "Entrada" ou "Saída".<br>
            - <em>Valor:</em> Formato brasileiro decimal (Ex: 1250,50 ou 45,00).<br>
            - <em>Data da Transação:</em> Formato brasileiro estrito (Ex: DD/MM/AAAA).<br><br>
            <span style="text-decoration: underline;">Nota:</span> Se <strong>qualquer uma das linhas</strong> contiver uma data inválida, valor incorreto ou campo em branco, <strong>toda a importação será cancelada</strong> e nenhum dado será salvo no sistema.
        </p>
        <p>
            <label for="arquivo_csv">Selecione o arquivo CSV:</label><br><br>
            <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
        </p>
        
        <p>
            <button type="button" onclick="window.location.href='../transacoes.php'">Voltar</button>
            <button type="submit">Importar Dados</button>
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