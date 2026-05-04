<?php
session_start();
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$pokedex_id = (int)($_GET['pokedex_id'] ?? 0);
if (!$pokedex_id) {
    header("Location: Pokemon.php");
    exit;
}

// Busca dados do Pokémon para exibir no formulário de captura
$stmt = $pdo->prepare("SELECT * FROM pokedex WHERE id = :id");
$stmt->execute([':id' => $pokedex_id]);
$pokemon = $stmt->fetch();

if (!$pokemon) {
    header("Location: Pokemon.php");
    exit;
}

$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = $_SESSION['treinador_id'];
    $apelido = $_POST['apelido'] ?? '';
    $nivel = (int)($_POST['nivel'] ?? 1);

    if ($nivel >= 1 && $nivel <= 100) {
        try {
            $sql = "INSERT INTO capturas (treinador_id, pokedex_id, apelido, nivel) VALUES (:tid, :pid, :apelido, :nivel)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':tid' => $treinador_id, ':pid' => $pokedex_id, ':apelido' => $apelido, ':nivel' => $nivel]);
            header("Location: Pokemon.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao registrar captura: " . $e->getMessage();
        }
    } else {
        $erro = "O nível deve ser entre 1 e 100.";
    }
}

echo $twig->render('capturas_inserir.html', [
    'pokemon' => $pokemon,
    'erro' => $erro
]);