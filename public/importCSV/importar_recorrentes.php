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
    <title>Finance Control - Importar Recorrentes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body id="app-page">

    <div class="modal-page-wrap wide">
        <div class="dialog-inner">
            <h2>Importar Transações Recorrentes via CSV</h2>

            <div class="notice-box">
                <strong class="notice-title">⚠️ Atenção aos requisitos das transações recorrentes</strong>
                <ul>
                    <li>O arquivo deve estar no formato <strong>.CSV</strong> separado por ponto e vírgula (<strong>;</strong>).</li>
                    <li>A primeira linha deve conter exatamente os seguintes cabeçalhos:</li>
                </ul>
                <code>Tipo;Descrição;Valor;Categoria;Dia da Transação;Data de Início;Data de Término</code>
                <ul>
                    <li><strong>Regras estritas por coluna:</strong></li>
                    <li><em>Tipo:</em> deve ser exatamente "Entrada" ou "Saída".</li>
                    <li><em>Valor:</em> decimal no padrão brasileiro (ex: 89,90 ou 1500,00).</li>
                    <li><em>Dia da Transação:</em> número inteiro entre <strong>1 e 28</strong>.</li>
                    <li><em>Data de Início / Término:</em> formato <strong>MM/AAAA</strong> (ex: 06/2026). O término pode ficar vazio.</li>
                </ul>
                <span class="notice-footnote">
                    <span style="text-decoration: underline;">Nota:</span> se houver qualquer inconsistência ou erro em qualquer linha, toda a operação será abortada pelo banco de dados por segurança.
                </span>
            </div>

            <form method="POST" action="processar_importacao_recorrentes.php" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="arquivo_csv">Selecione o arquivo CSV</label>
                    <div class="file-drop">
                        <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
                    </div>
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="window.location.href='../recorrentes.php'">Voltar</button>
                    <button type="submit" class="btn-primary">Importar recorrentes</button>
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