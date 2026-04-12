-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS kumosi_db;
USE kumosi_db;

-- Tabela de solicitações de serviço
CREATE TABLE IF NOT EXISTS solicitacoes_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(30),
    servico VARCHAR(100) NOT NULL,
    orcamento VARCHAR(50),
    descricao TEXT NOT NULL,
    prazo DATE,
    status ENUM('pendente', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Tabela de mensagens de contacto
CREATE TABLE IF NOT EXISTS mensagens_contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(30),
    assunto VARCHAR(200),
    mensagem TEXT NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);

-- Tabela de doações (para o marketplace/projetos)
CREATE TABLE IF NOT EXISTS doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_doador VARCHAR(100),
    email VARCHAR(100),
    projeto_id INT,
    valor DECIMAL(10,2),
    moeda VARCHAR(3) DEFAULT 'USD',
    metodo_pagamento VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pendente',
    data_doacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criar usuário do banco (ALTERE A SENHA!)
CREATE USER IF NOT EXISTS 'kumosi_user'@'localhost' IDENTIFIED BY 'Kumosi2026!';
GRANT ALL PRIVILEGES ON kumosi_db.* TO 'kumosi_user'@'localhost';
FLUSH PRIVILEGES;

-- Inserir dados de exemplo (opcional)
INSERT INTO solicitacoes_servico (nome, email, servico, descricao) VALUES
('João Silva', 'joao@exemplo.com', 'Consultoria Social', 'Projeto de desenvolvimento comunitário'),
('Maria Santos', 'maria@exemplo.com', 'Capacitação', 'Formação para jovens empreendedores');