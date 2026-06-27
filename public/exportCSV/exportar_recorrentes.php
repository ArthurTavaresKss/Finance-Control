<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    $transacoes = getRecurringByUserId($pdo, $idUsuario); 

    $nomeArquivo = 'transacoes_recorrentes_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    $cabecalho = ['Tipo', 'Descrição', 'Valor', 'Categoria', 'Dia da Transação', 'Data de Início', 'Data de Término'];
    fputcsv($output, $cabecalho, ';');

    foreach ($transacoes as $t) {
        $inicioFormatado = !empty($t['data_transacao_inicio']) ? date('m/Y', strtotime($t['data_transacao_inicio'])) : '';
        $terminoFormatado = !empty($t['data_transacao_termino']) ? date('m/Y', strtotime($t['data_transacao_termino'])) : '';

        $valorFormatado = number_format($t['valor'], 2, ',', '');

        $linha = [
            $t['tipo'],
            $t['descricao'],
            $valorFormatado,
            $t['categoria'],
            $t['dia_transacao'],
            $inicioFormatado,
            $terminoFormatado
        ];

        fputcsv($output, $linha, ';');
    }

    fclose($output);
    exit;
?>