<?php
session_start();
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

try {
    // Busca o total da tabela 'pokedex' conforme o SQL
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pokedex");
    $result = $stmt->fetch();
    $totalPokemons = $result['total'] ?? 0;
} catch (PDOException $e) {
    $totalPokemons = 0;
}

echo $twig->render('index.html', [
    'totalPokemons' => $totalPokemons
]);
