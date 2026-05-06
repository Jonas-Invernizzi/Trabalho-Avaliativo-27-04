<?php
session_start();

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
$erro = false;
$nome = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_dex = $_POST['numero_dex'] ?? false;
    $nome = $_POST['nome'] ?? false;
    $tipo_principal = $_POST['tipo_principal'] ?? false;
    $tipo_secundario = $_POST['tipo_secundario'] ?? null;
    $is_shiny = isset($_POST['is_shiny']) ? (int)$_POST['is_shiny'] : 0;
    $apelido = $_POST['apelido'] ?? '';
    $nivel = (int)($_POST['nivel'] ?? 5);
}

if ((!$nome || !$numero_dex || !$tipo_principal)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $erro = "⚠️ Preencha os campos obrigatórios.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && ($nivel < 1 || $nivel > 100)) {
    $erro = "⚠️ O nível deve ser entre 1 e 100.";
} else {
    // Gera a URL da PokeAPI automaticamente com base no número da Dex
    $pasta = $is_shiny ? 'shiny/' : '';
    $imagem_url = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$pasta}{$numero_dex}.png";

    require("carregar_pdo.php");
    try {
        $pdo->beginTransaction();

        // 1. Insere o Pokémon na Pokédex
        $sql = 'INSERT INTO pokedex (numero_dex, nome, tipo_principal, tipo_secundario, imagem_url, is_shiny) VALUES (:num, :nome, :tp, :ts, :img, :shiny)';
        $dados = $pdo->prepare($sql);
        $dados->execute([
            ':num'  => $numero_dex,
            ':nome' => $nome,
            ':tp'   => $tipo_principal,
            ':ts'   => $tipo_secundario,
            ':img'  => $imagem_url,
            ':shiny' => $is_shiny
        ]);

        $pokedex_id = $pdo->lastInsertId();
        $treinador_id = $_SESSION['treinador_id'];

        // Se o apelido não for informado, usa o nome da espécie como padrão
        if (empty($apelido)) {
            $apelido = $nome;
        }

        // 2. Registra automaticamente a captura para o treinador logado com os dados informados
        $sql_captura = "INSERT INTO capturas (treinador_id, pokedex_id, apelido, nivel) VALUES (:tid, :pid, :apelido, :nivel)";
        $stmt_captura = $pdo->prepare($sql_captura);
        $stmt_captura->execute([':tid' => $treinador_id, ':pid' => $pokedex_id, ':apelido' => $apelido, ':nivel' => $nivel]);

        $pdo->commit();
        header('location:Pokemon.php');
        die;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = "Erro ao processar cadastro: " . $e->getMessage();
    }
}

require('carregar_twig.php');
echo $twig->render('pokedex_inserir.html', [
    'erro' => $erro,
]);