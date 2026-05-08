<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = $_SESSION['treinador_id'];
    $pokedex_id_desejado = $_POST['pokedex_id_desejado'] ?? null;
    $captura_id_oferecida = $_POST['captura_id_oferecida'] ?? null;

    if ($pokedex_id_desejado && $captura_id_oferecida) {
        try {
            $sql = "INSERT INTO ofertas_troca (treinador_id, pokedex_id_desejado, captura_id_oferecida) 
                    VALUES (:tid, :pid, :cid)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tid' => $treinador_id,
                ':pid' => $pokedex_id_desejado,
                ':cid' => $captura_id_oferecida
            ]);
            header("Location: trocas_listar.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao criar oferta: " . $e->getMessage();
        }
    } else {
        $erro = "Selecione todos os campos.";
    }
}

    // --- Lógica para Processar Wishlist (Parâmetros via GET) ---
    $wish_pokedex_id = (int)($_GET['wish_pokedex_id'] ?? 0);
    $desired_pokedex_id = (int)($_GET['desired_pokedex_id'] ?? 0);
    $wish_treinador_id = (int)($_GET['wish_treinador_id'] ?? 0);

    $outro_treinador = null;
    $pokemons_outro_treinador = [];
    $pre_selected_oferecida = $wish_pokedex_id; // Sugere oferecer o que o outro deseja
    $pre_selected_desejada = $desired_pokedex_id; // Sugere o que você quer do outro

    if ($wish_treinador_id > 0) {
        // Busca informações do treinador dono da wishlist
        $stmt = $pdo->prepare("SELECT id, nome FROM treinadores WHERE id = :id");
        $stmt->execute([':id' => $wish_treinador_id]);
        $outro_treinador = $stmt->fetch();

        if ($outro_treinador) {
            // Filtra apenas os Pokémon que este treinador específico tem marcados como disponíveis para troca
            // Usamos DISTINCT pois o formulário de oferta pede a espécie (pokedex_id)
            $stmt = $pdo->prepare(" 
                SELECT DISTINCT p.id as pokedex_id, p.numero_dex, p.is_shiny
                FROM capturas c 
                JOIN pokedex p ON c.pokedex_id = p.id 
                WHERE c.treinador_id = :tid AND c.quantidade_disponivel > 0
                ORDER BY p.numero_dex ASC, p.is_shiny ASC
            ");
            $stmt->execute([':tid' => $wish_treinador_id]);
            $pokemons_outro_treinador = $stmt->fetchAll();
        }
    }

$pokedex = $pdo->query("SELECT id, numero_dex, is_shiny FROM pokedex ORDER BY numero_dex ASC, is_shiny ASC")->fetchAll(); 

// Busca as capturas do treinador logado para oferecer na troca
    // Adicionamos c.pokedex_id para permitir a pré-seleção automática no HTML
$stmt = $pdo->prepare("
        SELECT c.id, c.pokedex_id, p.numero_dex, p.is_shiny 
    FROM capturas c 
    JOIN pokedex p ON c.pokedex_id = p.id 
    WHERE c.treinador_id = :tid AND c.quantidade_disponivel > 0
    ORDER BY p.numero_dex ASC, p.is_shiny ASC
");
$stmt->execute([':tid' => $_SESSION['treinador_id']]);
$minhas_capturas = $stmt->fetchAll();

echo $twig->render('trocas_inserir.html', [
    'pokedex' => $pokedex,
    'minhas_capturas' => $minhas_capturas,
        'erro' => $erro,
        'outro_treinador' => $outro_treinador,
        'pokemons_outro_treinador' => $pokemons_outro_treinador,
        'pre_selected_oferecida' => $pre_selected_oferecida,
        'pre_selected_desejada' => $pre_selected_desejada
]);