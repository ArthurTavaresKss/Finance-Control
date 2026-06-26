<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';
    
    $sucesso = false;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idRecorrente = $_GET['id'];
        $idUsuario = $_SESSION['idUsuario'];

        $recorrente = getRecurringByUserIdAndId($pdo, $idUsuario, $idRecorrente);
        
        if (!$recorrente) {
            redirect("../recorrentes.php");
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id                     = $_POST['id'];
        $tipo                   = $_POST['tipo'];
        $descricao              = trim($_POST['descricao']);
        $valor                  = $_POST['valor'];
        $categoria              = $_POST['categoria'];
        $idUsuario              = $_SESSION['idUsuario'];
        
        $dia_transacao          = (int)$_POST['dia_transacao'];
        $dia_formatado          = str_pad($dia_transacao, 2, "0", STR_PAD_LEFT);

        if (empty($_POST['data_transacao_inicio']) || !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $_POST['data_transacao_inicio'])) {
            $_SESSION['status_recorrente'] = 'data_termino_maior';
            redirect("../recorrentes.php");
            exit;
        }

        $partes_inicio = explode('/', $_POST['data_transacao_inicio']);
        $mes_inicio = $partes_inicio[0];
        $ano_inicio = $partes_inicio[1];
        $data_transacao_inicio = $ano_inicio . '-' . $mes_inicio . '-' . $dia_formatado;

        if (!empty($_POST['data_transacao_termino'])) {
            
            if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $_POST['data_transacao_termino'])) {
                $_SESSION['status_recorrente'] = 'data_termino_maior';
                redirect("../recorrentes.php");
                exit;
            }

            $partes_termino = explode('/', $_POST['data_transacao_termino']);
            $mes_termino = $partes_termino[0];
            $ano_termino = $partes_termino[1];
            $data_transacao_termino = $ano_termino . '-' . $mes_termino . '-' . $dia_formatado;
            
            if (strtotime($data_transacao_termino) <= strtotime($data_transacao_inicio)) {
                $_SESSION['status_recorrente'] = 'data_termino_maior';
                redirect("../recorrentes.php");
                exit;
            }
        } else {
            $data_transacao_termino = null;
        }

        if ($categoria === 'nova_categoria') {
            $categoria = trim($_POST['nova_categoria']);
        }

        $sucesso = alterRecurringById(
            $pdo,
            $id,
            $idUsuario,
            $tipo,
            $descricao,
            $valor,
            $categoria,
            $dia_transacao,
            $data_transacao_inicio,
            $data_transacao_termino
        );

        if ($sucesso) {
            $_SESSION['status_recorrente'] = 'recorrente_editada';
        } else {
            $_SESSION['status_recorrente'] = 'erro_recorrente_editada';
        }
        redirect("../recorrentes.php");
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Transação Recorrente</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <h1>Editar Transação Recorrente</h1>
    <form method="POST" action="editar_recorrente.php">
        
        <label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" required>
            <option value="Entrada" <?= ($recorrente['tipo'] == 'Entrada') ? 'selected' : '' ?>>
                Entrada
            </option>
            <option value="Saída" <?= ($recorrente['tipo'] == 'Saída') ? 'selected' : '' ?>>
                Saída
            </option>
        </select><br><br>

        <label for="descricao">Descrição:</label>
        <input type="text" id="descricao" name="descricao" maxlength="90" required 
        placeholder="Ex: Assinatura Netflix" value="<?= sanitizeInput($recorrente['descricao']) ?>"><br><br>

        <label for="valor">Valor (R$):</label>
        <input type="number" step="0.01" id="valor" name="valor" required 
        placeholder="0.00" value="<?= $recorrente['valor'] ?>"><br><br>

        <label for="categoria">Categoria:</label>
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
                $selected = ($cat === $recorrente['categoria']) ? 'selected' : '';
                echo '<option value="' . sanitizeInput($cat) . '" ' . $selected . '>'
                    . sanitizeInput($cat) . '</option>';
            }
            ?>
            <option value="nova_categoria">+ Adicionar nova categoria...</option>
        </select>

        <input type="text" id="nova_categoria" name="nova_categoria" 
            placeholder="Digite a nova categoria" style="display: none; margin-top: 5px;">
        
        <br><br>
        
        <label for="dia_transacao">Dia da Transação:</label><br>
        <input type="number" id="dia_transacao" name="dia_transacao" min="1" max="28" step="1" placeholder="Dia da transação (1 a 28)"
        value="<?= $recorrente['dia_transacao'] ?>"><br><br>

        <label for="data_transacao_inicio">Data de Início:</label>
        <input type="text" 
               id="data_transacao_inicio" 
               name="data_transacao_inicio" 
               required 
               placeholder="MM/AAAA"
               maxlength="7"
               style="text-align: center; width: 100px;"
               value="<?= date('m/Y', strtotime($recorrente['data_transacao_inicio'])) ?>"><br><br>

        <label for="data_transacao_termino">Data de Término (Opcional):</label>
        <input type="text" 
               id="data_transacao_termino" 
               name="data_transacao_termino"
               placeholder="MM/AAAA"
               maxlength="7"
               style="text-align: center; width: 100px;"
               value="<?= !empty($recorrente['data_transacao_termino']) ? date('m/Y', strtotime($recorrente['data_transacao_termino'])) : '' ?>"><br><br>

        <input type="hidden" name="id" value="<?= $recorrente['id'] ?>">

        <button type="button" onclick="window.location.href='../recorrentes.php'">Cancelar</button>
        <button type="submit">Salvar</button>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            aplicarMascaraMesAno('data_transacao_inicio');
            aplicarMascaraMesAno('data_transacao_termino');
        });
    </script>
</body>
</html>