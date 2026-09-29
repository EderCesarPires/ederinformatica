SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS eder_informatica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eder_informatica;

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(160) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- hash bcrypt
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    done        TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description VARCHAR(255) NULL,
    price       DECIMAL(10,2) NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clients (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    document    VARCHAR(20)  NOT NULL UNIQUE,   -- CPF ou CNPJ (somente dígitos)
    cep         VARCHAR(9)   NOT NULL,
    address     VARCHAR(255) NULL,              -- preenchido via ViaCEP
    complement  VARCHAR(150) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS service_orders (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    client_id           INT NOT NULL,
    user_id             INT NOT NULL,
    status              ENUM('aberta','em_andamento','concluida','cancelada') NOT NULL DEFAULT 'aberta',
    subtotal            DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_percent    DECIMAL(5,2)  NOT NULL DEFAULT 0,
    surcharge_percent   DECIMAL(5,2)  NOT NULL DEFAULT 0,
    total               DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes               TEXT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_os_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_os_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS service_order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    service_id  INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    unit_price  DECIMAL(10,2) NOT NULL,          -- preço congelado no momento da OS
    CONSTRAINT fk_item_order   FOREIGN KEY (order_id)   REFERENCES service_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_service FOREIGN KEY (service_id) REFERENCES services(id)       ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Dados iniciais de exemplo
INSERT INTO services (name, description, price) VALUES
    ('Formatação de computador', 'Formatação + instalação do sistema operacional', 120.00),
    ('Limpeza interna', 'Limpeza completa e troca de pasta térmica', 80.00),
    ('Instalação de software', 'Instalação e configuração de programas', 50.00),
    ('Troca de tela de notebook', 'Mão de obra para substituição da tela', 150.00),
    ('Backup de dados', 'Cópia de segurança de arquivos', 90.00);
