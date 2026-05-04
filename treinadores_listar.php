<?php
session_start();

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

try {
    $stmt = $pdo->query("SELECT * FROM treinadores ORDER BY nome ASC");
    $treinadores = $stmt->fetchAll();
} catch (PDOException $e) {
    $treinadores = [];
}

echo $twig->render('treinadores_listar.html', [
    'treinadores' => $treinadores
]);