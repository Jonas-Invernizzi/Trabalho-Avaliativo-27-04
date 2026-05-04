<?php
require_once("vendor/autoload.php");

$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/templates');
$twig = new \Twig\Environment($loader, [
    // 'cache' => '/path/to/compilation_cache', // Descomente para produção
]);

// Adiciona a variável de sessão globalmente para todos os templates
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$twig->addGlobal('session', $_SESSION);