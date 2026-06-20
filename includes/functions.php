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

function getGastosPorCategoria($pdo, $idUsuario) {
    $sql = "SELECT categoria, SUM(valor) as total 
            FROM transacoes 
            WHERE id_usuario = :id_usuario 
              AND tipo = 'Saída'
              AND MONTH(data_transacao) = MONTH(CURRENT_DATE())
              AND YEAR(data_transacao) = YEAR(CURRENT_DATE())
            GROUP BY categoria";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBalancoMensal($pdo, $idUsuario) {
    $sql = "SELECT tipo, SUM(valor) as total 
            FROM transacoes 
            WHERE id_usuario = :id_usuario 
              AND MONTH(data_transacao) = MONTH(CURRENT_DATE())
              AND YEAR(data_transacao) = YEAR(CURRENT_DATE())
            GROUP BY tipo";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getIndicadoresMensais($pdo, $idUsuario) {
    $sql = "SELECT 
                COUNT(CASE WHEN tipo = 'Entrada' THEN 1 END) as qtd_entradas,
                COUNT(CASE WHEN tipo = 'Saída' THEN 1 END) as qtd_saidas,
                COUNT(*) as qtd_totais,
                IFNULL(SUM(CASE WHEN tipo = 'Entrada' THEN valor END), 0) as valor_entradas,
                IFNULL(SUM(CASE WHEN tipo = 'Saída' THEN valor END), 0) as valor_saidas
            FROM transacoes 
            WHERE id_usuario = :id_usuario 
              AND MONTH(data_transacao) = MONTH(CURRENT_DATE())
              AND YEAR(data_transacao) = YEAR(CURRENT_DATE())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEntradasPorCategoria($pdo, $idUsuario) {
    $sql = "SELECT categoria, SUM(valor) as total 
            FROM transacoes 
            WHERE id_usuario = :id_usuario 
              AND tipo = 'Entrada'
              AND MONTH(data_transacao) = MONTH(CURRENT_DATE())
              AND YEAR(data_transacao) = YEAR(CURRENT_DATE())
            GROUP BY categoria";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHistoricoAnual($pdo, $idUsuario) {
    $sql = "SELECT 
                MONTH(data_transacao) as mes,
                IFNULL(SUM(CASE WHEN tipo = 'Entrada' THEN valor END), 0) as entradas,
                IFNULL(SUM(CASE WHEN tipo = 'Saída' THEN valor END), 0) as saidas
            FROM transacoes 
            WHERE id_usuario = :id_usuario 
              AND YEAR(data_transacao) = YEAR(CURRENT_DATE())
            GROUP BY MONTH(data_transacao)
            ORDER BY MONTH(data_transacao) ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function handleDBException(PDOException $e, string $userMessage = "Ocorreu um erro no sistema. Tente novamente mais tarde.") {
    error_log("Trace: " . $e->getTraceAsString());
    throw new Exception($userMessage);
}

?>