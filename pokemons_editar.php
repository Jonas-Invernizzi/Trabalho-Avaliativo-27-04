<?php
session_start();
require('carregar_pdo.php');
require('carregar_twig.php');

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int) $_POST['id'] ?? false;
    $numero_dex = $_POST['numero_dex'] ?? false;
    $nome =  $_POST['nome'] ?? false;
    $tipo_principal = $_POST['tipo_principal'] ?? false;
    $tipo_secundario = $_POST['tipo_secundario'] ?? null;
    $is_shiny = isset($_POST['is_shiny']) ? (int)$_POST['is_shiny'] : 0;

    $pasta = $is_shiny ? 'shiny/' : '';
    $imagem_url = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$pasta}{$numero_dex}.png";

    $sql = 'UPDATE pokedex SET numero_dex = :num, nome = :nome, tipo_principal = :tp, tipo_secundario = :ts, imagem_url = :img, is_shiny = :shiny WHERE id = :id';

    $dados = $pdo->prepare($sql);
    $dados->execute([
        ':id' => $id,
        ':num' => $numero_dex,
        ':nome' => $nome,
        ':tp' => $tipo_principal,
        ':ts' => $tipo_secundario,
        ':img' => $imagem_url,
        ':shiny' => $is_shiny
    ]);

    header('location:Pokemon.php');
    die;
}
$id = (int) $_GET['id'] ?? false;
if (!$id) {
    header('Location: Pokemon.php');
    die;
} else {
    $dado = $pdo->prepare('SELECT * FROM pokedex WHERE id = :id');
    $dado->execute(['id' => $id]);
    $pokemon = $dado->fetch(PDO::FETCH_ASSOC);
}
echo $twig->render('pokedex_editar.html', [
    'pokemon' => $pokemon,
]);