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
    <title>Finance Control - Importar Transações</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body id="app-page">

    <div class="modal-page-wrap wide">
        <div class="dialog-inner">
            <h2>Importar Transações via CSV</h2>

            <form method="POST" action="processar_importacao_transacoes.php" enctype="multipart/form-data">
                <div class="notice-box">
                    <strong class="notice-title">⚠️ Atenção aos requisitos do arquivo</strong>
                    <ul>
                        <li>O arquivo deve estar obrigatoriamente no formato <strong>.CSV</strong> separado por ponto e vírgula (<strong>;</strong>).</li>
                        <li>A primeira linha do arquivo deve ser rigorosamente o cabeçalho:</li>
                    </ul>
                    <code>Tipo;Descrição;Valor;Categoria;Data da Transação</code>
                    <ul>
                        <li><strong>Campos aceitos:</strong></li>
                        <li><em>Tipo:</em> deve ser exatamente "Entrada" ou "Saída".</li>
                        <li><em>Valor:</em> formato brasileiro decimal (ex: 1250,50 ou 45,00).</li>
                        <li><em>Data da Transação:</em> formato brasileiro estrito (ex: DD/MM/AAAA).</li>
                    </ul>
                    <span class="notice-footnote">
                        <span style="text-decoration: underline;">Nota:</span> se qualquer uma das linhas contiver uma data inválida, valor incorreto ou campo em branco, toda a importação será cancelada e nenhum dado será salvo no sistema.
                    </span>
                </div>

                <div class="input-group">
                    <label for="arquivo_csv">Selecione o arquivo CSV</label>
                    <div class="file-drop">
                        <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
                    </div>
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="window.location.href='../transacoes.php'">Voltar</button>
                    <button type="submit" class="btn-primary">Importar dados</button>
                </div>
            </form>
        </div>
    </div>

    <dialog id="modalStatus" class="app-dialog">
        <div class="dialog-inner status-dialog-body">
            <h2><?= sanitizeInput($modalTitulo) ?></h2>
            <p><?= sanitizeInput($modalMensagem) ?></p>
            <div class="dialog-actions" style="justify-content: center;">
                <button type="button" class="btn-primary" onclick="document.getElementById('modalStatus').close()">Fechar</button>
            </div>
        </div>
    </dialog>

    <?php if ($mostrarModal): ?>
        <script>
            document.getElementById('modalStatus').showModal();
        </script>
    <?php endif; ?>

</body>
</html>