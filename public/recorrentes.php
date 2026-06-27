<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    $idUsuario = $_SESSION['idUsuario'];
    $usernameUsuario = $_SESSION['usernameUsuario'];
    
    $data_inicial           = $_GET['data_inicial']           ?? date('Y-m-01');
    $data_final             = $_GET['data_final']             ?? date('Y-m-t');
    $tipo                   = $_GET['tipo']                   ?? '';
    $descricao              = $_GET['descricao']              ?? '';
    $categoria              = $_GET['categoria']              ?? '';
    $operador_valor         = $_GET['operador_valor']         ?? '';
    $valor                  = $_GET['valor']                  ?? '';
    $dia_transacao          = $_GET['dia_transacao']          ?? '';
    $data_inicio_filtro     = $_GET['data_inicio_transacao']  ?? '';
    $data_termino_filtro    = $_GET['data_termino_transacao'] ?? '';
    $limite          = $_GET['tamanho_paginas']               ?? 10;

    $data_inicio_transacao = '';
    if (!empty($data_inicio_filtro) && preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $data_inicio_filtro)) {
        $partesInicio = explode('/', $data_inicio_filtro);
        $data_inicio_transacao = $partesInicio[1] . '-' . $partesInicio[0] . '-01';
    }

    $data_termino_transacao = '';
    if (!empty($data_termino_filtro) && preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $data_termino_filtro)) {
        $partesTermino = explode('/', $data_termino_filtro);
        $data_termino_transacao = $partesTermino[1] . '-' . $partesTermino[0] . '-01';
    }
    
    $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

    $recorrentes = getRecurringByUserIdAndParams(
        $pdo,
        $idUsuario,
        $data_inicial,
        $data_final,
        $tipo,
        $descricao,
        $categoria,
        $operador_valor,
        $valor,
        $dia_transacao,
        $data_inicio_transacao,
        $data_termino_transacao
    );

    $totalPaginas = (int) ceil(count($recorrentes) / $limite);
    
    if ($paginaAtual < 1 || ($totalPaginas > 0 && $paginaAtual > $totalPaginas)) {
        $query_params = $_GET;
        $query_params['pagina'] = 1;
        header("Location: ?" . http_build_query($query_params));
        exit;
    }
    
    $offset = ($paginaAtual - 1) * $limite;

    $recorrentesPaginadas = getRecurringByUserIdAndParamsAndPagination(
        $pdo,
        $idUsuario,
        $data_inicial,
        $data_final,
        $tipo,
        $descricao,
        $categoria,
        $operador_valor,
        $valor,
        $dia_transacao,
        $data_inicio_transacao,
        $data_termino_transacao,
        $limite,
        $offset
    );

    $status = $_SESSION['status_recorrente'] ?? '';
    unset($_SESSION['status_recorrente']);
    $mostrarModal = false;
    $modalTitulo = '';
    $modalMensagem = '';

    if (!empty($status)) {
        $mostrarModal = true;

        switch ($status) {
            case 'recorrente_adicionada':
                $modalTitulo = 'Sucesso!';
                $modalMensagem = 'Sua nova transação recorrente foi cadastrada com sucesso.';
                break;
            
            case 'data_termino_maior':
                $modalTitulo = 'Data Incorreta.';
                $modalMensagem = 'Você inseriu a data incorretamente, ou tentou inserir a data de término anterior ou igual à data de inicio.';
                break;

            case 'erro_recorrente_adicionada':
                $modalTitulo = 'Erro ao cadastrar';
                $modalMensagem = 'Não foi possível salvar a transação recorrente. Por favor, tente novamente.';
                break;

            case 'recorrente_deletada':
                $modalTitulo = 'Excluído!';
                $modalMensagem = 'A transação recorrente foi removida com sucesso.';
                break;

            case 'erro_recorrente_deletada':
                $modalTitulo = 'Erro ao excluir';
                $modalMensagem = 'Houve um problema ao tentar excluir a transação recorrente.';
                break;

            case 'recorrente_editada':
                $modalTitulo = 'Atualizado!';
                $modalMensagem = 'As alterações da transação recorrente foram salvas com sucesso.';
                break;

            case 'erro_recorrente_editada':
                $modalTitulo = 'Erro ao editar';
                $modalMensagem = 'Não foi possível atualizar os dados desta transação recorrente.';
                break;

            default:
                $modalTitulo = 'Aviso';
                $modalMensagem = 'Ação concluída.';
                break;
        }
    }
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
        <a href="transacoes.php">Transações</a> | 
        <a href="recorrentes.php">Transações Recorrentes</a> | 
        <a href="dashboards.php">Dashboards</a> | 
        <a href="perfil.php">Perfil</a> | 
        <a href="logout.php">Sair</a>
    </nav>
    <br>
    <h2>Olá, <?= $usernameUsuario ?></h2>
    <br>
    <button type="button" onclick="document.getElementById('modalRecorrente').showModal()">
        + Adicionar transação recorrente
    </button>
    <button type="button" onclick="window.location.href='importCSV/importar_recorrentes.php'">
        Importar CSV
    </button>
    <button type="button" onclick="window.location.href='exportCSV/exportar_recorrentes.php'">
        Exportar CSV
    </button>
    
    <form method="GET" action="recorrentes.php">
        <p>
            Mostrando transações recorrentes ativas no período de
            <input type="date" name="data_inicial" value="<?= $data_inicial ?>" required>
            até
            <input type="date" name="data_final" value="<?= $data_final ?>" required>
             : 
        </p>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>

                    <th colspan="9" style="padding: 8px; background-color: #f8f9fa; position: relative; font-weight: normal;">            
                        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">                  
                            <div style="display: inline-block;">
                                <span style="position: absolute; left: 15px; font-weight: normal; font-size: 0.9rem; color: #6c757d;">
                                    <p>Filtrar por:  
                                        <input type="number" name="tamanho_paginas" placeholder="10" maxlength="90" step="1" style="text-align: center; width: 40px;"
                                        value="<?= sanitizeInput($limite) ?>">
                                    </p>
                                </span>
                                <?php
                                $tamanhoSetor = 5;
                                $qntSetores = ceil($totalPaginas / $tamanhoSetor);
                                $setorAtual = ceil($paginaAtual / $tamanhoSetor);

                                $paginaInicio = (($setorAtual - 1) * $tamanhoSetor) + 1;
                                $paginaFim = min($setorAtual * $tamanhoSetor, $totalPaginas);

                                $voltarAba = (($paginaInicio - 5) > 0) ? $paginaInicio - 5 : 1;
                                echo '<a href="' . linkPagina($voltarAba) . '" style="margin-right: 15px; font-size: 0.75rem;"><< Voltar Aba</a>';

                                $voltarPagina = ($paginaAtual > 1) ? $paginaAtual - 1 : 1;
                                echo '<a href="' . linkPagina($voltarPagina) . '" style="margin-right: 10px;">← Voltar</a>';

                                for ($i = $paginaInicio; $i <= $paginaFim; $i++) {
                                    $ativo = ($i == $paginaAtual) ? 'font-size: 1.2rem; text-decoration: underline; font-weight: bold;' : '';
                                    echo '<a href="' . linkPagina($i) . '" style="margin: 0 8px; ' . $ativo . '">' . $i . '</a>';
                                }

                                $proximaPagina = ($paginaAtual < $totalPaginas) ? $paginaAtual + 1 : $totalPaginas;
                                echo '<a href="' . linkPagina($proximaPagina) . '" style="margin-left: 10px;">Próxima →</a>';

                                $proximaAba = (($paginaFim + 1) <= $totalPaginas) ? $paginaFim + 1 : $totalPaginas;
                                echo '<a href="' . linkPagina($proximaAba) . '" style="margin-left: 15px; font-size: 0.75rem;">Próxima Aba >></a>';
                                ?>
                            </div>

                            <span style="position: absolute; right: 15px; font-weight: normal; font-size: 0.9rem; color: #6c757d;">
                                <p><?= $paginaAtual . '/' . $totalPaginas ?></p>
                            </span>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Ordem</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Dia da Transação</th>
                    <th>Mês de Início</th>
                    <th>Mês de Término</th>
                    <th>Ações</th>
                </tr>
                <tr>
                    <th></th>
                    <th>
                        <select name="tipo" style="text-align: center;">
                            <option value="">~</option>
                            <option value="Entrada" <?= $tipo == 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                            <option value="Saída"   <?= $tipo == 'Saída'   ? 'selected' : '' ?>>Saída</option>
                        </select>
                    </th>
                    <th>
                        <input type="text" name="descricao" placeholder="~" maxlength="90" 
                               value="<?= sanitizeInput($descricao) ?>" style="text-align: center;">
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
                                echo '<option value="' . sanitizeInput($cat) . '" ' . $selected . '>' 
                                     . sanitizeInput($cat) . '</option>';
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
                               value="<?= sanitizeInput($valor) ?>" style="text-align: center;">
                    </th>
                    <th>
                        <input type="number" id="dia_transacao" name="dia_transacao" min="1" max="28" step="1" placeholder="Dia (1 a 28)"
                        value="<?= $dia_transacao ?>">
                    </th>
                    <th>
                        <input type="text" 
                               id="filtra_data_inicio"
                               name="data_inicio_transacao" 
                               placeholder="MM/AAAA"
                               maxlength="7"
                               value="<?= sanitizeInput($data_inicio_filtro) ?>" 
                               style="text-align: center; width: 90px;">
                    </th>
                    <th>
                        <input type="text" 
                               id="filtra_data_termino"
                               name="data_termino_transacao" 
                               placeholder="MM/AAAA"
                               maxlength="7"
                               value="<?= sanitizeInput($data_termino_filtro) ?>" 
                               style="text-align: center; width: 90px;">
                    </th>
                    <th>
                        <button type="submit">Filtrar</button>
                        <button type="button" onclick="window.location.href='recorrentes.php'">Resetar</button>
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($recorrentesPaginadas) === 0): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">Nenhuma transação encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php $order = $offset; foreach ($recorrentesPaginadas as $recorrente): ?>
                        <tr>
                            <td><?php $order += 1; echo($order);?></td>
                            <td><?= sanitizeInput($recorrente['tipo']) ?></td>
                            <td><?= sanitizeInput($recorrente['descricao']) ?></td>
                            <td><?= sanitizeInput($recorrente['categoria']) ?></td>
                            <td>R$ <?= sanitizeInput($recorrente['valor']) ?></td>
                            <td><?= sanitizeInput($recorrente['dia_transacao']) ?></td>
                            <td><?= date('m/Y', strtotime(sanitizeInput($recorrente['data_transacao_inicio']))) ?></td>
                            <td><?= !empty($recorrente['data_transacao_termino']) ? 
                            date('m/Y', strtotime(sanitizeInput($recorrente['data_transacao_termino']))) : 'N/A' ?></td>
                            <td style="text-align: center;">
                                <a href="editRecorrente/editar_recorrente.php?id=<?= $recorrente['id'] ?>">
                                    <img src="assets/img/editar.png" alt="Editar" width="23" height="23">
                                </a>
                                <a href="editRecorrente/excluir_recorrente.php?id=<?= $recorrente['id'] ?>"
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
    <dialog id="modalRecorrente">
        <h2>Nova Transação Recorrente</h2>
        <form method="POST" action="editRecorrente/salvar_recorrente.php">
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
                <input type="text" id="descricao" name="descricao" maxlength="90" required placeholder="Ex: Assinatura Netflix">
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
                            echo '<option value="' . sanitizeInput($categoria) . '">'
                                . sanitizeInput($categoria) . '</option>';
                            $categoriasRepetidas[] = $categoria;
                        }
                    }
                    ?>
                    <option value="nova_categoria">+ Adicionar nova categoria..</option>
                </select>
                <input type="text" id="nova_categoria" name="nova_categoria" 
                    placeholder="Digite a nova categoria" style="display: none; margin-top: 5px;">
            </p>

            <p>
                <label for="dia_transacao">Dia da Transação:</label><br>
                <input type="number" id="dia_transacao" name="dia_transacao" min="1" max="28" step="1" placeholder="Dia da transação (1 a 28)"
                        value="<?= $dia_transacao ?>">
            </p>

            <p>
                <label for="data_transacao_inicio">Data de Início:</label><br>
                <input type="text" 
                    id="data_transacao_inicio" 
                    name="data_transacao_inicio" 
                    required 
                    placeholder="MM/AAAA"
                    maxlength="7"
                    style="text-align: center; width: 100px;"
                    value="<?= date('m/Y') ?>">
            </p>

            <p>
                <label for="data_transacao_termino">Data de Término (Opcional):</label><br>
                <input type="text" 
                id="data_transacao_termino" 
                name="data_transacao_termino"
                placeholder="MM/AAAA"
                maxlength="7"
                style="text-align: center; width: 100px;">
                <small style="color: #6c757d; display: block; margin-top: 2px;">Deixe em branco se for por tempo indeterminado.</small>
            </p>

            <p>
                <button type="button" onclick="document.getElementById('modalRecorrente').close()">Cancelar</button>
                <button type="submit">Salvar</button>
            </p>
        </form>
    </dialog>
    <dialog id="modalStatus" style="padding: 20px; border-radius: 8px; border: 1px solid #ccc; position: fixed; inset: 0; margin: auto; max-width: 400px; height: fit-content;">
        <h2><?= sanitizeInput($modalTitulo) ?></h2>
        <p><?= sanitizeInput($modalMensagem) ?></p>
        <button type="button" onclick="document.getElementById('modalStatus').close()">Fechar</button>
    </dialog>
    <?php if ($mostrarModal): ?>
        <script>
            document.getElementById('modalStatus').showModal();
        </script>
    <?php endif; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            aplicarMascaraMesAno('data_transacao_inicio');
            aplicarMascaraMesAno('data_transacao_termino');
            aplicarMascaraMesAno('filtra_data_inicio');
            aplicarMascaraMesAno('filtra_data_termino');
        });
    </script>
</body>
</html>