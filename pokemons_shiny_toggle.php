<?php
session_start();
require_once 'carregar_pdo.php';

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
        $pasta = $novo_shiny ? 'shiny/' : '';
        $nova_url = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$pasta}{$pokemon['numero_dex']}.png";

        $stmt = $pdo->prepare("UPDATE pokedex SET is_shiny = :shiny, imagem_url = :url WHERE id = :id");
        $stmt->execute([
            ':shiny' => $novo_shiny,
            ':url'   => $nova_url,
            ':id'    => $id
        ]);
    }
}

header("Location: Pokemon.php");
exit;