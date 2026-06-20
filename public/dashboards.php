<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    $indicadores = getIndicadoresMensais($pdo, $idUsuario);
    $saldoMes = $indicadores['valor_entradas'] - $indicadores['valor_saidas'];

    $dadosGastosCat = getGastosPorCategoria($pdo, $idUsuario);
    $dadosEntradasCat = getEntradasPorCategoria($pdo, $idUsuario);

    usort($dadosGastosCat, function($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    usort($dadosEntradasCat, function($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    $historicoAnual = getHistoricoAnual($pdo, $idUsuario);

    $gCatLabels = []; $gCatValores = [];
    foreach ($dadosGastosCat as $item) { $gCatLabels[] = $item['categoria']; $gCatValores[] = (float)$item['total']; }

    $eCatLabels = []; $eCatValores = [];
    foreach ($dadosEntradasCat as $item) { $eCatLabels[] = $item['categoria']; $eCatValores[] = (float)$item['total']; }

    $mesesNomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    $anualEntradas = array_fill(0, 12, 0);
    $anualSaidas = array_fill(0, 12, 0);
    $anualSaldos = array_fill(0, 12, 0);

    foreach ($historicoAnual as $dados) {
        $idx = $dados['mes'] - 1;
        $anualEntradas[$idx] = (float)$dados['entradas'];
        $anualSaidas[$idx] = (float)$dados['saidas'];
        $anualSaldos[$idx] = (float)($dados['entradas'] - $dados['saidas']);
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboards - Finance Control</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f6f9; }
        .dashboard-row { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; width: 100%; }
        .card { flex: 1; min-width: 180px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 5px solid #34495e; }
        .card h4 { margin: 0 0 5px 0; color: #7f8c8d; font-size: 13px; text-transform: uppercase; }
        .card p { margin: 0; font-size: 20px; font-weight: bold; color: #2c3e50; }
        .card.positivo { border-left-color: #2ecc71; }
        .card.negativo { border-left-color: #e74c3c; }
        .chart-box { flex: 1; min-width: 45%; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .chart-box h3 { margin-top: 0; color: #34495e; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Finance Control</h1>
    <nav>
        <a href="transacoes.php">Transações</a> | 
        <a href="assinaturas.php">Assinaturas</a> | 
        <a href="investimentos.php">Investimentos</a> | 
        <a href="dashboards.php">Dashboards</a> | 
        <a href="perfil.php">Perfil</a> | 
        <a href="logout.php">Sair</a>
    </nav>
    <br>

    <h2>Dados Mensais</h2>
    <div class="dashboard-row">
        <div class="card">
            <h4>Qtd. Entradas</h4>
            <p><?= $indicadores['qtd_entradas'] ?></p>
        </div>
        <div class="card">
            <h4>Qtd. Saídas</h4>
            <p><?= $indicadores['qtd_saidas'] ?></p>
        </div>
        <div class="card">
            <h4>Transações Totais</h4>
            <p><?= $indicadores['qtd_totais'] ?></p>
        </div>
        <div class="card positivo">
            <h4>Valor Entradas</h4>
            <p>R$ <?= number_format($indicadores['valor_entradas'], 2, ',', '.') ?></p>
        </div>
        <div class="card negativo">
            <h4>Valor Saídas</h4>
            <p>R$ <?= number_format($indicadores['valor_saidas'], 2, ',', '.') ?></p>
        </div>
        <div class="card <?= $saldoMes >= 0 ? 'positivo' : 'negativo' ?>">
            <h4>Saldo Total</h4>
            <p>R$ <?= number_format($saldoMes, 2, ',', '.') ?></p>
        </div>
    </div>

    <h2>Dados por Categoria</h2>
    <div class="dashboard-row">
        <div class="chart-box">
            <h3>Gastos por Categoria (Colunas - Mês Atual)</h3>
            <canvas id="chartGastosCat"></canvas>
        </div>
        <div class="chart-box">
            <h3>Total de Entradas por Categoria (Colunas - Mês Atual)</h3>
            <canvas id="chartEntradasCat"></canvas>
        </div>
    </div>

    <h2>Dados por Saldos</h2>
    <div class="dashboard-row">
        <div class="chart-box">
            <h3>Evolução Anual (Entradas, Saídas e Saldo)</h3>
            <canvas id="chartEvolucaoAnual"></canvas>
        </div>
        <div class="chart-box">
            <h3>Saldo Total por Mês</h3>
            <canvas id="chartSaldoAnualBarra"></canvas>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('chartGastosCat'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($gCatLabels) ?>,
                datasets: [{
                    label: 'Gastos (R$)',
                    data: <?= json_encode($gCatValores) ?>,
                    backgroundColor: '#e74c3c'
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartEntradasCat'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($eCatLabels) ?>,
                datasets: [{
                    label: 'Entradas (R$)',
                    data: <?= json_encode($eCatValores) ?>,
                    backgroundColor: '#2ecc71'
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartEvolucaoAnual'), {
            type: 'line',
            data: {
                labels: <?= json_encode($mesesNomes) ?>,
                datasets: [
                    { label: 'Entradas', data: <?= json_encode($anualEntradas) ?>, borderColor: '#2ecc71', tension: 0.2, fill: false },
                    { label: 'Saídas', data: <?= json_encode($anualSaidas) ?>, borderColor: '#e74c3c', tension: 0.2, fill: false },
                    { label: 'Saldo', data: <?= json_encode($anualSaldos) ?>, borderColor: '#3498db', tension: 0.2, fill: false }
                ]
            },
            options: { responsive: true }
        });

        new Chart(document.getElementById('chartSaldoAnualBarra'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($mesesNomes) ?>,
                datasets: [{
                    label: 'Saldo Mensal (R$)',
                    data: <?= json_encode($anualSaldos) ?>,
                    backgroundColor: <?= json_encode(array_map(fn($v) => $v >= 0 ? '#2ecc71' : '#e74c3c', $anualSaldos)) ?>
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>