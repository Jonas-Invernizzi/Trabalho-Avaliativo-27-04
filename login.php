<?php
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

$erro = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        $sql = "SELECT * FROM treinadores WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $treinador = $stmt->fetch();

        if ($treinador && password_verify($senha, $treinador['senha'])) {
            $_SESSION['treinador_id'] = $treinador['id'];
            $_SESSION['treinador_nome'] = $treinador['nome'];
            
            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha inválidos.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}

echo $twig->render('login.html', ['erro' => $erro]);