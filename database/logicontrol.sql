CREATE DATABASE IF NOT EXISTS logicontrol
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE logicontrol;

CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT,
  peso DECIMAL(10,2) DEFAULT 0,
  quantidade INT DEFAULT 0,
  valor_unitario DECIMAL(10,2) DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS enderecos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cep VARCHAR(15),
  estado CHAR(2),
  cidade VARCHAR(100),
  bairro VARCHAR(100),
  rua VARCHAR(150),
  numero VARCHAR(20),
  complemento VARCHAR(150),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transportadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cnpj VARCHAR(25),
  telefone VARCHAR(30),
  email VARCHAR(150),
  endereco VARCHAR(255),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fretes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  produto_id INT NOT NULL,
  transportador_id INT NOT NULL,
  endereco_origem_id INT NOT NULL,
  endereco_destino_id INT NOT NULL,
  data_envio DATE,
  previsao_entrega DATE,
  status ENUM('Pendente','Em trânsito','Concluído') DEFAULT 'Pendente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_frete_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
  CONSTRAINT fk_frete_transportador FOREIGN KEY (transportador_id) REFERENCES transportadores(id),
  CONSTRAINT fk_frete_origem FOREIGN KEY (endereco_origem_id) REFERENCES enderecos(id),
  CONSTRAINT fk_frete_destino FOREIGN KEY (endereco_destino_id) REFERENCES enderecos(id)
);

-- Dados demonstrativos opcionais. Execute apenas uma vez se quiser exemplos.
INSERT IGNORE INTO produtos (codigo,nome,descricao,peso,quantidade,valor_unitario) VALUES
('PRD001','Caixa de ferramentas','Kit de ferramentas',10.50,100,159.90),
('PRD002','Peças automotivas','Peças diversas',5.00,50,89.90);

INSERT INTO enderecos (nome,cep,estado,cidade,bairro,rua,numero,complemento)
SELECT 'Centro de Distribuição - Matriz','87501-000','PR','Umuarama','Centro','Avenida Paraná','1500','Galpão 2'
WHERE NOT EXISTS (SELECT 1 FROM enderecos WHERE nome='Centro de Distribuição - Matriz');

INSERT INTO transportadores (nome,cnpj,telefone,email,endereco)
SELECT 'Transportadora Brasil','12.345.678/0001-90','(44) 99999-9999','contato@transportadora.com','Avenida Paraná, 1500'
WHERE NOT EXISTS (SELECT 1 FROM transportadores WHERE nome='Transportadora Brasil');
