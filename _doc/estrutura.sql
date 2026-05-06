-- 1. CRIAÇÃO DO BANCO DE DADOS
CREATE DATABASE IF NOT EXISTS sistema_pokemon;
USE sistema_pokemon;

-- 2. TABELA POKÉDEX (Catálogo oficial de espécies)
-- Aqui cadastramos a "base". Ex: O Pikachu é o número 25, tipo Elétrico.
CREATE TABLE pokedex (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_dex INT NOT NULL,             -- Número oficial na Pokédex (Pode repetir para Shinies)
    nome VARCHAR(50) NOT NULL,
    tipo_principal VARCHAR(30) NOT NULL, -- Ex: Fogo, Água, Planta
    tipo_secundario VARCHAR(30) NULL,    -- Pode ser nulo se o Pokémon tiver só um tipo
    imagem_url VARCHAR(255) NULL,        -- URL para exibir a foto do Pokémon no site
    is_shiny TINYINT(1) DEFAULT 0        -- Define se o Pokémon é shiny
);

-- 3. TABELA DE TREINADORES (Usuários do sistema)
CREATE TABLE treinadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    cidade VARCHAR(50) NULL,
    foto_perfil VARCHAR(255) DEFAULT 'img/default-avatar.png',
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABELA DE CAPTURAS (O inventário de Pokémon dos treinadores)
-- Relaciona um treinador a uma espécie da Pokédex.
CREATE TABLE capturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,           -- Quem é o dono
    pokedex_id INT NOT NULL,             -- Qual espécie ele capturou
    apelido VARCHAR(50) NULL,            -- Nome carinhoso dado pelo treinador
    nivel INT DEFAULT 1,
    data_captura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id) REFERENCES pokedex(id) ON DELETE RESTRICT
);

-- 5. TABELA DE INVENTÁRIO (Mochila de itens)
CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,
    nome_item VARCHAR(50) NOT NULL,      -- Ex: Pokébola, Poção, Doce Raro
    quantidade INT DEFAULT 1,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE
);

-- 6. TABELA LISTA DE DESEJOS (Apenas intenção de ter)
CREATE TABLE lista_desejos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,
    pokedex_id INT NOT NULL,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id) REFERENCES pokedex(id) ON DELETE CASCADE
);

-- 7. TABELA OFERTAS DE TROCA (O motor do mercado de trocas)
-- Relaciona: Quem criou + O que quer + O que oferece das suas capturas
CREATE TABLE ofertas_troca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinador_id INT NOT NULL,              -- Quem criou o anúncio
    pokedex_id_desejado INT NOT NULL,       -- Espécie que ele QUER (da pokedex)
    captura_id_oferecida INT NOT NULL,      -- Pokémon que ele DÁ (das suas capturas)
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (treinador_id) REFERENCES treinadores(id) ON DELETE CASCADE,
    FOREIGN KEY (pokedex_id_desejado) REFERENCES pokedex(id) ON DELETE CASCADE,
    FOREIGN KEY (captura_id_oferecida) REFERENCES capturas(id) ON DELETE CASCADE
);

-- ==========================================================
-- DADOS INICIAIS PARA TESTES (SEED)
-- ==========================================================

-- Populando a Pokédex com alguns exemplos (Simples e Dual Type)
INSERT INTO pokedex (numero_dex, nome, tipo_principal, tipo_secundario, imagem_url) VALUES
(1, 'Bulbasaur', 'Planta', 'Veneno', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/1.png'),
(4, 'Charmander', 'Fogo', NULL, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/4.png'),
(6, 'Charizard', 'Fogo', 'Voador', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/6.png'),
(7, 'Squirtle', 'Água', NULL, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/7.png'),
(25, 'Pikachu', 'Elétrico', NULL, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png'),
(94, 'Gengar', 'Fantasma', 'Veneno', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/94.png');

-- Criando Treinadores de teste
INSERT INTO treinadores (nome, email, cidade) VALUES
('Ash Ketchum', 'ash@kanto.com', 'Pallet Town'),
('Gary Oak', 'gary@kanto.com', 'Pallet Town'),
('Misty', 'misty@cerulean.com', 'Cerulean City');

-- Criando Capturas (Pokémon que eles já possuem)
-- Ash tem um Pikachu (ID 5 na pokedex)
INSERT INTO capturas (treinador_id, pokedex_id, apelido, nivel) VALUES (1, 5, 'Amigão', 10);
-- Gary tem um Squirtle (ID 4 na pokedex)
INSERT INTO capturas (treinador_id, pokedex_id, apelido, nivel) VALUES (2, 4, 'Boss', 12);
-- Misty tem um Bulbasaur (ID 1 na pokedex)
INSERT INTO capturas (treinador_id, pokedex_id, apelido, nivel) VALUES (3, 1, 'Bulba', 5);

-- Criando um Desejo de Troca
-- Ash (ID 1) quer um Bulbasaur (ID 1 na pokedex) e oferece o seu Pikachu (ID 1 nas capturas)
INSERT INTO ofertas_troca (treinador_id, pokedex_id_desejado, captura_id_oferecida) VALUES (1, 1, 1);