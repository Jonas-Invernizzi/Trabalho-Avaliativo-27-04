<?php
session_start();

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
$erro = false;
$nome = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_dex = $_POST['numero_dex'] ?? false;
    $nome = $_POST['nome'] ?? false;
    $tipo_principal = $_POST['tipo_principal'] ?? false;
    $tipo_secundario = $_POST['tipo_secundario'] ?? null;
    $is_shiny = isset($_POST['is_shiny']) ? (int)$_POST['is_shiny'] : 0;
}

if ((!$nome || !$numero_dex || !$tipo_principal)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $erro = "⚠️ Preencha os campos obrigatórios.";
    }
} else {
    // Gera a URL da PokeAPI automaticamente com base no número da Dex
    $pasta = $is_shiny ? 'shiny/' : '';
    $imagem_url = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$pasta}{$numero_dex}.png";

    require("carregar_pdo.php");
    $sql = 'INSERT INTO pokedex (numero_dex, nome, tipo_principal, tipo_secundario, imagem_url, is_shiny) VALUES (:num, :nome, :tp, :ts, :img, :shiny)';
    $dados = $pdo->prepare($sql);
    $dados->execute([
        ':num'  => $numero_dex,
        ':nome' => $nome,
        ':tp'   => $tipo_principal,
        ':ts'   => $tipo_secundario,
        ':img'  => $imagem_url,
        ':shiny' => $is_shiny
    ]);

    header('location:Pokemon.php');
    die;
}

require('carregar_twig.php');
echo $twig->render('pokedex_inserir.html', [
    'erro' => $erro,
]);