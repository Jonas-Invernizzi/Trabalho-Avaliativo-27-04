<?php
session_start();
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cidade = $_POST['cidade'];

    $sql = 'UPDATE treinadores SET nome = :nome, email = :email, cidade = :cidade WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nome' => $nome, ':email' => $email, ':cidade' => $cidade, ':id' => $id]);

    header('Location: treinadores_listar.php');
    exit;
}

$id = (int)$_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM treinadores WHERE id = :id');
$stmt->execute([':id' => $id]);
$treinador = $stmt->fetch();

if (!$treinador) {
    header('Location: treinadores_listar.php');
    exit;
}

echo $twig->render('treinadores_editar.html', ['treinador' => $treinador]);