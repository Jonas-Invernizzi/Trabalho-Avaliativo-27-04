<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

// Proteção: apenas treinadores logados podem inserir novos pokémons
if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $treinador_id = $_SESSION['treinador_id'];
    $numero_dex = (int)($_POST['numero_dex'] ?? 0);
    $nome = $_POST['nome'] ?? '';
    $is_shiny = (int)($_POST['is_shiny'] ?? 0);

    if ($numero_dex > 0 && !empty($nome)) {
        try {
            $pdo->beginTransaction();

            // 1. Verifica se o Pokémon já existe na Pokédex global para evitar duplicatas
            $stmt = $pdo->prepare("SELECT id FROM pokedex WHERE numero_dex = :num AND is_shiny = :shiny");
            $stmt->execute([':num' => $numero_dex, ':shiny' => $is_shiny]);
            $existente = $stmt->fetch();

            if ($existente) {
                $pokedex_id = $existente['id'];
            } else {
                // Insere na tabela global da Pokedex se não existir
                $stmt = $pdo->prepare("INSERT INTO pokedex (numero_dex, nome, is_shiny) VALUES (:num, :nome, :shiny)");
                $stmt->execute([':num' => $numero_dex, ':nome' => $nome, ':shiny' => $is_shiny]);
                $pokedex_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)");
            $stmt->execute([':tid' => $treinador_id, ':pid' => $pokedex_id]);

            $pdo->commit();
            header("Location: Pokemon.php"); // Redireciona para a listagem
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = "Erro ao salvar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha o número da Dex para carregar os dados.";
    }
}

echo $twig->render('pokedex_inserir.html', ['erro' => $erro]);
