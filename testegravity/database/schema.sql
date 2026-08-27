-- database/schema.sql
-- Primeira Migração do Banco de Dados (RNF009, RNF010)

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL DEFAULT 'user', -- 'admin' ou 'user'
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jogadores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nick VARCHAR(50) NOT NULL,
    nome_real VARCHAR(100),
    rota VARCHAR(20) NOT NULL, -- Top, Jungle, Mid, ADC, Support
    tier_elo VARCHAR(30) NOT NULL, -- Challenger, Grandmaster, Master, Diamond
    nacionalidade VARCHAR(50) NOT NULL, -- Coreia do Sul, Brasil, China, Europa, EUA
    time_atual VARCHAR(50) NOT NULL, -- T1, LOUD, Gen.G, G2, Fnatic
    foto_url TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS estatisticas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    jogador_id INTEGER NOT NULL,
    partidas INTEGER DEFAULT 0,
    vitorias INTEGER DEFAULT 0,
    derrotas INTEGER DEFAULT 0,
    taxa_vitoria_pct REAL DEFAULT 0.0,
    kda_ratio REAL DEFAULT 0.0,
    abates_media REAL DEFAULT 0.0,
    mortes_media REAL DEFAULT 0.0,
    assistencias_media REAL DEFAULT 0.0,
    cs_por_minuto REAL DEFAULT 0.0,
    dpm REAL DEFAULT 0.0,
    visao_score REAL DEFAULT 0.0,
    campeao_favorito VARCHAR(50),
    nota_media REAL DEFAULT 0.0,
    FOREIGN KEY (jogador_id) REFERENCES jogadores(id) ON DELETE CASCADE
);
