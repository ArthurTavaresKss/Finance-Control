<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];

    $mesAtual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
    $anoAtual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

    if ($mesAtual < 1 || $mesAtual > 12) {
        $mesAtual = (int)date('n');
    }
    if ($anoAtual < 2000 || $anoAtual > 2100) {
        $anoAtual = (int)date('Y');
    }

    $indicadores = getIndicadoresMensais($pdo, $idUsuario, $anoAtual, $mesAtual);
    $saldoMes = $indicadores['valor_entradas'] - $indicadores['valor_saidas'];

    $dadosGastosCat = getGastosPorCategoria($pdo, $idUsuario, $anoAtual, $mesAtual);
    $dadosEntradasCat = getEntradasPorCategoria($pdo, $idUsuario, $anoAtual, $mesAtual);

    usort($dadosGastosCat, function($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    usort($dadosEntradasCat, function($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    $historicoAnual = getHistoricoAnual($pdo, $idUsuario, $anoAtual);

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

    $listaNomesMeses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];

    $mesesValidosNoBanco = getMesesDisponiveisFiltro($pdo, $idUsuario);
    $anosDisponiveis = getAnosDisponiveisFiltro($pdo, $idUsuario);

    if (!in_array($mesAtual, $mesesValidosNoBanco)) {
        $mesesValidosNoBanco[] = $mesAtual;
        sort($mesesValidosNoBanco);
    }
    if (!in_array($anoAtual, $anosDisponiveis)) {
        $anosDisponiveis[] = $anoAtual;
        rsort($anosDisponiveis);
    }

    $mesesNomesCompletos = $listaNomesMeses;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control - Dashboards</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Finance Control</h1>
    <nav>
        <a href="transacoes.php">Transações</a> | 
        <a href="recorrentes.php">Transações Recorrentes</a> | 
        <a href="dashboards.php">Dashboards</a> | 
        <a href="perfil.php">Perfil</a> | 
        <a href="logout.php">Sair</a>
    </nav>
    <br>
    <form method="GET" action="dashboards.php" id="filtro-periodo">
        <label for="mes">Mês:</label>
        <select name="mes" id="mes">
            <?php foreach ($mesesValidosNoBanco as $numMes): ?>
                <option value="<?= $numMes ?>" <?= $numMes == $mesAtual ? 'selected' : '' ?>>
                    <?= $listaNomesMeses[$numMes] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="ano">Ano:</label>
        <select name="ano" id="ano">
            <?php foreach ($anosDisponiveis as $anoOpcao): ?>
                <option value="<?= $anoOpcao ?>" <?= $anoOpcao == $anoAtual ? 'selected' : '' ?>>
                    <?= $anoOpcao ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
        <a href="dashboards.php" style="margin-left: 10px;"><button type="button">Resetar Filtros</button></a>
    </form>
    <h2>Dados Mensais — <?= sanitizeInput($mesesNomesCompletos[$mesAtual]) ?> de <?= $anoAtual ?></h2>
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
    <h2>Dados por Categoria — <?= sanitizeInput($mesesNomesCompletos[$mesAtual]) ?> de <?= $anoAtual ?></h2>
    <div class="dashboard-row">
        <div class="chart-box">
            <h3>Gastos por Categoria (Colunas)</h3>
            <canvas id="chartGastosCat"></canvas>
        </div>
        <div class="chart-box">
            <h3>Total de Entradas por Categoria (Colunas)</h3>
            <canvas id="chartEntradasCat"></canvas>
        </div>
    </div>
    <h2>Dados por Saldos — Ano de <?= $anoAtual ?></h2>
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