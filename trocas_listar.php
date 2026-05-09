<?php
// carregar_twig.php já inicia a sessão e configura o session_save_path
// Não é necessário chamar session_start() aqui novamente.

require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}

$viewer_id = $_SESSION['treinador_id'];
$sql = "SELECT 
            ot.id, 
            t.nome as treinador_nome, 
            ot.treinador_id as criador_id,
            p_desejado.numero_dex as pokemon_desejado_dex, p_desejado.is_shiny as pokemon_desejado_shiny, p_desejado.nome as pokemon_desejado_nome,
            p_oferecido.numero_dex as pokemon_oferecido_dex, p_oferecido.is_shiny as pokemon_oferecido_shiny, p_oferecido.nome as pokemon_oferecido_nome,
            COALESCE(estoque_usuario.possui, 0) as possui
        FROM ofertas_troca ot
        LEFT JOIN treinadores t ON ot.treinador_id = t.id
        LEFT JOIN pokedex p_desejado ON ot.pokedex_id_desejado = p_desejado.id
        LEFT JOIN capturas c_oferecida ON ot.captura_id_oferecida = c_oferecida.id
        LEFT JOIN pokedex p_oferecido ON c_oferecida.pokedex_id = p_oferecido.id
        LEFT JOIN (
            SELECT pokedex_id, SUM(quantidade_disponivel) as possui 
            FROM capturas 
            WHERE treinador_id = :viewer_id AND quantidade_disponivel > 0
            GROUP BY pokedex_id
        ) as estoque_usuario ON ot.pokedex_id_desejado = estoque_usuario.pokedex_id
        WHERE c_oferecida.quantidade_disponivel > 0
        ORDER BY ot.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':viewer_id' => $viewer_id]);
$trocas = $stmt->fetchAll();

echo $twig->render('trocas_listar.html', ['trocas' => $trocas]);
