<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require('carregar_pdo.php');
require('carregar_twig.php');

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int) $_POST['id'] ?? false;
    $numero_dex = $_POST['numero_dex'] ?? false;
    $is_shiny = isset($_POST['is_shiny']) ? (int)$_POST['is_shiny'] : 0;

    $sql = 'UPDATE pokedex SET numero_dex = :num, is_shiny = :shiny WHERE id = :id';

    $dados = $pdo->prepare($sql);
    $dados->execute([
        ':id' => $id,
        ':num' => $numero_dex,
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

    // Busca a quantidade total de exemplares que o treinador possui desta espécie
    $stmt_count = $pdo->prepare("SELECT SUM(quantidade_disponivel) FROM capturas WHERE pokedex_id = :pid AND treinador_id = :tid");
    $stmt_count->execute([':pid' => $id, ':tid' => $_SESSION['treinador_id']]);
    $quantidade_total = (int)$stmt_count->fetchColumn();
}
echo $twig->render('pokedex_editar.html', [
    'pokemon' => $pokemon,
    'quantidade_total' => $quantidade_total
]);