<?php
require('carregar_pdo.php');
require('carregar_twig.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int) $_POST['id'] ?? false;
    $numero_dex = $_POST['numero_dex'] ?? false;
    $nome =  $_POST['nome'] ?? false;
    $tipo_principal = $_POST['tipo_principal'] ?? false;
    $tipo_secundario = $_POST['tipo_secundario'] ?? null;

    if (!$_FILES['capa']['error']) {
        $dados = $pdo->prepare('SELECT imagem_url FROM pokedex WHERE id = :id');
        $dados->execute([':id' => $id]);
        $capa_velha = $dados->fetch(PDO::FETCH_ASSOC)['imagem_url'];

        $caminhoVelho = __DIR__ . '/img/' . $capa_velha;
        if ($capa_velha && !filter_var($capa_velha, FILTER_VALIDATE_URL) && file_exists($caminhoVelho)) {
            unlink($caminhoVelho);
        }

        $ext = pathinfo($_FILES["capa"]["name"], PATHINFO_EXTENSION);
        $capa = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['capa']['tmp_name'], "img/{$capa}");
    }
    
    $sql = 'UPDATE pokedex SET numero_dex = :num, nome = :nome, tipo_principal = :tp, tipo_secundario = :ts' . (isset($capa) ? ', imagem_url = :img' : '') . ' WHERE id = :id';

    $dados = $pdo->prepare($sql);
    $params = [
        ':id' => $id,
        ':num' => $numero_dex,
        ':nome' => $nome,
        ':tp' => $tipo_principal,
        ':ts' => $tipo_secundario,
    ];
    if (isset($capa)) {
        $params[':img'] = $capa;
    }
    $dados->execute($params);

    header('location:Pokemon.php');
    die;
}
$id = (int) $_GET['id'] ?? false;
if (!$id) {
    header('Location: Pokemon.php');
    die;
} else {
    $dado = $pdo->prepare('SELECT * FROM pokedex WHERE id = :id');
    $dado->execute(['id' => $id]);
    $pokemon = $dado->fetch(PDO::FETCH_ASSOC);
}
echo $twig->render('pokedex_editar.html', [
    'pokemon' => $pokemon,
]);