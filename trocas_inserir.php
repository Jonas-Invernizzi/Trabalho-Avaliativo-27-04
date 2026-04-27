<?php
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = $_POST['treinador_id'] ?? null;
    $pokedex_id_desejado = $_POST['pokedex_id_desejado'] ?? null;
    $captura_id_oferecida = $_POST['captura_id_oferecida'] ?? null;

    if ($treinador_id && $pokedex_id_desejado && $captura_id_oferecida) {
        try {
            $sql = "INSERT INTO lista_desejos (treinador_id, pokedex_id_desejado, captura_id_oferecida) 
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

$treinadores = $pdo->query("SELECT id, nome FROM treinadores ORDER BY nome")->fetchAll();
$pokedex = $pdo->query("SELECT id, nome FROM pokedex ORDER BY nome")->fetchAll();

echo $twig->render('trocas_inserir.html', [
    'treinadores' => $treinadores,
    'pokedex' => $pokedex,
    'erro' => $erro
]);