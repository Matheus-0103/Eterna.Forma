CREATE DATABASE IF NOT EXISTS meu_sistema
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE meu_sistema;

CREATE TABLE IF NOT EXISTS desportista (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nome           VARCHAR(80)      NOT NULL,
    sobrenome      VARCHAR(80)      NOT NULL,
    email          VARCHAR(191)     NOT NULL,
    senha_hash     VARCHAR(255)     NOT NULL,
    criado_em      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_desportista_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;