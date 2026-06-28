<?php

function sanitizeInput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['idUsuario']);
}

function getUserByEmail($pdo, $email) {
    $sql = "SELECT * FROM usuarios WHERE email = :email AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByUsername($pdo, $username) {
    $sql = "SELECT * FROM usuarios WHERE username = :username AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($pdo, $id) {
    $sql = "SELECT * FROM usuarios WHERE id = :id AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function insertUser($pdo, $username, $email, $senha) {
    $sql = "INSERT INTO usuarios (username, email, senha) VALUES
    (:username, :email, :senha)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    return $stmt->execute();
}

function getTransactionsByUserIdAndParamsAndPagination($pdo, $idUsuario, $dataInicial, $dataFinal, $tipo, $descricao,
 $categoria, $operadorValor, $valor, $dataTransacao, $limite, $offset) {
    
    $sql = "SELECT * FROM transacoes WHERE id_usuario = :idUsuario";
    $conditions = [];
    $params = [':idUsuario' => $idUsuario];

    if (!empty($dataInicial) && !empty($dataFinal)) {
        $conditions[] = "data_transacao BETWEEN :data_inicial AND :data_final";
        $params[':data_inicial'] = $dataInicial;
        $params[':data_final'] = $dataFinal;
    }

    if (!empty($tipo)) {
        $conditions[] = "tipo = :tipo";
        $params[':tipo'] = $tipo;
    }

    if (!empty($descricao)) {
        $conditions[] = "descricao LIKE :descricao";
        $params[':descricao'] = "%$descricao%";
    }

    if (!empty($categoria)) {
        $conditions[] = "categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    if (!empty($valor) && !empty($operadorValor)) {
        if ($operadorValor === 'igual_a') {
            $conditions[] = "valor = :valor";
        } elseif ($operadorValor === 'maior_que') {
            $conditions[] = "valor > :valor";
        } elseif ($operadorValor === 'menor_que') {
            $conditions[] = "valor < :valor";
        }
        $params[':valor'] = $valor;
    }

    if (!empty($dataTransacao)) {
        $conditions[] = "data_transacao = :data_transacao";
        $params[':data_transacao'] = $dataTransacao;
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY data_transacao DESC, id DESC";

    $sql .= " LIMIT :limite OFFSET :offset";

    $params[':limite']  = (int)$limite;
    $params[':offset'] = (int)$offset;

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':limite',  $params[':limite'],  PDO::PARAM_INT);
    $stmt->bindParam(':offset', $params[':offset'], PDO::PARAM_INT);

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTransactionsByUserIdAndParams($pdo, $idUsuario, $dataInicial, $dataFinal, $tipo, $descricao,
 $categoria, $operadorValor, $valor, $dataTransacao) {
    
    $sql = "SELECT * FROM transacoes WHERE id_usuario = :idUsuario";
    $conditions = [];
    $params = [':idUsuario' => $idUsuario];

    if (!empty($dataInicial) && !empty($dataFinal)) {
        $conditions[] = "data_transacao BETWEEN :data_inicial AND :data_final";
        $params[':data_inicial'] = $dataInicial;
        $params[':data_final'] = $dataFinal;
    }

    if (!empty($tipo)) {
        $conditions[] = "tipo = :tipo";
        $params[':tipo'] = $tipo;
    }

    if (!empty($descricao)) {
        $conditions[] = "descricao LIKE :descricao";
        $params[':descricao'] = "%$descricao%";
    }

    if (!empty($categoria)) {
        $conditions[] = "categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    if (!empty($valor) && !empty($operadorValor)) {
        if ($operadorValor === 'igual_a') {
            $conditions[] = "valor = :valor";
        } elseif ($operadorValor === 'maior_que') {
            $conditions[] = "valor > :valor";
        } elseif ($operadorValor === 'menor_que') {
            $conditions[] = "valor < :valor";
        }
        $params[':valor'] = $valor;
    }

    if (!empty($dataTransacao)) {
        $conditions[] = "data_transacao = :data_transacao";
        $params[':data_transacao'] = $dataTransacao;
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY data_transacao DESC, id DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTransactionsByUserId($pdo, $idUsuario) {
    $sql = "SELECT * FROM transacoes WHERE id_usuario = :idUsuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTransactionByUserIdAndId($pdo, $idUsuario, $idTransacao) {
    $sql = "SELECT * FROM transacoes WHERE id_usuario = :idUsuario AND id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->bindParam(':id', $idTransacao);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function insertTransacao($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria, $data_transacao) {
    $sql = "INSERT INTO transacoes (id_usuario, tipo, descricao, valor, categoria, data_transacao) VALUES
    (:idUsuario, :tipo, :descricao, :valor, :categoria, :data_transacao)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':data_transacao', $data_transacao);
    return $stmt->execute();
}

function alterTransactionById($pdo, $id, $idUsuario, $tipo, $descricao, $valor, $categoria, $data_transacao) {
    $sql = "UPDATE transacoes 
            SET 
                tipo = :tipo,
                descricao = :descricao,
                valor = :valor,
                categoria = :categoria,
                data_transacao = :data_transacao
            WHERE id = :id 
              AND id_usuario = :idUsuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':data_transacao', $data_transacao);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    return $stmt->execute();
}

function updateUserUsernameAndEmailById($pdo, $idUsuario, $username, $email) {
    $sql = "UPDATE usuarios 
            SET 
                username = :username,
                email = :email
            WHERE id = :idUsuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    return $stmt->execute();
}

function updateUserPasswordById($pdo, $idUsuario, $novaSenhaHash) {
    $sql = "UPDATE usuarios 
            SET 
                senha = :senha
            WHERE id = :idUsuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':senha', $novaSenhaHash);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    return $stmt->execute();
}

function updateUserActiveById($pdo, $idUsuario, $ativo) {
    $sql = "UPDATE usuarios 
            SET 
                ativo = :ativo
            WHERE id = :idUsuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    return $stmt->execute();
}

function deleteTransactionByUserIdAndId($pdo, $idUsuario, $idTransacao) {
    $sql = "DELETE FROM transacoes WHERE id_usuario = :idUsuario AND id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->bindParam(':id', $idTransacao);
    return $stmt->execute();
}

function linkPagina($numPagina) {
    $params = $_GET;
    $params['pagina'] = $numPagina;
    return '?' . http_build_query($params);
}

function getIndicadoresMensais($pdo, $idUsuario, $ano, $mes) {
    $dataInicial = sprintf('%04d-%02d-01', $ano, $mes);
    $dataFinal   = date('Y-m-t', strtotime($dataInicial));
 
    $sql = "SELECT
                IFNULL(SUM(qtd_entradas), 0) AS qtd_entradas,
                IFNULL(SUM(qtd_saidas), 0) AS qtd_saidas,
                IFNULL(SUM(qtd_entradas) + SUM(qtd_saidas), 0) AS qtd_totais,
                IFNULL(SUM(valor_entradas), 0) AS valor_entradas,
                IFNULL(SUM(valor_saidas), 0) AS valor_saidas
            FROM (
                SELECT
                    COUNT(CASE WHEN tipo = 'Entrada' THEN 1 END) AS qtd_entradas,
                    COUNT(CASE WHEN tipo = 'Saída' THEN 1 END) AS qtd_saidas,
                    IFNULL(SUM(CASE WHEN tipo = 'Entrada' THEN valor END), 0) AS valor_entradas,
                    IFNULL(SUM(CASE WHEN tipo = 'Saída' THEN valor END), 0) AS valor_saidas
                FROM transacoes
                WHERE id_usuario = :id_usuario1
                  AND data_transacao BETWEEN :data_inicial1 AND :data_final1
 
                UNION ALL

                SELECT
                    COUNT(CASE WHEN tipo = 'Entrada' THEN 1 END) AS qtd_entradas,
                    COUNT(CASE WHEN tipo = 'Saída' THEN 1 END) AS qtd_saidas,
                    IFNULL(SUM(CASE WHEN tipo = 'Entrada' THEN valor END), 0) AS valor_entradas,
                    IFNULL(SUM(CASE WHEN tipo = 'Saída' THEN valor END), 0) AS valor_saidas
                FROM transacoes_recorrentes
                WHERE id_usuario = :id_usuario2
                  AND data_transacao_inicio <= :data_final2
                  AND (data_transacao_termino IS NULL
                       OR DATE_ADD(data_transacao_termino, INTERVAL 1 DAY) >= :data_inicial2)
            ) AS combinado";
 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario1'   => $idUsuario,
        ':data_inicial1' => $dataInicial,
        ':data_final1'   => $dataFinal,
        ':id_usuario2'   => $idUsuario,
        ':data_final2'   => $dataFinal,
        ':data_inicial2' => $dataInicial,
    ]);
 
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getGastosPorCategoria($pdo, $idUsuario, $ano, $mes) {
    return getMovimentoPorCategoria($pdo, $idUsuario, $ano, $mes, 'Saída');
}
 
function getEntradasPorCategoria($pdo, $idUsuario, $ano, $mes) {
    return getMovimentoPorCategoria($pdo, $idUsuario, $ano, $mes, 'Entrada');
}

function getMovimentoPorCategoria($pdo, $idUsuario, $ano, $mes, $tipo) {
    $dataInicial = sprintf('%04d-%02d-01', $ano, $mes);
    $dataFinal   = date('Y-m-t', strtotime($dataInicial));
 
    $sql = "SELECT categoria, SUM(total) AS total
            FROM (
                SELECT categoria, SUM(valor) AS total
                FROM transacoes
                WHERE id_usuario = :id_usuario1
                  AND tipo = :tipo1
                  AND data_transacao BETWEEN :data_inicial1 AND :data_final1
                GROUP BY categoria
 
                UNION ALL
 
                SELECT categoria, SUM(valor) AS total
                FROM transacoes_recorrentes
                WHERE id_usuario = :id_usuario2
                  AND tipo = :tipo2
                  AND data_transacao_inicio <= :data_final2
                  AND (data_transacao_termino IS NULL
                       OR DATE_ADD(data_transacao_termino, INTERVAL 1 DAY) >= :data_inicial2)
                GROUP BY categoria
            ) AS combinado
            GROUP BY categoria";
 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario1'   => $idUsuario,
        ':tipo1'         => $tipo,
        ':data_inicial1' => $dataInicial,
        ':data_final1'   => $dataFinal,
        ':id_usuario2'   => $idUsuario,
        ':tipo2'         => $tipo,
        ':data_final2'   => $dataFinal,
        ':data_inicial2' => $dataInicial,
    ]);
 
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHistoricoAnual($pdo, $idUsuario, $ano) {
    $resultado = [];
 
    for ($mes = 1; $mes <= 12; $mes++) {
        $dataInicial = sprintf('%04d-%02d-01', $ano, $mes);
        $dataFinal   = date('Y-m-t', strtotime($dataInicial));
 
        $sql = "SELECT
                    IFNULL(SUM(entradas), 0) AS entradas,
                    IFNULL(SUM(saidas), 0) AS saidas
                FROM (
                    SELECT
                        IFNULL(SUM(CASE WHEN tipo = 'Entrada' THEN valor END), 0) AS entradas,
                        IFNULL(SUM(CASE WHEN tipo = 'Saída' THEN valor END), 0) AS saidas
                    FROM transacoes
                    WHERE id_usuario = :id_usuario1
                      AND data_transacao BETWEEN :data_inicial1 AND :data_final1
 
                    UNION ALL
 
                    SELECT
                        IFNULL(SUM(CASE WHEN tipo = 'Entrada' THEN valor END), 0) AS entradas,
                        IFNULL(SUM(CASE WHEN tipo = 'Saída' THEN valor END), 0) AS saidas
                    FROM transacoes_recorrentes
                    WHERE id_usuario = :id_usuario2
                      AND data_transacao_inicio <= :data_final2
                      AND (data_transacao_termino IS NULL
                           OR DATE_ADD(data_transacao_termino, INTERVAL 1 DAY) >= :data_inicial2)
                ) AS combinado";
 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario1'   => $idUsuario,
            ':data_inicial1' => $dataInicial,
            ':data_final1'   => $dataFinal,
            ':id_usuario2'   => $idUsuario,
            ':data_final2'   => $dataFinal,
            ':data_inicial2' => $dataInicial,
        ]);
 
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
 
        $resultado[] = [
            'mes'      => $mes,
            'entradas' => (float)$linha['entradas'],
            'saidas'   => (float)$linha['saidas'],
        ];
    }
 
    return $resultado;
}

function getAnosDisponiveisFiltro($pdo, $idUsuario) {
    $anoAtual = (int)date('Y');

    $sql = "SELECT DISTINCT ano FROM (
                SELECT DISTINCT YEAR(data_transacao) AS ano 
                FROM transacoes 
                WHERE id_usuario = :id_usuario1
                
                UNION
                
                SELECT DISTINCT YEAR(data_transacao_inicio) AS ano 
                FROM transacoes_recorrentes 
                WHERE id_usuario = :id_usuario2
            ) AS anos_combinados 
            WHERE ano IS NOT NULL
            ORDER BY ano DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario1' => $idUsuario,
        ':id_usuario2' => $idUsuario
    ]);
    
    $anos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($anos)) {
        return [$anoAtual];
    }
    
    return array_map('intval', $anos);
}

function getMesesDisponiveisFiltro($pdo, $idUsuario) {
    $sql = "SELECT DISTINCT mes FROM (
                SELECT DISTINCT MONTH(data_transacao) AS mes FROM transacoes WHERE id_usuario = :id_usuario1
                UNION
                SELECT DISTINCT MONTH(data_transacao_inicio) AS mes FROM transacoes_recorrentes WHERE id_usuario = :id_usuario2
            ) AS meses_reais 
            WHERE mes IS NOT NULL
            ORDER BY mes ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario1' => $idUsuario,
        ':id_usuario2' => $idUsuario
    ]);
    
    $mesesLogados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($mesesLogados)) {
        return [(int)date('n')];
    }

    return array_map('intval', $mesesLogados);
}

function handleDBException(PDOException $e, string $userMessage = "Ocorreu um erro no sistema. Tente novamente mais tarde.") {
    error_log("Trace: " . $e->getTraceAsString());
    throw new Exception($userMessage);
}

function getRecurringByUserId($pdo, $idUsuario) {
    $sql = "SELECT * FROM transacoes_recorrentes WHERE id_usuario = :idUsuario";
    $sql .= " ORDER BY dia_transacao DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecurringByUserIdAndParams($pdo, $idUsuario, $dataInicial, $dataFinal, $tipo, $descricao,
 $categoria, $operadorValor, $valor, $diaTransacao, $data_inicio_transacao, $data_termino_transacao) {
    
    $sql = "SELECT * FROM transacoes_recorrentes WHERE id_usuario = :idUsuario";

    $conditions = [];
    $params = [':idUsuario' => $idUsuario];

    if (!empty($dataInicial) && !empty($dataFinal)) {
        $conditions[] = "data_transacao_inicio <= :data_final 
                         AND (data_transacao_termino IS NULL 
                              OR DATE_ADD(data_transacao_termino, INTERVAL 1 DAY) >= :data_inicial)";
        $params[':data_inicial'] = $dataInicial;
        $params[':data_final'] = $dataFinal;
    }

    if (!empty($tipo)) {
        $conditions[] = "tipo = :tipo";
        $params[':tipo'] = $tipo;
    }

    if (!empty($descricao)) {
        $conditions[] = "descricao LIKE :descricao";
        $params[':descricao'] = "%$descricao%";
    }

    if (!empty($categoria)) {
        $conditions[] = "categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    if (!empty($valor) && !empty($operadorValor)) {
        if ($operadorValor === 'igual_a') {
            $conditions[] = "valor = :valor";
        } elseif ($operadorValor === 'maior_que') {
            $conditions[] = "valor > :valor";
        } elseif ($operadorValor === 'menor_que') {
            $conditions[] = "valor < :valor";
        }
        $params[':valor'] = $valor;
    }

    if (isset($diaTransacao) && is_numeric($diaTransacao) && $diaTransacao > 0) {
        $conditions[] = "dia_transacao = :dia";
        $params[':dia'] = (int)$diaTransacao;
    }

    if (!empty($data_inicio_transacao)) {
        $conditions[] = "data_transacao_inicio = :data_inicio";
        $params[':data_inicio'] = $data_inicio_transacao;
    }

    if (!empty($data_termino_transacao)) {
        $conditions[] = "data_transacao_termino = :data_termino";
        $params[':data_termino'] = $data_termino_transacao;
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY dia_transacao DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecurringByUserIdAndParamsAndPagination($pdo, $idUsuario, $dataInicial, $dataFinal, $tipo, $descricao,
 $categoria, $operadorValor, $valor, $diaTransacao, $data_inicio_transacao, $data_termino_transacao, $limite, $offset) {
    
    $sql = "SELECT * FROM transacoes_recorrentes WHERE id_usuario = :idUsuario";
    $conditions = [];
    $params = [':idUsuario' => $idUsuario];

    if (!empty($dataInicial) && !empty($dataFinal)) {
        $conditions[] = "data_transacao_inicio <= :data_final 
                         AND (data_transacao_termino IS NULL 
                              OR DATE_ADD(data_transacao_termino, INTERVAL 1 DAY) >= :data_inicial)";
        $params[':data_inicial'] = $dataInicial;
        $params[':data_final'] = $dataFinal;
    }

    if (!empty($tipo)) {
        $conditions[] = "tipo = :tipo";
        $params[':tipo'] = $tipo;
    }

    if (!empty($descricao)) {
        $conditions[] = "descricao LIKE :descricao";
        $params[':descricao'] = "%$descricao%";
    }

    if (!empty($categoria)) {
        $conditions[] = "categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    if (!empty($valor) && !empty($operadorValor)) {
        if ($operadorValor === 'igual_a') {
            $conditions[] = "valor = :valor";
        } elseif ($operadorValor === 'maior_que') {
            $conditions[] = "valor > :valor";
        } elseif ($operadorValor === 'menor_que') {
            $conditions[] = "valor < :valor";
        }
        $params[':valor'] = $valor;
    }

    if (isset($diaTransacao) && is_numeric($diaTransacao) && $diaTransacao > 0) {
        $conditions[] = "dia_transacao = :dia";
        $params[':dia'] = (int)$diaTransacao;
    }

    if (!empty($data_inicio_transacao)) {
        $conditions[] = "data_transacao_inicio = :data_inicio";
        $params[':data_inicio'] = $data_inicio_transacao;
    }

    if (!empty($data_termino_transacao)) {
        $conditions[] = "data_transacao_termino = :data_termino";
        $params[':data_termino'] = $data_termino_transacao;
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY dia_transacao DESC, id DESC";

    $sql .= " LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }

    $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertRecurring($pdo, $idUsuario, $tipo, $descricao, $valor, $categoria, $diaTransacao, $dataInicio, $dataTermino) {
    $sql = "INSERT INTO transacoes_recorrentes (id_usuario, tipo, descricao, valor, categoria, dia_transacao, data_transacao_inicio, data_transacao_termino) 
            VALUES (:id_usuario, :tipo, :descricao, :valor, :categoria, :dia_transacao, :data_inicio, :data_termino)";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue(':tipo', $tipo);
    $stmt->bindValue(':descricao', $descricao);
    $stmt->bindValue(':valor', $valor); 
    $stmt->bindValue(':categoria', $categoria);
    $stmt->bindValue(':dia_transacao', $diaTransacao, PDO::PARAM_INT);
    $stmt->bindValue(':data_inicio', $dataInicio);
    $stmt->bindValue(':data_termino', $dataTermino, $dataTermino === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    
    return $stmt->execute();
}

function deleteRecurringByUserIdAndId($pdo, $idUsuario, $idRecorrente) {
    $sql = "DELETE FROM transacoes_recorrentes WHERE id = :id AND id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':id', $idRecorrente, PDO::PARAM_INT);
    $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
    
    return $stmt->execute();
}

function alterRecurringById($pdo, $id, $idUsuario, $tipo, $descricao, $valor, $categoria, $diaTransacao, $dataInicio, $dataTermino) {
    $sql = "UPDATE transacoes_recorrentes 
            SET tipo = :tipo, descricao = :descricao, valor = :valor, categoria = :categoria, 
                dia_transacao = :dia_transacao, data_transacao_inicio = :data_inicio, data_transacao_termino = :data_termino
            WHERE id = :id AND id_usuario = :id_usuario";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':tipo', $tipo);
    $stmt->bindValue(':descricao', $descricao);
    $stmt->bindValue(':valor', $valor);
    $stmt->bindValue(':categoria', $categoria);
    $stmt->bindValue(':dia_transacao', $diaTransacao, PDO::PARAM_INT);
    $stmt->bindValue(':data_inicio', $dataInicio);
    $stmt->bindValue(':data_termino', $dataTermino, $dataTermino === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
    
    return $stmt->execute();
}

function getRecurringByUserIdAndId($pdo, $idUsuario, $idRecorrente) {
    $sql = "SELECT id, tipo, descricao, valor, categoria, dia_transacao, data_transacao_inicio, data_transacao_termino 
            FROM transacoes_recorrentes 
            WHERE id = :id AND id_usuario = :id_usuario 
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':id', $idRecorrente, PDO::PARAM_INT);
    $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
    
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);

}
