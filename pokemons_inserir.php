<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.

require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
$erro = false;
$nome = false;
$numero_dex = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_dex = $_POST['numero_dex'] ?? false;
    $is_shiny = isset($_POST['is_shiny']) ? (int)$_POST['is_shiny'] : 0;
    $nome_pokemon = $_POST['nome'] ?? ''; // Captura o nome do Pokémon do formulário
    $nivel = (int)($_POST['nivel'] ?? 5);
    $quantidade = (int)($_POST['quantidade_disponivel'] ?? 0);
}

if (!$numero_dex) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $erro = "⚠️ Preencha os campos obrigatórios.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && ($nivel < 1 || $nivel > 100)) {
    $erro = "⚠️ O nível deve ser entre 1 e 100.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $quantidade <= 0) {
    $erro = "⚠️ A quantidade disponível para troca deve ser maior que zero.";
} else {
    // Verifica se já existe esse Pokémon (Normal ou Shiny) na Pokédex
    $check = $pdo->prepare("SELECT id FROM pokedex WHERE numero_dex = :num AND is_shiny = :shiny");
    $check->execute([':num' => $numero_dex, ':shiny' => $is_shiny]);
    
    if ($check->fetch()) {
        $erro = "⚠️ Este Pokémon (" . ($is_shiny ? "Shiny" : "Normal") . ") já está registrado na Pokédex.";
    } else {

    try {
        $pdo->beginTransaction();

        // 1. Insere o Pokémon na Pokédex
        $sql = 'INSERT INTO pokedex (numero_dex, is_shiny, nome) VALUES (:num, :shiny, :nome)';
        $dados = $pdo->prepare($sql);
        $dados->execute([
            ':num'  => $numero_dex,
            ':shiny' => $is_shiny,
            ':nome'  => strtolower($nome_pokemon) // Salva o nome em minúsculas para padronização
        ]);

        $pokedex_id = $pdo->lastInsertId();
        $treinador_id = $_SESSION['treinador_id'];

        // 1.5 Registra permanentemente na Pokédex do treinador
        $sql_registro = "INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)";
        $pdo->prepare($sql_registro)->execute([':tid' => $treinador_id, ':pid' => $pokedex_id]);

        // 2. Registra automaticamente a captura para o treinador logado com os dados informados
        $sql_captura = "INSERT INTO capturas (treinador_id, pokedex_id, nivel, quantidade_disponivel) VALUES (:tid, :pid, :nivel, :qtd)";
        $stmt_captura = $pdo->prepare($sql_captura);
        $stmt_captura->execute([':tid' => $treinador_id, ':pid' => $pokedex_id, ':nivel' => $nivel, ':qtd' => $quantidade]);

        $pdo->commit();
        header('location:Pokemon.php');
        die;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = "Erro ao processar cadastro: " . $e->getMessage();
    }
    }
}

echo $twig->render('pokedex_inserir.html', [
    'erro' => $erro,
]);