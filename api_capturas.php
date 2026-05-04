<?php
session_start();
require_once 'carregar_pdo.php';

// Basic check: only allow logged-in users to access their own captures
if (!isset($_SESSION['treinador_id']) || $_SESSION['treinador_id'] != ($_GET['treinador_id'] ?? 0)) {
    http_response_code(403); // Forbidden
    die(json_encode(['error' => 'Acesso não autorizado.']));
}
$tid = (int)($_GET['treinador_id'] ?? 0);

if ($tid > 0) {
    $sql = "SELECT c.id, c.apelido, c.nivel, p.nome as especie 
            FROM capturas c 
            JOIN pokedex p ON c.pokedex_id = p.id 
            WHERE c.treinador_id = :tid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tid' => $tid]);
    echo json_encode($stmt->fetchAll());
} else {
    echo json_encode([]);
}