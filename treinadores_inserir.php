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

    if ($nome && $email && $senha) {
        try {
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
            $sql = 'INSERT INTO treinadores (nome, email, senha, cidade) VALUES (:nome, :email, :senha, :cidade)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash, ':cidade' => $cidade]);
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