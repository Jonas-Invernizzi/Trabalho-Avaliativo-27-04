<?php
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$trade_id = (int)($_GET['id'] ?? 0);
$accepter_id = $_SESSION['treinador_id'];

try {
    // 1. Busca os detalhes da oferta e do Pokémon oferecido
    $stmt = $pdo->prepare("
        SELECT ot.*, c.pokedex_id as offered_pokedex_id, p.nome as offered_name
        FROM ofertas_troca ot
        JOIN capturas c ON ot.captura_id_oferecida = c.id
        JOIN pokedex p ON c.pokedex_id = p.id
        WHERE ot.id = :id
    ");
    $stmt->execute([':id' => $trade_id]);
    $trade = $stmt->fetch();

    if (!$trade) {
        die("⚠️ Erro: Oferta de troca não encontrada.");
    }

    if ($trade['treinador_id'] == $accepter_id) {
        die("⚠️ Erro: Você não pode aceitar sua própria oferta.");
    }

    $creator_id = $trade['treinador_id'];
    $offered_capture_id = $trade['captura_id_oferecida'];
    $desired_pokedex_id = $trade['pokedex_id_desejado'];

    // 2. Verifica se o Criador ainda possui o Pokémon para enviar
    $stmt = $pdo->prepare("SELECT id FROM capturas WHERE id = :cid AND quantidade_disponivel > 0");
    $stmt->execute([':cid' => $offered_capture_id]);
    if (!$stmt->fetch()) {
        die("⚠️ Erro: O criador não possui mais este Pokémon disponível.");
    }

    // 3. Verifica se VOCÊ (quem aceita) possui o Pokémon que o criador quer
    $stmt = $pdo->prepare("
        SELECT id FROM capturas 
        WHERE treinador_id = :tid AND pokedex_id = :pid AND quantidade_disponivel > 0 
        LIMIT 1
    ");
    $stmt->execute([':tid' => $accepter_id, ':pid' => $desired_pokedex_id]);
    $accepter_source_capture = $stmt->fetch();

    if (!$accepter_source_capture) {
        die("⚠️ Erro: Você não possui o Pokémon solicitado para realizar esta troca.");
    }

    $accepter_source_id = $accepter_source_capture['id'];

    // 4. INÍCIO DA TROCA (Transação)
    $pdo->beginTransaction();

    // A. Subtrai 1 de cada lado
    $pdo->prepare("UPDATE capturas SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = :id")
        ->execute([':id' => $offered_capture_id]);

    $pdo->prepare("UPDATE capturas SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = :id")
        ->execute([':id' => $accepter_source_id]);

    // B. Adiciona o Pokémon para os novos donos (Cria nova captura)
    // Criador recebe o Pokémon que o aceitante deu
    $pdo->prepare("INSERT INTO capturas (treinador_id, pokedex_id, quantidade_disponivel) VALUES (:tid, :pid, 1)")
        ->execute([':tid' => $creator_id, ':pid' => $desired_pokedex_id]);

    // Aceitante recebe o Pokémon que o criador deu
    $pdo->prepare("INSERT INTO capturas (treinador_id, pokedex_id, quantidade_disponivel) VALUES (:tid, :pid, 1)")
        ->execute([':tid' => $accepter_id, ':pid' => $trade['offered_pokedex_id']]);

    // C. Registra na Pokédex pessoal (treinador_pokedex) que agora eles "descobriram" esses Pokémon
    $pdo->prepare("INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)")
        ->execute([':tid' => $creator_id, ':pid' => $desired_pokedex_id]);
    $pdo->prepare("INSERT IGNORE INTO treinador_pokedex (treinador_id, pokedex_id) VALUES (:tid, :pid)")
        ->execute([':tid' => $accepter_id, ':pid' => $trade['offered_pokedex_id']]);

    // C. Remove a oferta de troca do mercado
    $pdo->prepare("DELETE FROM ofertas_troca WHERE id = :id")->execute([':id' => $trade_id]);

    $pdo->commit();

    // Redireciona para o perfil com mensagem de sucesso
    header("Location: treinador_perfil.php?id=" . $accepter_id . "&sucesso=Troca concluída! Verifique seu inventário.");
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("❌ Erro fatal na troca: " . $e->getMessage());
}
