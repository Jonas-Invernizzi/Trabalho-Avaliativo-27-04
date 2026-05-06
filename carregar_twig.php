<?php
require_once("vendor/autoload.php");

$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/templates');
$twig = new \Twig\Environment($loader, [
    // 'cache' => '/path/to/compilation_cache', // Descomente para produção
]);

// Adiciona a variável de sessão globalmente para todos os templates
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Verificação de integridade: O usuário da sessão ainda existe no banco?
if (isset($_SESSION['treinador_id'])) {
    // Garante que o PDO está disponível para a verificação
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT id FROM treinadores WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['treinador_id']]);
        if (!$stmt->fetch()) {
            // Se o treinador não existe mais no banco, destrói a sessão
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit;
        }
    }
}

$twig->addGlobal('session', $_SESSION);