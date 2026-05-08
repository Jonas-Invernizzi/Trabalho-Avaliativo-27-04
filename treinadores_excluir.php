<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM treinadores WHERE id = :id');
    $stmt->execute([':id' => $id]);
    header('Location: treinadores_listar.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM treinadores WHERE id = :id');
$stmt->execute([':id' => $id]);
$treinador = $stmt->fetch();

echo $twig->render('treinadores_excluir.html', ['treinador' => $treinador]);