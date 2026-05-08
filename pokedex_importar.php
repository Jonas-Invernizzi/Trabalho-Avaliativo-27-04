<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

set_time_limit(0); // Impede que o script pare por tempo limite

$totalImportar = 151; // Geração 1. Você pode aumentar conforme necessário.

try {
    for ($i = 1; $i <= $totalImportar; $i++) {
        // Inserir Versão Normal
        $stmtNormal = $pdo->prepare("SELECT id FROM pokedex WHERE numero_dex = :num AND is_shiny = 0");
        $stmtNormal->execute([':num' => $i]);
        
        if (!$stmtNormal->fetch()) {
            // Busca o nome do Pokémon na PokeAPI
            $pokemon_data = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon/{$i}/"), true);
            $pokemon_name = $pokemon_data['name'] ?? 'unknown'; // Garante um nome padrão se a API falhar

            $sql = "INSERT INTO pokedex (numero_dex, is_shiny, nome) VALUES (?, 0, ?)";
            $pdo->prepare($sql)->execute([$i, strtolower($pokemon_name)]); // Salva o nome em minúsculas
        }
        
        // Inserir Versão Shiny
        $stmtShiny = $pdo->prepare("SELECT id FROM pokedex WHERE numero_dex = :num AND is_shiny = 1");
        $stmtShiny->execute([':num' => $i]);

        if (!$stmtShiny->fetch()) {
            // Busca o nome do Pokémon na PokeAPI (deve ser o mesmo da versão normal)
            $pokemon_data = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon/{$i}/"), true);
            $pokemon_name = $pokemon_data['name'] ?? 'unknown'; // Garante um nome padrão se a API falhar

            $sql = "INSERT INTO pokedex (numero_dex, is_shiny, nome) VALUES (?, 1, ?)";
            $pdo->prepare($sql)->execute([$i, strtolower($pokemon_name)]); // Salva o nome em minúsculas
        }
    }

    header("Location: Pokemon.php?sucesso=Pokédex Populada!");
    exit;
} catch (Exception $e) {
    die("Erro durante a importação: " . $e->getMessage());
}