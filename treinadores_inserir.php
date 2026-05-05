<?php
session_start();
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $cidade = $_POST['cidade'] ?? 'Pallet Town';
    $fotoPerfil = 'img/default-avatar.png';

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid('perfil_') . '.' . $extensao;
        $diretorio = 'img/perfis/';
        if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $diretorio . $nomeArquivo)) {
            $fotoPerfil = $diretorio . $nomeArquivo;
        }
    }

    if ($nome && $email && $senha) {
        try {
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
            $sql = 'INSERT INTO treinadores (nome, email, senha, cidade, foto_perfil) VALUES (:nome, :email, :senha, :cidade, :foto_perfil)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash, ':cidade' => $cidade, ':foto_perfil' => $fotoPerfil]);
            header('Location: treinadores_listar.php');
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha os campos obrigatórios.";
    }
}

echo $twig->render('treinadores_inserir.html', ['erro' => $erro]);
