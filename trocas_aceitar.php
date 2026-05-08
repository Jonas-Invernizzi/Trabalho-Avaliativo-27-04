<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php'; // Inicia a sessão e carrega as globais

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$id_troca = (int)($_GET['id'] ?? 0);
$treinador_id_logado = $_SESSION['treinador_id'];

if ($id_troca > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Buscar detalhes da oferta de troca e verificar se a captura oferecida ainda tem estoque
        $stmt = $pdo->prepare("SELECT * FROM ofertas_troca WHERE id = :id");
        $stmt->execute([':id' => $id_troca]);
        $troca = $stmt->fetch();

        if (!$troca) {
            throw new Exception("Oferta de troca não encontrada.");
        }

        $stmt = $pdo->prepare("SELECT quantidade_disponivel FROM capturas WHERE id = :id");
        $stmt->execute([':id' => $troca['captura_id_oferecida']]);
        $qtd_oferecida = $stmt->fetchColumn();

        if ($qtd_oferecida <= 0) {
            throw new Exception("O treinador não possui mais este Pokémon disponível para troca.");
        }

        if ($troca['treinador_id'] == $treinador_id_logado) {
            throw new Exception("Você não pode aceitar sua própria oferta.");
        }

        // 2. Verificar se o treinador logado possui o Pokémon que o criador da oferta quer
        $stmt = $pdo->prepare("SELECT id FROM capturas WHERE treinador_id = :tid AND pokedex_id = :pid AND quantidade_disponivel > 0 LIMIT 1");
        $stmt->execute([
            ':tid' => $treinador_id_logado,
            ':pid' => $troca['pokedex_id_desejado']
        ]);
        $minha_captura = $stmt->fetch();

        if (!$minha_captura) {
            throw new Exception("Você não possui o Pokémon solicitado para completar esta troca.");
        }

        // 3. Deduzir a quantidade de ambos os treinadores
        $stmt = $pdo->prepare("UPDATE capturas SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = :id");
        $stmt->execute([':id' => $troca['captura_id_oferecida']]);
        $stmt->execute([':id' => $minha_captura['id']]);

        // 4. Identificar o Pokémon que o aceitante está recebendo do criador da oferta
        $stmt = $pdo->prepare("SELECT pokedex_id FROM capturas WHERE id = :id");
        $stmt->execute([':id' => $troca['captura_id_oferecida']]);
        $pid_vindo_do_criador = $stmt->fetchColumn();

        // 5. Atualizar ou Inserir para o Treinador Logado (Aceitante)
        $stmt_check = $pdo->prepare("SELECT id FROM capturas WHERE treinador_id = :tid AND pokedex_id = :pid LIMIT 1");
        $stmt_check->execute([':tid' => $treinador_id_logado, ':pid' => $pid_vindo_do_criador]);
        $id_existente_logado = $stmt_check->fetchColumn();

        if ($id_existente_logado) {
            $stmt = $pdo->prepare("UPDATE capturas SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = :id");
            $stmt->execute([':id' => $id_existente_logado]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO capturas (treinador_id, pokedex_id, nivel, quantidade_disponivel) VALUES (:tid, :pid, 5, 1)");
            $stmt->execute([':tid' => $treinador_id_logado, ':pid' => $pid_vindo_do_criador]);
        }

        // 6. Atualizar ou Inserir para o Criador da Oferta (que recebe o desejado)
        $stmt_check->execute([':tid' => $troca['treinador_id'], ':pid' => $troca['pokedex_id_desejado']]);
        $id_existente_criador = $stmt_check->fetchColumn();

        if ($id_existente_criador) {
            $stmt = $pdo->prepare("UPDATE capturas SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = :id");
            $stmt->execute([':id' => $id_existente_criador]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO capturas (treinador_id, pokedex_id, nivel, quantidade_disponivel) VALUES (:tid, :pid, 5, 1)");
            $stmt->execute([':tid' => $troca['treinador_id'], ':pid' => $troca['pokedex_id_desejado']]);
        }

        // 6.5 Registrar permanentemente a descoberta para ambos os treinadores
        $sql_permanent = "INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)";
        $stmt_perm = $pdo->prepare($sql_permanent);
        $stmt_perm->execute([':tid' => $treinador_id_logado, ':pid' => $pid_vindo_do_criador]);
        $stmt_perm->execute([':tid' => $troca['treinador_id'], ':pid' => $troca['pokedex_id_desejado']]);

        // 7. Remover a oferta finalizada
        $stmt = $pdo->prepare("DELETE FROM ofertas_troca WHERE id = :id");
        $stmt->execute([':id' => $id_troca]);

        $pdo->commit();
        header("Location: trocas_listar.php?sucesso=Troca realizada com sucesso!");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        header("Location: trocas_listar.php?erro=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: trocas_listar.php");
exit;