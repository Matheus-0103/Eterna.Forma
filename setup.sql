-- ═══════════════════════════════════════════════════════════════════
-- SportMatch — Schema completo
-- Execute: mysql -u root -p < setup.sql
-- ═══════════════════════════════════════════════════════════════════
CREATE DATABASE IF NOT EXISTS sportmatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportmatch;

-- DESPORTISTAS
CREATE TABLE IF NOT EXISTS desportistas (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(80)  NOT NULL,
    sobrenome     VARCHAR(80)  NOT NULL,
    email         VARCHAR(191) NOT NULL,
    senha_hash    VARCHAR(255) NOT NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_desportista_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PERFIS FITNESS (1:1 com desportistas)
CREATE TABLE IF NOT EXISTS perfis_fitness (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    desportista_id   INT UNSIGNED NOT NULL,
    idade            TINYINT UNSIGNED,
    sexo             ENUM('M','F','outro'),
    peso_kg          DECIMAL(5,2),
    altura_cm        SMALLINT UNSIGNED,
    nivel            ENUM('iniciante','intermediario','avancado') NOT NULL DEFAULT 'iniciante',
    objetivo         ENUM('emagrecimento','hipertrofia','resistencia','saude','performance') NOT NULL DEFAULT 'saude',
    modalidades      JSON COMMENT 'Array de esportes praticados',
    disponibilidade  JSON COMMENT 'Dias e horários disponíveis',
    localizacao      VARCHAR(120),
    bio              TEXT,
    foto_url         VARCHAR(500),
    criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_perfil_desportista (desportista_id),
    CONSTRAINT fk_perfil_desportista FOREIGN KEY (desportista_id) REFERENCES desportistas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MATCHES
CREATE TABLE IF NOT EXISTS matches (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    desportista_a_id    INT UNSIGNED NOT NULL,
    desportista_b_id    INT UNSIGNED NOT NULL,
    status              ENUM('pendente','aceito','recusado') NOT NULL DEFAULT 'pendente',
    score_similaridade  DECIMAL(5,2) COMMENT 'Score 0-100',
    iniciado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_match_par (desportista_a_id, desportista_b_id),
    CONSTRAINT fk_match_a FOREIGN KEY (desportista_a_id) REFERENCES desportistas(id) ON DELETE CASCADE,
    CONSTRAINT fk_match_b FOREIGN KEY (desportista_b_id) REFERENCES desportistas(id) ON DELETE CASCADE,
    CHECK (desportista_a_id <> desportista_b_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EQUIPE TÉCNICA
CREATE TABLE IF NOT EXISTS equipe_tecnica (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(80)  NOT NULL,
    sobrenome     VARCHAR(80)  NOT NULL,
    email         VARCHAR(191) NOT NULL,
    senha_hash    VARCHAR(255) NOT NULL,
    especialidade VARCHAR(120),
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tecnico_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ATENDENTES DE ACADEMIA
CREATE TABLE IF NOT EXISTS atendentes (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(80)  NOT NULL,
    sobrenome       VARCHAR(80)  NOT NULL,
    email           VARCHAR(191) NOT NULL,
    senha_hash      VARCHAR(255) NOT NULL,
    academia        VARCHAR(120),
    ativo           TINYINT(1)   NOT NULL DEFAULT 1,
    cadastrado_por  INT UNSIGNED COMMENT 'ID do técnico responsável',
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_atendente_email (email),
    CONSTRAINT fk_atendente_tecnico FOREIGN KEY (cadastrado_por) REFERENCES equipe_tecnica(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CHAMADOS DE SUPORTE
CREATE TABLE IF NOT EXISTS chamados (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    desportista_id INT UNSIGNED NOT NULL,
    tecnico_id     INT UNSIGNED,
    titulo         VARCHAR(200) NOT NULL,
    descricao      TEXT         NOT NULL,
    categoria      ENUM('tecnico','conta','fitness','match','outro') NOT NULL DEFAULT 'outro',
    prioridade     ENUM('baixa','media','alta','critica')            NOT NULL DEFAULT 'media',
    status         ENUM('aberto','em_andamento','resolvido','fechado') NOT NULL DEFAULT 'aberto',
    resposta       TEXT,
    respondido_em  DATETIME,
    criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade),
    CONSTRAINT fk_chamado_desportista FOREIGN KEY (desportista_id) REFERENCES desportistas(id) ON DELETE CASCADE,
    CONSTRAINT fk_chamado_tecnico     FOREIGN KEY (tecnico_id)     REFERENCES equipe_tecnica(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED: técnico padrão (senha: Admin@2025)
INSERT IGNORE INTO equipe_tecnica (nome, sobrenome, email, senha_hash, especialidade) VALUES
('Admin', 'Sistema', 'admin@sportmatch.com',
 '$2y$12$LQv3c1yqBWVHxkd0LI1Vre4eVT5J3mfBfH2PjNQzA7gZLuM9HnC6a',
 'Administrador');
