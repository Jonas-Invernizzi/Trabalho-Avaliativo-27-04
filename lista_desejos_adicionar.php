<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$pokedex_id = (int)($_GET['pokedex_id'] ?? 0);
$treinador_id = $_SESSION['treinador_id'];

if ($pokedex_id > 0) {
    // INSERT IGNORE evita duplicados se o usuário clicar várias vezes
    $stmt = $pdo->prepare("INSERT IGNORE INTO lista_desejos (treinador_id, pokedex_id) VALUES (:tid, :pid)");
    $stmt->execute([':tid' => $treinador_id, ':pid' => $pokedex_id]);
}

// Redireciona de volta para a Pokédex com uma mensagem de sucesso
header("Location: Pokemon.php?sucesso=Adicionado à lista de desejos!");
exit;