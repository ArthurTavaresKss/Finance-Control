<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $data_inicial = ($_POST['data_inicial'] ?? date('Y-m-01'));
    $data_final = ($_POST['data_final'] ?? date('Y-m-t'));
    $idUsuario = $_SESSION['idUsuario'];

    $transacoes = getTransactionsByUserIdAndDate($pdo, $idUsuario, $data_inicial, $data_final);
    
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
    <br><br>
    <button type="button" onclick="document.getElementById('modalTransacao').showModal()">
        Adicionar transação
    </button>
    <p>Mostrando transações de </p>
    <form method='POST' action="index.php">
        <input type="date" id="data_inicial" name="data_inicial" required value="<?= $data_inicial ?>">
        Até
        <input type="date" id="data_final" name="data_final" required value="<?= $data_final ?>">
        <button type="submit">Filtrar</button>
        <button type="button" onclick="window.location.href='index.php'">Resetar Filtros</button>
    </form>
    <br>
    <?php if (count($transacoes) === 0): ?>
            <p>Nenhuma transação cadastrada no período informado.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>

                <?php foreach ($transacoes as $transacao): ?>
                    <tr>
                        <td><?= sanitizeInput($transacao['tipo']) ?></td>
                        <td><?= sanitizeInput($transacao['descricao']) ?></td>
                        <td><?= sanitizeInput($transacao['categoria']) ?></td>
                        <td>R$ <?= sanitizeInput($transacao['valor']) ?></td>
                        <td><?= sanitizeInput($transacao['data_transacao']) ?></td>
                        <td style="text-align: center;">
                            <a href="editar_transacao.php?id=<?= $transacao['id'] ?>" title="Editar transação">
                                <img src="assets/img/editar.png" alt="Editar" width="23" height="23">
                            </a>
                            <a href="excluir_transacao.php?id=<?= $transacao['id'] ?>" 
                            onclick="return confirm('Tem certeza que deseja excluir esta transação?')" 
                            title="Excluir transação">
                                <img src="assets/img/excluir.png" alt="Excluir" width="23" height="23">
                            </a>
                        </td>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
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