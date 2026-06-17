<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];
    $data_inicial    = $_POST['data_inicial']    ?? date('Y-m-01');
    $data_final      = $_POST['data_final']      ?? date('Y-m-t');
    $tipo            = $_POST['tipo']            ?? '';
    $descricao       = $_POST['descricao']       ?? '';
    $categoria       = $_POST['categoria']       ?? '';
    $operador_valor  = $_POST['operador_valor']  ?? '';
    $valor           = $_POST['valor']           ?? '';
    $data_transacao  = $_POST['data_transacao']  ?? '';
    $limite = 10;
    $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

    $transacoes = getTransactionsByUserIdAndParams(
        $pdo,
        $idUsuario,
        $data_inicial,
        $data_final,
        $tipo,
        $descricao,
        $categoria,
        $operador_valor,
        $valor,
        $data_transacao
    );

    $totalPaginas = (int) ceil(count($transacoes) / $limite);
    if (!isset($paginaAtual)) {
        if ($paginaAtual < 1 || $paginaAtual > $totalPaginas) {
            redirect("?pagina=1");
        }
    }
    $offset = ($paginaAtual - 1) * $limite;

    $transacoesPaginadas = getTransactionsByUserIdAndParamsAndPagination(
        $pdo,
        $idUsuario,
        $data_inicial,
        $data_final,
        $tipo,
        $descricao,
        $categoria,
        $operador_valor,
        $valor,
        $data_transacao,
        $limite,
        $offset
    );
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body>
    <h1>Finance Control</h1>
    <nav>
        <a href="index.php">Transações</a> | 
        <a href="dashboards.php">Dashboards</a> | 
        <a href="logout.php">Perfil</a> | 
        <a href="logout.php">Sair</a>
    </nav>
    <br>
    <button type="button" onclick="document.getElementById('modalTransacao').showModal()">
        + Adicionar transação
    </button>
    <form method="POST" action="index.php">
        <p>
            Mostrando transações de
            <input type="date" name="data_inicial" value="<?= $data_inicial ?>" required>
            até
            <input type="date" name="data_final" value="<?= $data_final ?>" required>
             : 
        </p>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th colspan="7" style="padding: 8px; background-color: #f8f9fa;">
                        <?php
                        $tamanhoSetor = 5;
                        $qntSetores = ceil($totalPaginas / $tamanhoSetor);
                        $setorAtual = ceil($paginaAtual / $tamanhoSetor);

                        $paginaInicio = (($setorAtual - 1) * $tamanhoSetor) + 1;
                        $paginaFim = min($setorAtual * $tamanhoSetor, $totalPaginas);

                        $voltarAba = (($paginaInicio - 5) > 0) ? $paginaInicio - 5 : 1;
                        echo '<a href="?pagina=' . $voltarAba . '" style="margin-right: 15px; font-size: 0.75rem;"><< Voltar Aba</a>';

                        // --- BOTÃO VOLTAR PÁGINA ---
                        $voltarPagina = ($paginaAtual > 1) ? $paginaAtual - 1 : 1;
                        echo '<a href="?pagina=' . $voltarPagina . '" style="margin-right: 10px;">← Voltar</a>';

                        // --- NÚMEROS DAS PÁGINAS ---
                        for ($i = $paginaInicio; $i <= $paginaFim; $i++) {
                            $ativo = ($i == $paginaAtual) ? 'font-size: 1.2rem; text-decoration: underline; font-weight: bold;' : '';
                            echo '<a href="?pagina=' . $i . '" style="margin: 0 8px; ' . $ativo . '">' . $i . '</a>';
                        }

                        // --- BOTÃO PRÓXIMA PÁGINA ---
                        $proximaPagina = ($paginaAtual < $totalPaginas) ? $paginaAtual + 1 : $totalPaginas;
                        echo '<a href="?pagina=' . $proximaPagina . '" style="margin-left: 10px;">Próxima →</a>';

                        // --- BOTÃO PRÓXIMA ABA ---
                        // Calcula a primeira página do próximo setor. Se já estiver no último setor, trava na última página total.
                        $proximaAba = (($paginaFim + 1) <= $totalPaginas) ? $paginaFim + 1 : $totalPaginas;
                        echo '<a href="?pagina=' . $proximaAba . '" style="margin-left: 15px; font-size: 0.75rem;">Próxima Aba >></a>';
                        ?>
                    </th>
                </tr>
                <tr>
                    <th>Ordem</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
                <tr>
                    <th>

                    </th>
                    <th>
                        <select name="tipo" style="text-align: center;">
                            <option value="">~</option>
                            <option value="Entrada" <?= $tipo == 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                            <option value="Saída"   <?= $tipo == 'Saída'   ? 'selected' : '' ?>>Saída</option>
                        </select>
                    </th>
                    <th>
                        <input type="text" name="descricao" placeholder="~" maxlength="90" 
                               value="<?= htmlspecialchars($descricao) ?>" style="text-align: center;">
                    </th>
                    <th>
                        <select name="categoria" style="text-align: center;">
                            <option value="">~</option>
                            <?php
                            $todasTransacoes = getTransactionsByUserId($pdo, $idUsuario);
                            $categoriasUnicas = [];
                            foreach ($todasTransacoes as $t) {
                                $cat = sanitizeInput($t['categoria']);
                                if (!empty($cat) && !in_array($cat, $categoriasUnicas)) {
                                    $categoriasUnicas[] = $cat;
                                }
                            }
                            foreach ($categoriasUnicas as $cat) {
                                $selected = ($cat === $categoria) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($cat) . '" ' . $selected . '>' 
                                     . htmlspecialchars($cat) . '</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <th>
                        <select name="operador_valor" style="text-align: center;">
                            <option value="">~</option>
                            <option value="igual_a"   <?= $operador_valor == 'igual_a'   ? 'selected' : '' ?>>Igual a</option>
                            <option value="maior_que" <?= $operador_valor == 'maior_que' ? 'selected' : '' ?>>Maior que</option>
                            <option value="menor_que" <?= $operador_valor == 'menor_que' ? 'selected' : '' ?>>Menor que</option>
                        </select>
                        <input type="number" step="0.01" name="valor" placeholder="0.00" 
                               value="<?= htmlspecialchars($valor) ?>" style="text-align: center;">
                    </th>
                    <th>
                        <input type="date" name="data_transacao" value="<?= $data_transacao ?>" style="text-align: center;">
                    </th>

                    <th>
                        <button type="submit">Filtrar</button>
                        <button type="button" onclick="window.location.href='index.php'">Resetar</button>
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($transacoesPaginadas) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhuma transação encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php $order = 0; foreach ($transacoesPaginadas as $transacao): ?>
                        <tr>
                            <td><?php $order += 1; echo($order);?></td>
                            <td><?= sanitizeInput($transacao['tipo']) ?></td>
                            <td><?= sanitizeInput($transacao['descricao']) ?></td>
                            <td><?= sanitizeInput($transacao['categoria']) ?></td>
                            <td>R$ <?= sanitizeInput($transacao['valor']) ?></td>
                            <td><?= date('d/m/Y', strtotime(sanitizeInput($transacao['data_transacao']))) ?></td>
                            <td style="text-align: center;">
                                <a href="editar_transacao.php?id=<?= $transacao['id'] ?>">
                                    <img src="assets/img/editar.png" alt="Editar" width="23" height="23">
                                </a>
                                <a href="excluir_transacao.php?id=<?= $transacao['id'] ?>"
                                   onclick="return confirm('Tem certeza que deseja excluir esta transação?')">
                                    <img src="assets/img/excluir.png" alt="Excluir" width="23" height="23">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
        <dialog id="modalTransacao">
            <h2>Nova Transação</h2>
            <form method="POST" action="salvar_transacao.php">
                <p>
                    <label for="tipo">Tipo:</label><br>
                    <select id="tipo" name="tipo" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="Entrada">Entrada</option>
                        <option value="Saída">Saída</option>
                    </select>
                </p>
                <p>
                    <label for="descricao">Descrição:</label><br>
                    <input type="text" id="descricao" name="descricao" maxlength="90" required placeholder="Ex: Compra de chocolate">
                </p>
                <p>
                    <label for="valor">Valor (R$):</label><br>
                    <input type="number" step="0.01" id="valor" name="valor" required placeholder="0.00">
                </p>
                <p>
                    <label for="categoria">Categoria:</label><br>
                    <select id="categoria" name="categoria" required onchange="mostrarCampoNovaCategoria()">
                        <option value="" disabled selected>Selecione uma categoria</option>      

                        <?php
                        $transacoes = getTransactionsByUserId($pdo, $idUsuario);
                        $categoriasRepetidas = [];

                        foreach ($transacoes as $transacao) {
                            $categoria = sanitizeInput($transacao['categoria']);
                            if (!empty($categoria) && !in_array($categoria, $categoriasRepetidas)) {
                                echo '<option value="' . htmlspecialchars($categoria) . '">'
                                    . htmlspecialchars($categoria) . '</option>';
                                $categoriasRepetidas[] = $categoria;
                            }
                        }
                        ?>

                        <option value="nova_categoria">+ Adicionar nova categoria..</option>
                    </select>

                    <!-- Campo para digitar nova categoria (fica escondido no início) -->
                    <input type="text" id="nova_categoria" name="nova_categoria" 
                        placeholder="Digite a nova categoria" style="display: none; margin-top: 5px;">
                </p>
                <p>
                    <label for="data_transacao">Data da Transação:</label><br>
                    <input type="date" id="data_transacao" name="data_transacao" required value="<?= date('Y-m-d') ?>">
                </p>
                <p>
                    <button type="button" onclick="document.getElementById('modalTransacao').close()">Cancelar</button>
                    <button type="submit">Salvar</button>
                </p>
            </form>
        </dialog>
</body>
</html>