<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_csv'])) {
        $idUsuario = $_SESSION['idUsuario'];
        $arquivo = $_FILES['arquivo_csv'];

        if ($arquivo['error'] !== UPLOAD_ERR_OK || empty($arquivo['tmp_name'])) {
            $_SESSION['status_importacao'] = 'erro_arquivo';
            redirect("importar_transacoes.php");
            exit;
        }

        if (($handle = fopen($arquivo['tmp_name'], 'r')) !== false) {

            fgetcsv($handle, 0, ';');

            $pdo->beginTransaction();

            try {
                while (($dados = fgetcsv($handle, 0, ';')) !== false) {
                    if (empty($dados) || (count($dados) === 1 && empty($dados[0]))) {
                        continue;
                    }

                    if (count($dados) < 5) {
                        throw new Exception("Linha incompleta no arquivo CSV.");
                    }

                    $tipo      = trim($dados[0]);
                    $descricao = trim($dados[1]);
                    $valorRaw  = trim($dados[2]);
                    $categoria = trim($dados[3]);
                    $dataRaw   = trim($dados[4]);

                    if ($tipo !== 'Entrada' && $tipo !== 'Saída') {
                        throw new Exception("Tipo inválido. Deve ser 'Entrada' ou 'Saída'.");
                    }

                    if (empty($descricao)) {
                        throw new Exception("A descrição não pode estar vazia.");
                    }

                    if ($valorRaw === '') {
                        throw new Exception("O valor não pode estar vazio.");
                    }

                    $valor = str_replace(',', '.', str_replace('.', '', $valorRaw));
                    if (!is_numeric($valor)) {
                        throw new Exception("Formato de valor numérico inválido.");
                    }
                    $valor = (float)$valor;

                    if (empty($categoria)) {
                        throw new Exception("A categoria não pode estar vazia.");
                    }

                    if (!preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[0-9]{4}$/', $dataRaw)) {
                        throw new Exception("Formato de data inválido. Use DD/MM/AAAA.");
                    }

                    $partesData = explode('/', $dataRaw);
                    $dia = (int)$partesData[0];
                    $mes = (int)$partesData[1];
                    $ano = (int)$partesData[2];

                    if (!checkdate($mes, $dia, $ano)) {
                        throw new Exception("A data informada não existe no calendário.");
                    }

                    $data_transacao = $ano . '-' . str_pad($mes, 2, "0", STR_PAD_LEFT) . '-' . str_pad($dia, 2, "0", STR_PAD_LEFT);

                    $stmt = $pdo->prepare("INSERT INTO transacoes (id_usuario, tipo, descricao, valor, categoria, data_transacao) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$idUsuario, $tipo, $descricao, $valor, $categoria, $data_transacao]);
                }

                $pdo->commit();
                $_SESSION['status_importacao'] = 'sucesso';

            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['status_importacao'] = 'erro_processamento';
            }

            fclose($handle);
        } else {
            $_SESSION['status_importacao'] = 'erro_processamento';
        }

        redirect("importar_transacoes.php");
        exit;
    } else {
        redirect("importar_transacoes.php");
        exit;
    }
?>