<?php
session_start();
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $treinador_id = $_SESSION['treinador_id'];
    // Busca pokemons e verifica se o treinador logado possui algum exemplar desta espécie
    $stmt = $pdo->prepare("
        SELECT p.*, 
        (SELECT COUNT(*) FROM capturas c WHERE c.pokedex_id = p.id AND c.treinador_id = :tid) as capturado 
        FROM pokedex p 
        ORDER BY p.numero_dex ASC
    ");
    $stmt->execute([':tid' => $treinador_id]);
    $pokemons = $stmt->fetchAll();
} catch (PDOException $e) {
    $pokemons = [];
}

echo $twig->render('pokedex_listar.html', ['pokemons' => $pokemons]);
