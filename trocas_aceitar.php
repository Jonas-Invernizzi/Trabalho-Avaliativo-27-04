<?php
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

        // 1. Buscar detalhes da oferta de troca
        $stmt = $pdo->prepare("SELECT * FROM lista_desejos WHERE id = :id");
        $stmt->execute([':id' => $id_troca]);
        $troca = $stmt->fetch();

        if (!$troca) {
            throw new Exception("Oferta de troca não encontrada.");
        }

        if ($troca['treinador_id'] == $treinador_id_logado) {
            throw new Exception("Você não pode aceitar sua própria oferta.");
        }

        // 2. Verificar se o treinador logado possui o Pokémon que o criador da oferta quer
        $stmt = $pdo->prepare("SELECT id FROM capturas WHERE treinador_id = :tid AND pokedex_id = :pid LIMIT 1");
        $stmt->execute([
            ':tid' => $treinador_id_logado,
            ':pid' => $troca['pokedex_id_desejado']
        ]);
        $minha_captura = $stmt->fetch();

        if (!$minha_captura) {
            throw new Exception("Você não possui o Pokémon solicitado para completar esta troca.");
        }

        // 3. Trocar os donos: O Pokémon oferecido passa a ser do treinador logado
        $stmt = $pdo->prepare("UPDATE capturas SET treinador_id = :novo_dono WHERE id = :cid");
        $stmt->execute([':novo_dono' => $treinador_id_logado, ':cid' => $troca['captura_id_oferecida']]);

        // 4. Trocar os donos: O meu Pokémon passa a ser de quem criou a oferta
        $stmt = $pdo->prepare("UPDATE capturas SET treinador_id = :novo_dono WHERE id = :cid");
        $stmt->execute([':novo_dono' => $troca['treinador_id'], ':cid' => $minha_captura['id']]);

        // 5. Remover a oferta da lista de desejos
        $stmt = $pdo->prepare("DELETE FROM lista_desejos WHERE id = :id");
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