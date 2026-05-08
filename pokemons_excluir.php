<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require('carregar_pdo.php');
require('carregar_twig.php');

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST["id"] ?? false;
    if ($id) {
        $excluir = $pdo->prepare("DELETE FROM pokedex WHERE id = :id");
        $excluir->bindParam(":id", $id);
        $excluir->execute();
    }
    header("location:Pokemon.php");
    die;
}
$id = (int) $_GET["id"] ?? false;

if (!$id) {
    header('location:Pokemon.php');
    die;
}

$dados = $pdo->prepare('SELECT * FROM pokedex WHERE id = :id');
$dados->execute([':id' => $id]);

if ($dados->rowCount() != 1) {
    header('location:Pokemon.php');
    die;
};

$pokemon = $dados->fetch(PDO::FETCH_ASSOC);

echo $twig->render('pokedex_excluir.html', [
    'pokemon' => $pokemon,
]);