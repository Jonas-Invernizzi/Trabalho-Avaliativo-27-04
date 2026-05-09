<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

$erro = false;
$nome = '';
$email = '';
$cidade = '';
$fotoPerfil = 'img/default-avatar.png';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';
    $cidade = trim($_POST['cidade'] ?? '');

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

    if ($nome && $email && $senha && $senha_confirm) {
        // Regra: Mínimo 8 caracteres, 1 maiúscula, 1 minúscula, 1 número e 1 especial
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*_=+-]).{8,}$/';

        if ($senha !== $senha_confirm) {
            $erro = "As senhas não coincidem.";
        } elseif (!preg_match($regex, $senha)) {
            $erro = "A senha deve ser forte: mínimo de 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.";
        } else {
            try {
                // Verifica se o e-mail já está cadastrado antes de tentar inserir
                $stmtCheck = $pdo->prepare("SELECT id FROM treinadores WHERE email = :email");
                $stmtCheck->execute([':email' => $email]);

                if ($stmtCheck->fetch()) {
                    $erro = "Este e-mail já está cadastrado no sistema.";
                } else {
                    $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
                    $sql = 'INSERT INTO treinadores (nome, email, senha, cidade, foto_perfil) 
                        VALUES (:nome, :email, :senha, :cidade, :foto)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':nome'        => $nome,
                        ':email'       => $email,
                        ':senha'       => $senhaHash,
                        ':cidade'      => $cidade,
                        ':foto'        => $fotoPerfil
                    ]);

                    header("Location: login.php");
                    exit;
                }
            } catch (PDOException $e) {
                $erro = "Erro ao cadastrar treinador: " . $e->getMessage();
            }
        }
    } else {
        $erro = "Por favor, preencha todos os campos obrigatórios (nome, e-mail e senha).";
    }
}

echo $twig->render('treinadores_inserir.html', [
    'erro' => $erro,
    'nome' => $nome,
    'email' => $email,
    'cidade' => $cidade,
    'foto_perfil' => $fotoPerfil
]);
