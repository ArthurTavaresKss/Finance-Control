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

function insertUser($pdo, $username, $email, $senha) {
    $sql = "INSERT INTO usuarios (username, email, senha) VALUES
    (:username, :email, :senha)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    return $stmt->execute();
}

function getTransactionsByUserIdAndDate($pdo, $idUsuario, $dataInicial, $dataFinal) {
    $sql = "SELECT * FROM transacoes WHERE id_usuario = :idUsuario AND
    data_transacao BETWEEN :dataInicial AND :dataFinal";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->bindParam(':dataInicial', $dataInicial);
    $stmt->bindParam(':dataFinal', $dataFinal);
    $stmt->execute();
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

function AlterTransactionById($pdo, $id, $idUsuario, $tipo, $descricao, $valor, $categoria, $data_transacao) {
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

function deleteTransactionByUserIdAndId($pdo, $idUsuario, $idTransacao) {
    $sql = "DELETE FROM transacoes WHERE id_usuario = :idUsuario AND id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->bindParam(':id', $idTransacao);
    return $stmt->execute();
}

?>