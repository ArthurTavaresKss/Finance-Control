<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];
    $usernameUsuario = $_SESSION['usernameUsuario'];
    
    $tipo            = $_GET['tipo']            ?? '';
    $descricao       = $_GET['descricao']       ?? '';
    $categoria       = $_GET['categoria']       ?? '';
    $operador_valor  = $_GET['operador_valor']  ?? '';
    $valor           = $_GET['valor']           ?? '';
    $limite          = $_GET['tamanho_paginas'] ?? 10;
    
    $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

    $predefinicoes = getPredefinitionsByUserIdAndParams(
        $pdo,
        $idUsuario,
        $tipo,
        $descricao,
        $categoria,
        $operador_valor,
        $valor
    );

    $totalPaginas = (int) ceil(count($predefinicoes) / $limite);
    
    if ($paginaAtual < 1 || ($totalPaginas > 0 && $paginaAtual > $totalPaginas)) {
        $query_params = $_GET;
        $query_params['pagina'] = 1;
        header("Location: ?" . http_build_query($query_params));
        exit;
    }
    
    $offset = ($paginaAtual - 1) * $limite;

    $predefinicoesPaginadas = getPredefinitionsByUserIdAndParamsAndPagination(
        $pdo,
        $idUsuario,
        $tipo,
        $descricao,
        $categoria,
        $operador_valor,
        $valor,
        $limite,
        $offset
    );

    $status = $_SESSION['status_predefinicao'] ?? '';
    unset($_SESSION['status_predefinicao']);
    $mostrarModal = false;
    $modalTitulo = '';
    $modalMensagem = '';

    if (!empty($status)) {
        $mostrarModal = true;

        switch ($status) {
            case 'predefinicao_adicionada':
                $modalTitulo = 'Predefinição Adicionada!';
                $modalMensagem = 'Sua nova predefinição foi cadastrada com sucesso.';
                break;

            case 'erro_predefinicao_adicionada':
                $modalTitulo = 'Erro ao Adicionar!';
                $modalMensagem = 'Não foi possível cadastrar a predefinição. Por favor, verifique os dados e tente novamente.';
                break;

            case 'predefinicao_alterada':
                $modalTitulo = 'Predefinição Atualizada!';
                $modalMensagem = 'Os dados da sua predefinição foram editados e salvos com sucesso.';
                break;

            case 'erro_predefinicao_alterada':
                $modalTitulo = 'Erro ao Editar';
                $modalMensagem = 'Não foi possível salvar as alterações da predefinição. Tente novamente.';
                break;

            case 'predefinicao_deletada':
                $modalTitulo = 'Predefinição Removida';
                $modalMensagem = 'A predefinição foi excluída permanentemente do seu histórico.';
                break;

            case 'erro_predefinicao_deletada':
                $modalTitulo = 'Erro ao Excluir';
                $modalMensagem = 'Houve uma falha ao tentar excluir a predefinição. Por favor, tente de novo.';
                break;

            default:
                $mostrarModal = false;
                break;
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Control - Predefinições</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body id="app-page">

    <header class="app-topbar">
        <svg class="ticker-line" viewBox="0 0 600 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <polyline points="0,150 60,140 120,160 180,120 240,135 300,90 360,110 420,70 480,85 540,55 600,65"
                fill="none" stroke="#16a05f" stroke-width="1.5" opacity="0.35" />
        </svg>

        <div class="app-brand">
            <img src="assets/img/logo.png" alt="Finance Control" class="logo-mark">
        </div>

        <nav class="app-nav">
            <a href="transacoes">Transações</a>
            <a href="recorrentes">Transações Recorrentes</a>
            <a href="predefinicoes" class="active">Predefinições</a>
            <a href="dashboards">Dashboards</a>
            <a href="perfil">Perfil</a>
            <span class="app-nav-divider"></span>
            <a href="logout" class="logout">Sair</a>
        </nav>
    </header>

    <main class="app-content">

        <div class="app-page-header">
            <div>
                <span class="eyebrow">Olá, <?= sanitizeInput($usernameUsuario) ?></span>
                <h2>Predefinições</h2>
            </div>
            <div class="app-header-actions">
                <button type="button" class="btn-primary" onclick="document.getElementById('modalPredefinicao').showModal()">
                    + Adicionar predefinição
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='importCSV/importar_predefinicoes'">
                    Importar CSV
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='exportCSV/exportar_predefinicoes'">
                    Exportar CSV
                </button>
            </div>
        </div>

        <form method="GET" action="predefinicoes">

            <div class="app-card">
                <div class="app-table-wrap">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Ordem</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                            <tr class="filter-row">
                                <th></th>
                                <th>
                                    <select name="tipo">
                                        <option value="">Todos</option>
                                        <option value="Entrada" <?= $tipo == 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                                        <option value="Saída"   <?= $tipo == 'Saída'   ? 'selected' : '' ?>>Saída</option>
                                    </select>
                                </th>
                                <th>
                                    <input type="text" name="descricao" placeholder="Buscar..." maxlength="90"
                                           value="<?= sanitizeInput($descricao) ?>">
                                </th>
                                <th>
                                    <select name="categoria">
                                        <option value="">Todas</option>
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
                                            echo '<option value="' . sanitizeInput($cat) . '" ' . $selected . '>'
                                                 . sanitizeInput($cat) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th style="min-width: 170px;">
                                    <div class="value-filter-group">
                                        <select name="operador_valor" class="op-select">
                                            <option value="">~</option>
                                            <option value="igual_a"   <?= $operador_valor == 'igual_a'   ? 'selected' : '' ?>>=</option>
                                            <option value="maior_que" <?= $operador_valor == 'maior_que' ? 'selected' : '' ?>>&gt;</option>
                                            <option value="menor_que" <?= $operador_valor == 'menor_que' ? 'selected' : '' ?>>&lt;</option>
                                        </select>
                                        <input type="number" step="0.01" name="valor" placeholder="0.00"
                                               value="<?= sanitizeInput($valor) ?>">
                                    </div>
                                </th>
                                <th style="white-space: nowrap;">
                                    <button type="submit" class="btn-primary" style="height: 36px; padding: 0 14px; font-size: 13px;">Filtrar</button>
                                    <button type="button" class="btn-secondary" style="height: 36px; padding: 0 12px; font-size: 13px;" onclick="window.location.href='predefinicoes'">Resetar</button>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($predefinicoesPaginadas) === 0): ?>
                                <tr class="empty-row">
                                    <td colspan="7">Nenhuma predefinição encontrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php $order = $offset; foreach ($predefinicoesPaginadas as $predefinicao): ?>
                                    <tr>
                                        <td><?php $order += 1; echo($order); ?></td>
                                        <td>
                                            <?php $tipoClasse = sanitizeInput($predefinicao['tipo']) === 'Entrada' ? 'entrada' : 'saida'; ?>
                                            <span class="badge <?= $tipoClasse ?>"><?= sanitizeInput($predefinicao['tipo']) ?></span>
                                        </td>
                                        <td><?= sanitizeInput($predefinicao['descricao']) ?></td>
                                        <td><?= sanitizeInput($predefinicao['categoria']) ?></td>
                                        <td>R$ <?= sanitizeInput($predefinicao['valor']) ?></td>
                                        <td>
                                            <div class="row-actions">
                                                <a href="editPredefinicao/editar_predefinicao?id=<?= $predefinicao['id'] ?>" class="row-action-btn edit">
                                                    <img src="assets/img/editar.png" alt="Editar" width="20" height="20">
                                                </a>
                                                <a href="editPredefinicao/excluir_predefinicao?id=<?= $predefinicao['id'] ?>"
                                                   onclick="return confirm('Tem certeza que deseja excluir esta predefinição?')" class="row-action-btn delete">
                                                    <img src="assets/img/excluir.png" alt="Excluir" width="20" height="20">
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-bar">
                    <div class="page-size">
                        <span>Itens por página</span>
                        <input type="number" name="tamanho_paginas" placeholder="10" step="1" min="1" max="100"
                               value="<?= sanitizeInput($limite) ?>">
                    </div>

                    <div class="pagination-links">
                        <?php
                        $tamanhoSetor = 5;
                        $qntSetores = ceil($totalPaginas / $tamanhoSetor);
                        $setorAtual = ceil($paginaAtual / $tamanhoSetor);

                        $paginaInicio = (($setorAtual - 1) * $tamanhoSetor) + 1;
                        $paginaFim = min($setorAtual * $tamanhoSetor, $totalPaginas);

                        $voltarAba = (($paginaInicio - 5) > 0) ? $paginaInicio - 5 : 1;
                        echo '<a href="' . linkPagina($voltarAba) . '" class="jump">&laquo; Aba</a>';

                        $voltarPagina = ($paginaAtual > 1) ? $paginaAtual - 1 : 1;
                        echo '<a href="' . linkPagina($voltarPagina) . '">&larr;</a>';

                        for ($i = $paginaInicio; $i <= $paginaFim; $i++) {
                            $ativo = ($i == $paginaAtual) ? ' active' : '';
                            echo '<a href="' . linkPagina($i) . '" class="' . trim($ativo) . '">' . $i . '</a>';
                        }

                        $proximaPagina = ($paginaAtual < $totalPaginas) ? $paginaAtual + 1 : $totalPaginas;
                        echo '<a href="' . linkPagina($proximaPagina) . '">&rarr;</a>';

                        $proximaAba = (($paginaFim + 1) <= $totalPaginas) ? $paginaFim + 1 : $totalPaginas;
                        echo '<a href="' . linkPagina($proximaAba) . '" class="jump">Aba &raquo;</a>';
                        ?>
                    </div>

                    <div class="pagination-status">
                        Página <?= $paginaAtual . ' de ' . max($totalPaginas, 1) ?>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <dialog id="modalPredefinicao" class="app-dialog">
        <div class="dialog-inner">
            <h2>Nova Predefinição</h2>
            <form method="POST" action="editPredefinicao/salvar_predefinicao">
                <div class="input-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="Entrada">Entrada</option>
                        <option value="Saída">Saída</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="descricao">Descrição</label>
                    <input type="text" id="descricao" name="descricao" maxlength="90" required placeholder="Ex: Compra de chocolate">
                </div>

                <div class="input-group">
                    <label for="valor">Valor (R$)</label>
                    <input type="number" step="0.01" id="valor" name="valor" required placeholder="0.00">
                </div>

                <div class="input-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" required onchange="mostrarCampoNovaCategoria()">
                        <option value="" disabled selected>Selecione uma categoria</option>
                        <?php
                        $transacoes = getTransactionsByUserId($pdo, $idUsuario);
                        $categoriasRepetidas = [];
                        foreach ($transacoes as $transacao) {
                            $categoriaOpt = sanitizeInput($transacao['categoria']);
                            if (!empty($categoriaOpt) && !in_array($categoriaOpt, $categoriasRepetidas)) {
                                echo '<option value="' . sanitizeInput($categoriaOpt) . '">'
                                    . sanitizeInput($categoriaOpt) . '</option>';
                                $categoriasRepetidas[] = $categoriaOpt;
                            }
                        }
                        ?>
                        <option value="nova_categoria">+ Adicionar nova categoria..</option>
                    </select>
                    <input type="text" id="nova_categoria" name="nova_categoria"
                        placeholder="Digite a nova categoria" style="display: none; margin-top: 8px;">
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('modalPredefiniacao').close()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </dialog>

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