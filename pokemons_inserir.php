<?php
$erro = false;
$nome = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_dex = $_POST['numero_dex'] ?? false;
    $nome = $_POST['nome'] ?? false;
    $tipo_principal = $_POST['tipo_principal'] ?? false;
    $tipo_secundario = $_POST['tipo_secundario'] ?? null;
    $imagem_url_api = $_POST['imagem_url_api'] ?? null;
}

if ((!$nome || !$numero_dex || !$tipo_principal)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $erro = "⚠️ Preencha os campos obrigatórios.";
    }
} else {
    $imagem_url = $imagem_url_api; // Padrão: usa a URL da API

    // Se o usuário subiu uma foto manual, ela tem prioridade
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES["capa"]["name"], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid().'.'.$ext; 
        if (move_uploaded_file($_FILES['capa']['tmp_name'], "img/{$nome_arquivo}")) {
            $imagem_url = "img/{$nome_arquivo}";
        }
    }

    require("carregar_pdo.php");
    $sql = 'INSERT INTO pokedex (numero_dex, nome, tipo_principal, tipo_secundario, imagem_url) VALUES (:num, :nome, :tp, :ts, :img)';
    $dados = $pdo->prepare($sql);
    $dados->execute([
        ':num'  => $numero_dex,
        ':nome' => $nome,
        ':tp'   => $tipo_principal,
        ':ts'   => $tipo_secundario,
        ':img'  => $imagem_url
    ]);

    header('location:Pokemon.php');
    die;
}

require('carregar_twig.php');
echo $twig->render('pokedex_inserir.html', [
    'erro' => $erro,
]);