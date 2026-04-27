<?php
require_once 'carregar_pdo.php';

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