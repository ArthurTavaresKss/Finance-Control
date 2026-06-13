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
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function insertUser($pdo, $username, $email, $senha) {
    $sql = "INSERT INTO usuarios (username, email, senha) VALUES
    (:username, :email, :senha)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->execute();
}

?>