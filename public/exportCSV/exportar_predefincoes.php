<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    $transacoes = getPredefinitionsByUserId($pdo, $idUsuario);

    $nomeArquivo = 'transacoes_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    $cabecalho = ['Tipo', 'Descrição', 'Valor', 'Categoria', 'Data da Transação'];
    fputcsv($output, $cabecalho, ';');

    foreach ($transacoes as $t) {
        $dataFormatada = !empty($t['data_transacao']) ? date('d/m/Y', strtotime($t['data_transacao'])) : '';

        $valorFormatado = number_format($t['valor'], 2, ',', '');

        $linha = [
            $t['tipo'],
            $t['descricao'],
            $valorFormatado,
            $t['categoria'],
        ];

        fputcsv($output, $linha, ';');
    }

    fclose($output);
    exit;
?>