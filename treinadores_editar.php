<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cidade = $_POST['cidade'];
    $fotoPerfil = null;

    // Lógica de Upload de Foto de Perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid('perfil_') . '.' . $extensao;
        $diretorio = 'img/';
        
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $diretorio . $nomeArquivo)) {
            $fotoPerfil = $diretorio . $nomeArquivo;
        }
    }

    $sql = 'UPDATE treinadores SET nome = :nome, email = :email, cidade = :cidade' . 
           ($fotoPerfil ? ', foto_perfil = :foto' : '') . 
           ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $params = [':nome' => $nome, ':email' => $email, ':cidade' => $cidade, ':id' => $id];
    if ($fotoPerfil) $params[':foto'] = $fotoPerfil;
    
    $stmt->execute($params);

    // Se o usuário estiver editando o próprio perfil, atualiza a sessão após o sucesso no banco
    if ($id == $_SESSION['treinador_id']) {
        $_SESSION['treinador_nome'] = $nome;
        if ($fotoPerfil) {
            $_SESSION['treinador_foto'] = $fotoPerfil;
        }
    }

    header('Location: treinadores_listar.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM treinadores WHERE id = :id');
$stmt->execute([':id' => $id]);
$treinador = $stmt->fetch();

if (!$treinador) {
    header('Location: treinadores_listar.php');
    exit;
}

echo $twig->render('treinadores_editar.html', ['treinador' => $treinador]);