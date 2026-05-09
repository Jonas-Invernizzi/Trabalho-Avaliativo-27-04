<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
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

$stmt = $pdo->prepare("SELECT * FROM pokedex WHERE id = :id");
$stmt->execute([':id' => $pokedex_id]);
$pokemon = $stmt->fetch();

if (!$pokemon) {
    header("Location: Pokemon.php");
    exit;
}

$erro = false;
$quantidade = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = $_SESSION['treinador_id'];
    $quantidade = (int)($_POST['quantidade_disponivel'] ?? $quantidade);

    if ($quantidade > 0) {
        try {
            $pdo->beginTransaction();
            $sql_reg = "INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)";
            $pdo->prepare($sql_reg)->execute([':tid' => $treinador_id, ':pid' => $pokedex_id]);

            $sql = "INSERT INTO capturas (treinador_id, pokedex_id, quantidade_disponivel) VALUES (:tid, :pid, :qtd)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':tid' => $treinador_id, ':pid' => $pokedex_id, ':qtd' => $quantidade]);
            $pdo->commit();
            header("Location: Pokemon.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao registrar captura: " . $e->getMessage();
        }
    } else {
        $erro = "A quantidade disponível para troca deve ser maior que zero.";
    }
}

echo $twig->render('capturas_inserir.html', [
    'pokemon' => $pokemon,
    'erro' => $erro,
    'quantidade_disponivel' => $quantidade
]);
