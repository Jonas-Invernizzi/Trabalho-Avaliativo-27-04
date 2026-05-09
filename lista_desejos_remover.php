<?php
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

// Proteção: apenas treinadores logados
if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$pokedex_id = (int)($_GET['pokedex_id'] ?? 0);
$treinador_id = $_SESSION['treinador_id'];

if ($pokedex_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM lista_desejos WHERE treinador_id = :tid AND pokedex_id = :pid");
        $stmt->execute([':tid' => $treinador_id, ':pid' => $pokedex_id]);
    } catch (PDOException $e) {
        // Em caso de erro, apenas segue o fluxo de redirecionamento
    }
}

header("Location: treinador_perfil.php?id=" . $treinador_id);
exit;
