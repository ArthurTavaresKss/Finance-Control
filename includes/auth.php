<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();

define('TEMPO_LIMITE_INATIVIDADE', 60 * 60); // 1 hora
 
if (!isset($_SESSION['idUsuario'])) {
    redirect("/Finance-Control/public/login.php");
    exit;
}

if (isset($_SESSION['ultimo_acesso'])) {
    $tempoInativo = time() - $_SESSION['ultimo_acesso'];
 
    if ($tempoInativo > TEMPO_LIMITE_INATIVIDADE) {
        session_unset();
        session_destroy();
        redirect("login?status=sessao_expirada");
        exit;
    }
}

$_SESSION['ultimo_acesso'] = time();
?>
 