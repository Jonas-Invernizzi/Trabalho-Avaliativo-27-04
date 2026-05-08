-- 1. CRIAÇÃO DO BANCO DE DADOS
CREATE DATABASE IF NOT EXISTS sistema_pokemon;
USE sistema_pokemon;

DROP TABLE IF EXISTS treinador_pokedex;
DROP TABLE IF EXISTS ofertas_troca;
DROP TABLE IF EXISTS lista_desejos;
DROP TABLE IF EXISTS inventario;
DROP TABLE IF EXISTS capturas;
DROP TABLE IF EXISTS treinadores;
DROP TABLE IF EXISTS pokedex;

CREATE TABLE pokedex (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_dex INT NOT NULL,
    is_shiny TINYINT(1) DEFAULT 0,
    nome VARCHAR(50) NOT NULL DEFAULT 'unknown'
);

CREATE TABLE treinadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    cidade VARCHAR(50) NULL,
    foto_perfil VARCHAR(255) DEFAULT 'img/default-avatar.png',
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE capturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,
    pokedex_id INT NOT NULL,
    nivel INT DEFAULT 1,
    quantidade_disponivel INT DEFAULT 0,
    data_captura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id) REFERENCES pokedex(id) ON DELETE CASCADE
);

CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,
    nome_item VARCHAR(50) NOT NULL,
    quantidade INT DEFAULT 1,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE
);

CREATE TABLE lista_desejos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,
    pokedex_id INT NOT NULL,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id) REFERENCES pokedex(id) ON DELETE CASCADE
);

CREATE TABLE ofertas_troca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,
    pokedex_id_desejado INT NOT NULL,
    captura_id_oferecida INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id_desejado) REFERENCES pokedex(id) ON DELETE CASCADE,
    FOREIGN KEY (captura_id_oferecida) REFERENCES capturas(id) ON DELETE CASCADE
);

CREATE TABLE treinador_pokedex (
    treinador_id INT NOT NULL,
    pokedex_id INT NOT NULL,
    PRIMARY KEY (treinador_id, pokedex_id),
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id) REFERENCES pokedex(id) ON DELETE CASCADE
);