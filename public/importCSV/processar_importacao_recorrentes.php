<?php
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../includes/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_csv'])) {
        $idUsuario = $_SESSION['idUsuario'];
        $arquivo = $_FILES['arquivo_csv'];

        if ($arquivo['error'] !== UPLOAD_ERR_OK || empty($arquivo['tmp_name'])) {
            $_SESSION['status_importacao'] = 'erro_arquivo';
            redirect("importar_transacoes");
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

                    if (count($dados) < 6) {
                        throw new Exception("Linha incompleta no CSV.");
                    }

                    $tipo           = trim($dados[0]);
                    $descricao      = trim($dados[1]);
                    $valorRaw       = trim($dados[2]);
                    $categoria      = trim($dados[3]);
                    $diaRaw         = trim($dados[4]);
                    $dataInicioRaw  = trim($dados[5]);
                    $dataTerminoRaw = isset($dados[6]) ? trim($dados[6]) : '';

                    if ($tipo !== 'Entrada' && $tipo !== 'Saída') {
                        throw new Exception("Tipo inválido.");
                    }

                    if (empty($descricao) || empty($categoria)) {
                        throw new Exception("Campos obrigatórios vazios.");
                    }

                    $valor = str_replace(',', '.', str_replace('.', '', $valorRaw));
                    if (!is_numeric($valor)) {
                        throw new Exception("Valor numérico inválido.");
                    }
                    $valor = (float)$valor;

                    $dia_transacao = (int)$diaRaw;
                    if ($dia_transacao < 1 || $dia_transacao > 28) {
                        throw new Exception("O dia da transação deve ser entre 1 e 28.");
                    }
                    $dia_formatado = str_pad($dia_transacao, 2, "0", STR_PAD_LEFT);

                    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $dataInicioRaw)) {
                        throw new Exception("Formato da data de início inválido.");
                    }
                    $partesInicio = explode('/', $dataInicioRaw);
                    $mesInicio = (int)$partesInicio[0];
                    $anoInicio = (int)$partesInicio[1];

                    if (!checkdate($mesInicio, $dia_transacao, $anoInicio)) {
                        throw new Exception("Data de início não existe no calendário.");
                    }
                    $data_transacao_inicio = $anoInicio . '-' . str_pad($mesInicio, 2, "0", STR_PAD_LEFT) . '-' . $dia_formatado;

                    $data_transacao_termino = null;
                    if (!empty($dataTerminoRaw)) {
                        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{4}$/', $dataTerminoRaw)) {
                            throw new Exception("Formato da data de término inválido.");
                        }
                        $partesTermino = explode('/', $dataTerminoRaw);
                        $mesTermino = (int)$partesTermino[0];
                        $anoTermino = (int)$partesTermino[1];

                        if (!checkdate($mesTermino, $dia_transacao, $anoTermino)) {
                            throw new Exception("Data de término não existe no calendário.");
                        }
                        $data_transacao_termino = $anoTermino . '-' . str_pad($mesTermino, 2, "0", STR_PAD_LEFT) . '-' . $dia_formatado;

                        if (strtotime($data_transacao_termino) <= strtotime($data_transacao_inicio)) {
                            throw new Exception("A data de término deve ser maior que a data de início.");
                        }
                    }

                    $sql = "INSERT INTO transacoes_recorrentes 
                            (id_usuario, tipo, descricao, valor, categoria, dia_transacao, data_transacao_inicio, data_transacao_termino) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $idUsuario, 
                        $tipo, 
                        $descricao, 
                        $valor, 
                        $categoria, 
                        $dia_transacao, 
                        $data_transacao_inicio, 
                        $data_transacao_termino
                    ]);
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

        redirect("importar_recorrentes");
        exit;
    } else {
        redirect("importar_recorrentes");
        exit;
    }
?>