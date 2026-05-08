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
    $nivel = (int)($_POST['nivel'] ?? 1);
    $quantidade = (int)($_POST['quantidade_disponivel'] ?? 0);

    if ($nivel >= 1 && $nivel <= 100 && $quantidade > 0) {
        try {
            $pdo->beginTransaction();
            // Registro permanente na Pokédex
            $sql_reg = "INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)";
            $pdo->prepare($sql_reg)->execute([':tid' => $treinador_id, ':pid' => $pokedex_id]);

            $sql = "INSERT INTO capturas (treinador_id, pokedex_id, nivel, quantidade_disponivel) VALUES (:tid, :pid, :nivel, :qtd)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':tid' => $treinador_id, ':pid' => $pokedex_id, ':nivel' => $nivel, ':qtd' => $quantidade]);
            $pdo->commit();
            header("Location: Pokemon.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao registrar captura: " . $e->getMessage();
        }
    } elseif ($quantidade <= 0) {
        $erro = "A quantidade disponível para troca deve ser maior que zero.";
    } else {
        $erro = "O nível deve ser entre 1 e 100.";
    }
}

echo $twig->render('capturas_inserir.html', [
    'pokemon' => $pokemon,
    'erro' => $erro
]);