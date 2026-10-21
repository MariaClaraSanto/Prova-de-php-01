CREATE DATABASE IF NOT EXISTS catalogojogos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalogojogos;

CREATE TABLE IF NOT EXISTS jogos (
    codigo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(250) NOT NULL UNIQUE,
    link VARCHAR(250) NOT NULL,
    descricao TEXT,
    INDEX idx_nome (nome)
);