<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

// Verifica se o usuário está logado antes de permitir a alteração
if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Busca dados atuais para saber o Dex e inverter o Shiny
    $stmt = $pdo->prepare("SELECT numero_dex, is_shiny FROM pokedex WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $pokemon = $stmt->fetch();

    if ($pokemon) {
        $novo_shiny = 1 - $pokemon['is_shiny'];
        $stmt = $pdo->prepare("UPDATE pokedex SET is_shiny = :shiny WHERE id = :id");
        $stmt->execute([
            ':shiny' => $novo_shiny,
            ':id'    => $id
        ]);
    }
}

header("Location: Pokemon.php");
exit;