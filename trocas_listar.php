<?php
session_start();

if (!isset($_SESSION['treinador_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'carregar_pdo.php';
require_once 'carregar_twig.php';

$sql = "SELECT 
            ld.id, 
            t.nome as treinador_nome, 
            ld.treinador_id as criador_id,
            p_desejado.nome as pokemon_desejado, 
            p_desejado.imagem_url as img_desejado,
            c_oferecida.apelido as apelido_oferecido,
            p_oferecido.nome as pokemon_oferecido,
            p_oferecido.imagem_url as img_oferecido
        FROM ofertas_troca ld
        JOIN treinadores t ON ld.treinador_id = t.id
        JOIN pokedex p_desejado ON ld.pokedex_id_desejado = p_desejado.id
        JOIN capturas c_oferecida ON ld.captura_id_oferecida = c_oferecida.id
        JOIN pokedex p_oferecido ON c_oferecida.pokedex_id = p_oferecido.id
        ORDER BY ld.data_criacao DESC";

$stmt = $pdo->query($sql);
$trocas = $stmt->fetchAll();

echo $twig->render('trocas_listar.html', ['trocas' => $trocas]);