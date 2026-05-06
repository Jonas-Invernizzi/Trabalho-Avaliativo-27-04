<?php
session_start();

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = $_SESSION['treinador_id'];
    $pokedex_id_desejado = $_POST['pokedex_id_desejado'] ?? null;
    $captura_id_oferecida = $_POST['captura_id_oferecida'] ?? null;

    if ($pokedex_id_desejado && $captura_id_oferecida) {
        try {
            $sql = "INSERT INTO ofertas_troca (treinador_id, pokedex_id_desejado, captura_id_oferecida) 
                    VALUES (:tid, :pid, :cid)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tid' => $treinador_id,
                ':pid' => $pokedex_id_desejado,
                ':cid' => $captura_id_oferecida
            ]);
            header("Location: trocas_listar.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao criar oferta: " . $e->getMessage();
        }
    } else {
        $erro = "Selecione todos os campos.";
    }
}

$pokedex = $pdo->query("SELECT id, nome FROM pokedex ORDER BY nome")->fetchAll();

// Busca as capturas do treinador logado para oferecer na troca
$stmt = $pdo->prepare("
    SELECT c.id, p.nome as especie, c.apelido 
    FROM capturas c 
    JOIN pokedex p ON c.pokedex_id = p.id 
    WHERE c.treinador_id = :tid
");
$stmt->execute([':tid' => $_SESSION['treinador_id']]);
$minhas_capturas = $stmt->fetchAll();

echo $twig->render('trocas_inserir.html', [
    'pokedex' => $pokedex,
    'minhas_capturas' => $minhas_capturas,
    'erro' => $erro
]);