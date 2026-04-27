<?php
// Configurações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'sistema_pokemon';
$username = 'root';
$password = ''; // Adicione uma senha se necessário para segurança

try {
    // Cria a conexão PDO com charset UTF-8 para suporte a caracteres especiais
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Define o modo de erro para lançar exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Opcional: Define o modo de busca padrão para objetos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Conexão bem-sucedida (pode adicionar logs aqui se necessário)
} catch (PDOException $e) {
    // Trata erros de conexão
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>