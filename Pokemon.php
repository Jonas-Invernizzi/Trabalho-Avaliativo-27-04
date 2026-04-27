<?php
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

try {
    // Alinhado com a tabela 'pokedex' do SQL
    $stmt = $pdo->query("SELECT * FROM pokedex ORDER BY numero_dex ASC");
    $pokemons = $stmt->fetchAll();
} catch (PDOException $e) {
    $pokemons = [];
}

echo $twig->render('pokedex_listar.html', ['pokemons' => $pokemons]);
