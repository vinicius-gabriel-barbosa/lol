-- ============================================================
-- Script DDL - Criação do Banco de Dados
-- Baseado no diagrama de entidade-relacionamento
-- ============================================================

-- Criação do banco de dados (opcional)
-- CREATE DATABASE IF NOT EXISTS estatisticas_esportivas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE estatisticas_esportivas;

-- ============================================================
-- TABELA: perfis
-- ============================================================
CREATE TABLE perfis (
    id          INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do perfil',
    nome        VARCHAR(50) NOT NULL COMMENT 'Nome do perfil (ex: Administrador, Analista, Usuário)',
    descricao   VARCHAR(100) COMMENT 'Descrição detalhada do perfil',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Perfis de acesso dos usuários do sistema';

-- ============================================================
-- TABELA: usuarios
-- ============================================================
CREATE TABLE usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do usuário',
    nome        VARCHAR(100) NOT NULL COMMENT 'Nome completo do usuário',
    email       VARCHAR(150) NOT NULL COMMENT 'Endereço de e-mail (login)',
    senha_hash  VARCHAR(255) NOT NULL COMMENT 'Hash da senha para autenticação',
    perfil_id   INT COMMENT 'Referência ao perfil de acesso do usuário',
    ativo       BOOLEAN DEFAULT TRUE COMMENT 'Indica se o usuário está ativo no sistema',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',

    CONSTRAINT uk_usuarios_email UNIQUE (email),
    CONSTRAINT fk_usuarios_perfil_id FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários cadastrados no sistema';

-- ============================================================
-- TABELA: jogadores
-- ============================================================
CREATE TABLE jogadores (
    id            INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do jogador',
    nome          VARCHAR(150) NOT NULL COMMENT 'Nome completo do jogador',
    posicao       VARCHAR(50) COMMENT 'Posição em campo (ex: Atacante, Meia, Zagueiro)',
    data_nasc     DATE COMMENT 'Data de nascimento do jogador',
    numero        INT COMMENT 'Número da camisa',
    time_atual    VARCHAR(100) COMMENT 'Nome do time/clube atual',
    nacionalidade VARCHAR(50) COMMENT 'País de origem do jogador',
    ativo         BOOLEAN DEFAULT TRUE COMMENT 'Indica se o jogador está ativo',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro de jogadores/esportistas';

-- ============================================================
-- TABELA: estatisticas
-- ============================================================
CREATE TABLE estatisticas (
    id                  INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único da estatística',
    jogador_id          INT NOT NULL COMMENT 'Referência ao jogador dono das estatísticas',
    temporada           VARCHAR(10) COMMENT 'Temporada/ano de referência (ex: 2024, 2024/2025)',
    jogos_disputados    INT COMMENT 'Quantidade total de jogos disputados na temporada',
    metrica_principal   DECIMAL(10,2) COMMENT 'Métrica principal de desempenho (ex: gols, pontos)',
    metrica_secundaria  DECIMAL(10,2) COMMENT 'Métrica secundária (ex: assistências, rebotes)',
    metrica_terciaria   DECIMAL(10,2) COMMENT 'Métrica terciária (ex: passes, bloqueios)',
    eficiencia          DECIMAL(5,2) COMMENT 'Índice de eficiência do jogador',
    media_geral         DECIMAL(5,2) COMMENT 'Média geral de desempenho',
    desvio_padrao       DECIMAL(5,2) COMMENT 'Desvio padrão das métricas',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',

    CONSTRAINT fk_estatisticas_jogador_id FOREIGN KEY (jogador_id) REFERENCES jogadores(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Estatísticas de desempenho dos jogadores por temporada';

-- ============================================================
-- TABELA: logs_importacao
-- ============================================================
CREATE TABLE logs_importacao (
    id              INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do log',
    usuario_id      INT COMMENT 'Referência ao usuário que realizou a importação',
    nome_arquivo    VARCHAR(255) COMMENT 'Nome do arquivo importado',
    qtd_registros   INT COMMENT 'Quantidade de registros processados no arquivo',
    status          ENUM('sucesso', 'erro', 'aviso') COMMENT 'Status do processamento da importação',
    mensagem        TEXT COMMENT 'Mensagem descritiva do resultado da importação',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da importação',

    CONSTRAINT fk_logs_usuario_id FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de importação de dados no sistema';

-- ============================================================
-- ÍNDICES ADICIONAIS (performance)
-- ============================================================
CREATE INDEX idx_jogadores_nome ON jogadores(nome);
CREATE INDEX idx_jogadores_time_atual ON jogadores(time_atual);
CREATE INDEX idx_estatisticas_jogador_temporada ON estatisticas(jogador_id, temporada);
CREATE INDEX idx_estatisticas_temporada ON estatisticas(temporada);
CREATE INDEX idx_logs_importacao_usuario ON logs_importacao(usuario_id);
CREATE INDEX idx_logs_importacao_status ON logs_importacao(status);
CREATE INDEX idx_logs_importacao_created_at ON logs_importacao(created_at);
