<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.

require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM treinadores ORDER BY nome ASC");
    $treinadores = $stmt->fetchAll();
} catch (PDOException $e) {
    $treinadores = [];
}

echo $twig->render('treinadores_listar.html', [
    'treinadores' => $treinadores
]);