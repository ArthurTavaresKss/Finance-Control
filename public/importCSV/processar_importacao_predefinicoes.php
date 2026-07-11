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

                    if (count($dados) < 5) {
                        throw new Exception("Linha incompleta no arquivo CSV.");
                    }

                    $tipo      = trim($dados[0]);
                    $descricao = trim($dados[1]);
                    $valorRaw  = trim($dados[2]);
                    $categoria = trim($dados[3]);

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

                    $stmt = $pdo->prepare("INSERT INTO predefinicoes (id_usuario, tipo, descricao, valor, categoria) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$idUsuario, $tipo, $descricao, $valor, $categoria]);
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

        redirect("importar_predefinicoes");
        exit;
    } else {
        redirect("importar_predefinicoes");
        exit;
    }
?>