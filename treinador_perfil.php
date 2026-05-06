<?php
session_start();
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
        SELECT c.id, c.apelido, c.nivel, p.nome as especie, p.numero_dex, p.tipo_principal, p.tipo_secundario, p.imagem_url 
        FROM capturas c
        JOIN pokedex p ON c.pokedex_id = p.id
        WHERE c.treinador_id = :tid
        ORDER BY p.numero_dex ASC
    ");
    $stmt->execute([':tid' => $id]);
    $capturas = $stmt->fetchAll();

    // Busca a lista de desejos (espécies que ele marcou como interesse)
    $stmt = $pdo->prepare("
        SELECT p.* FROM lista_desejos ld 
        JOIN pokedex p ON ld.pokedex_id = p.id 
        WHERE ld.treinador_id = :tid
    ");
    $stmt->execute([':tid' => $id]);
    $desejos = $stmt->fetchAll();

    // Busca as ofertas de troca ativas que este treinador criou no mercado
    $stmt = $pdo->prepare("
        SELECT ot.id,
               pd.nome as pokemon_desejado_nome, pd.imagem_url as pokemon_desejado_img,
               co.apelido as pokemon_oferecido_apelido, po.nome as pokemon_oferecido_nome, po.imagem_url as pokemon_oferecido_img
        FROM ofertas_troca ot
        JOIN pokedex pd ON ot.pokedex_id_desejado = pd.id
        JOIN capturas co ON ot.captura_id_oferecida = co.id
        JOIN pokedex po ON co.pokedex_id = po.id
        WHERE ld.treinador_id = :tid
    ");
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