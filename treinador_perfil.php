<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: treinadores_listar.php");
    exit;
}

try {
    // Busca os dados básicos do treinador
    $stmt = $pdo->prepare("SELECT id, nome, email, cidade, foto_perfil FROM treinadores WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $treinador = $stmt->fetch();

    if (!$treinador) {
        header("Location: treinadores_listar.php");
        exit;
    }

    // Busca os Pokémon capturados por este treinador
    $stmt = $pdo->prepare("
        SELECT c.id, c.pokedex_id, c.nivel, c.quantidade_disponivel, p.numero_dex, p.is_shiny, p.nome
        FROM capturas c 
        JOIN pokedex p ON c.pokedex_id = p.id
        WHERE c.treinador_id = :tid
        ORDER BY p.numero_dex ASC, p.is_shiny ASC
    ");
    $stmt->execute([':tid' => $id]);
    $capturas = $stmt->fetchAll();

    // Busca a lista de desejos (espécies que ele marcou como interesse)
    $stmt = $pdo->prepare("
        SELECT p.*, p.nome FROM lista_desejos ld 
        JOIN pokedex p ON ld.pokedex_id = p.id 
        WHERE ld.treinador_id = :tid
        ORDER BY p.numero_dex ASC, p.is_shiny ASC
    ");
    $stmt->execute([':tid' => $id]);
    $desejos = $stmt->fetchAll();

    // Busca as ofertas de troca que este treinador criou
    $stmt = $pdo->prepare("
        SELECT ot.id,
               pd.numero_dex AS pokemon_desejado_dex, pd.is_shiny AS pokemon_desejado_shiny, pd.nome AS pokemon_desejado_nome,
               po.numero_dex AS pokemon_oferecido_dex, po.is_shiny AS pokemon_oferecido_shiny, po.nome AS pokemon_oferecido_nome
        FROM ofertas_troca ot
        LEFT JOIN pokedex pd ON ot.pokedex_id_desejado = pd.id
        LEFT JOIN capturas co ON ot.captura_id_oferecida = co.id
        LEFT JOIN pokedex po ON co.pokedex_id = po.id
        WHERE ot.treinador_id = :tid
    ");
    // Removi o filtro restrito de quantidade para que você possa ao menos ver a oferta e depurar se o Pokémon existe
    $stmt->execute([':tid' => $id]);
    $ofertas = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar perfil: " . $e->getMessage());
}

echo $twig->render('treinador_perfil.html', [
    'treinador_perfil' => $treinador,
    'capturas_treinador' => $capturas,
    'desejos_treinador' => $desejos,
    'ofertas_troca' => $ofertas,
    'is_own_profile' => ($id == $_SESSION['treinador_id'])
]);