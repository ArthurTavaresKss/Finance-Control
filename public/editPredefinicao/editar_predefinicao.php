<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';
    
    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idPredefinicao = $_GET['id'];
        $idUsuario = $_SESSION['idUsuario'];

        $predefinicao = getPredefinitionByUserIdAndId($pdo, $idUsuario, $idPredefinicao);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id          = $_POST['id'];
        $tipo        = $_POST['tipo'];
        $descricao   = trim($_POST['descricao']);
        $valor       = $_POST['valor'];
        $categoria   = $_POST['categoria'];
        $idUsuario   = $_SESSION['idUsuario'];
        if ($categoria === 'nova_categoria') {
            $categoria = trim($_POST['nova_categoria']);
        }

        $sucesso = alterPredefinitionById(
            $pdo,
            $id,
            $idUsuario,
            $tipo,
            $descricao,
            $valor,
            $categoria
        );
        if ($sucesso) {
            $_SESSION['status_predefinicao'] = 'predefinicao_alterada';
        } else {
            $_SESSION['status_predefinicao'] = 'erro_predefinicao_alterada';
        }
        redirect("../predefinicoes?");
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control - Editar Predefinição</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body id="app-page">

    <div class="modal-page-wrap">
        <div class="dialog-inner">
            <h2>Editar Transação</h2>

            <form method="POST" action="editar_predefinicao">
                <div class="input-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Entrada" <?= ($predefinicao['tipo'] == 'Entrada') ? 'selected' : '' ?>>Entrada</option>
                        <option value="Saída" <?= ($predefinicao['tipo'] == 'Saída') ? 'selected' : '' ?>>Saída</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="descricao">Descrição</label>
                    <input type="text" id="descricao" name="descricao" maxlength="90" required
                           placeholder="Ex: Compra de chocolate" value="<?= sanitizeInput($predefinicao['descricao']) ?>">
                </div>

                <div class="input-group">
                    <label for="valor">Valor (R$)</label>
                    <input type="number" step="0.01" id="valor" name="valor" required
                           placeholder="0.00" value="<?= sanitizeInput($predefinicao['valor']) ?>">
                </div>

                <div class="input-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" required onchange="mostrarCampoNovaCategoria()">
                        <option value="" disabled>Selecione uma categoria</option>
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
                            $selected = ($cat === $predefinicao['categoria']) ? 'selected' : '';
                            echo '<option value="' . sanitizeInput($cat) . '" ' . $selected . '>'
                                . sanitizeInput($cat) . '</option>';
                        }
                        ?>
                        <option value="nova_categoria">+ Adicionar nova categoria...</option>
                    </select>
                    <input type="text" id="nova_categoria" name="nova_categoria"
                           placeholder="Digite a nova categoria" style="display: none; margin-top: 8px;">
                </div>

                <input type="hidden" name="id" value="<?= $predefinicao['id'] ?>">

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="window.location.href='../predefinicoes'">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>