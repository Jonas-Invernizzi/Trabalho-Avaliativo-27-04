<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $treinador_id = $_SESSION['treinador_id'];
    $search = $_GET['search'] ?? '';

    $sql = "
        SELECT p.*, 
        COALESCE((SELECT SUM(quantidade_disponivel) FROM capturas c WHERE c.pokedex_id = p.id AND c.treinador_id = :tid), 0) as capturado,
        EXISTS(SELECT 1 FROM treinador_pokedex tp WHERE tp.pokedex_id = p.id AND tp.treinador_id = :tid) as visto
        FROM pokedex p
    ";

    $params = [':tid' => $treinador_id];

    if (!empty($search)) {
        // Adiciona busca por número da Dex OU nome do Pokémon (case-insensitive)
        $sql .= " WHERE p.numero_dex = :search_num OR p.nome LIKE :search_name";
        $params[':search_name'] = "%" . strtolower($search) . "%"; // Converte a busca para minúsculas
        $params[':search_num'] = is_numeric($search) ? (int)$search : -1;
    }

    $sql .= " ORDER BY p.numero_dex ASC, p.is_shiny ASC";

    // Busca pokemons e verifica se o treinador logado possui algum exemplar desta espécie
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pokemons = $stmt->fetchAll();
} catch (PDOException $e) {
    $pokemons = [];
}

echo $twig->render('pokedex_listar.html', ['pokemons' => $pokemons, 'search' => $search]);
