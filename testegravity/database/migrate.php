<?php
// database/migrate.php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

echo "=== Executando Migração do Banco de Dados Recall (RNF010) ===\n";

// Executa DDL do schema.sql
$sqlSchema = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sqlSchema);
echo "✔ Tabelas 'usuarios', 'jogadores' e 'estatisticas' verificadas/criadas com sucesso.\n";

// 1. Criar usuários iniciais obrigatoriamente usando password_hash() (RNF006, RNF007)
$stmtCheckUsers = $pdo->query("SELECT COUNT(*) FROM usuarios");
if ($stmtCheckUsers->fetchColumn() == 0) {
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $userPass  = password_hash('user123', PASSWORD_DEFAULT);

    $stmtUser = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)");
    
    // Administrador (RF011, RF012)
    $stmtUser->execute(['Administrador Recall', 'admin@sistema.com', $adminPass, 'admin']);
    // Usuário Comum (RF011)
    $stmtUser->execute(['Usuário Analista', 'user@sistema.com', $userPass, 'user']);

    echo "✔ Usuários padrões inseridos:\n";
    echo "   - Admin: admin@sistema.com / admin123\n";
    echo "   - User: user@sistema.com / user123\n";
}

// 2. Se a tabela jogadores estiver vazia, insere jogadores e estatísticas fictícias de eSports LoL
$stmtCheckPlayers = $pdo->query("SELECT COUNT(*) FROM jogadores");
if ($stmtCheckPlayers->fetchColumn() == 0) {
    $playersData = [
        [
            'nick' => 'Faker', 'nome_real' => 'Lee Sang-hyeok', 'rota' => 'Mid', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'T1',
            'partidas' => 142, 'vitorias' => 102, 'derrotas' => 40, 'kda' => 5.2, 'abates' => 6.1, 'mortes' => 2.1, 'assistencias' => 6.8,
            'cs' => 9.4, 'dpm' => 685.0, 'visao' => 38.5, 'campeao' => 'Ahri', 'nota' => 9.8
        ],
        [
            'nick' => 'Chovy', 'nome_real' => 'Jeong Ji-hoon', 'rota' => 'Mid', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'Gen.G',
            'partidas' => 130, 'vitorias' => 98, 'derrotas' => 32, 'kda' => 6.1, 'abates' => 5.8, 'mortes' => 1.6, 'assistencias' => 5.9,
            'cs' => 10.2, 'dpm' => 710.5, 'visao' => 35.0, 'campeao' => 'Yone', 'nota' => 9.7
        ],
        [
            'nick' => 'Tinowns', 'nome_real' => 'Thiago Sartori', 'rota' => 'Mid', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Brasil', 'time_atual' => 'LOUD',
            'partidas' => 110, 'vitorias' => 74, 'derrotas' => 36, 'kda' => 4.4, 'abates' => 5.2, 'mortes' => 2.4, 'assistencias' => 6.4,
            'cs' => 8.9, 'dpm' => 620.0, 'visao' => 32.1, 'campeao' => 'Syndra', 'nota' => 8.9
        ],
        [
            'nick' => 'Caps', 'nome_real' => 'Rasmus Winther', 'rota' => 'Mid', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Europa', 'time_atual' => 'G2 Esports',
            'partidas' => 125, 'vitorias' => 85, 'derrotas' => 40, 'kda' => 4.8, 'abates' => 6.4, 'mortes' => 2.8, 'assistencias' => 7.0,
            'cs' => 9.1, 'dpm' => 660.0, 'visao' => 34.0, 'campeao' => 'Sylas', 'nota' => 9.2
        ],
        [
            'nick' => 'Robo', 'nome_real' => 'Leonardo Souza', 'rota' => 'Top', 'tier_elo' => 'Grandmaster',
            'nacionalidade' => 'Brasil', 'time_atual' => 'LOUD',
            'partidas' => 108, 'vitorias' => 72, 'derrotas' => 36, 'kda' => 3.8, 'abates' => 4.1, 'mortes' => 3.1, 'assistencias' => 7.6,
            'cs' => 8.2, 'dpm' => 550.0, 'visao' => 29.5, 'campeao' => 'K\'Sante', 'nota' => 8.7
        ],
        [
            'nick' => 'Zeus', 'nome_real' => 'Choi Woo-je', 'rota' => 'Top', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'T1',
            'partidas' => 138, 'vitorias' => 96, 'derrotas' => 42, 'kda' => 4.6, 'abates' => 4.9, 'mortes' => 2.5, 'assistencias' => 6.6,
            'cs' => 8.8, 'dpm' => 590.0, 'visao' => 31.0, 'campeao' => 'Aatrox', 'nota' => 9.4
        ],
        [
            'nick' => 'Ruler', 'nome_real' => 'Park Jae-hyuk', 'rota' => 'ADC', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'JD Gaming',
            'partidas' => 120, 'vitorias' => 90, 'derrotas' => 30, 'kda' => 5.9, 'abates' => 7.2, 'mortes' => 1.9, 'assistencias' => 6.0,
            'cs' => 10.0, 'dpm' => 740.0, 'visao' => 28.0, 'campeao' => 'Jinx', 'nota' => 9.6
        ],
        [
            'nick' => 'brTT', 'nome_real' => 'Felipe Gonçalves', 'rota' => 'ADC', 'tier_elo' => 'Master',
            'nacionalidade' => 'Brasil', 'time_atual' => 'LOS',
            'partidas' => 95, 'vitorias' => 58, 'derrotas' => 37, 'kda' => 3.9, 'abates' => 6.1, 'mortes' => 3.2, 'assistencias' => 6.3,
            'cs' => 8.7, 'dpm' => 605.0, 'visao' => 26.0, 'campeao' => 'Draven', 'nota' => 8.5
        ],
        [
            'nick' => 'Keria', 'nome_real' => 'Ryu Min-seok', 'rota' => 'Support', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'T1',
            'partidas' => 140, 'vitorias' => 100, 'derrotas' => 40, 'kda' => 5.5, 'abates' => 2.1, 'mortes' => 2.3, 'assistencias' => 12.5,
            'cs' => 2.1, 'dpm' => 280.0, 'visao' => 75.0, 'campeao' => 'Nami', 'nota' => 9.7
        ],
        [
            'nick' => 'Canyon', 'nome_real' => 'Kim Geon-bu', 'rota' => 'Jungle', 'tier_elo' => 'Challenger',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'Gen.G',
            'partidas' => 128, 'vitorias' => 92, 'derrotas' => 36, 'kda' => 5.0, 'abates' => 4.5, 'mortes' => 2.2, 'assistencias' => 8.1,
            'cs' => 6.8, 'dpm' => 490.0, 'visao' => 42.0, 'campeao' => 'Lee Sin', 'nota' => 9.5
        ],
        [
            'nick' => 'Croc', 'nome_real' => 'Park Jong-hoon', 'rota' => 'Jungle', 'tier_elo' => 'Grandmaster',
            'nacionalidade' => 'Coreia do Sul', 'time_atual' => 'LOUD',
            'partidas' => 112, 'vitorias' => 75, 'derrotas' => 37, 'kda' => 4.1, 'abates' => 3.8, 'mortes' => 2.7, 'assistencias' => 8.4,
            'cs' => 6.2, 'dpm' => 430.0, 'visao' => 39.0, 'campeao' => 'Sejuani', 'nota' => 8.8
        ],
        [
            'nick' => 'Ceos', 'nome_real' => 'Denilson Gonçalves', 'rota' => 'Support', 'tier_elo' => 'Grandmaster',
            'nacionalidade' => 'Brasil', 'time_atual' => 'KaBuM!',
            'partidas' => 105, 'vitorias' => 68, 'derrotas' => 37, 'kda' => 4.3, 'abates' => 1.4, 'mortes' => 2.5, 'assistencias' => 11.2,
            'cs' => 1.8, 'dpm' => 210.0, 'visao' => 68.0, 'campeao' => 'Thresh', 'nota' => 8.8
        ]
    ];

    $stmtPlayer = $pdo->prepare("INSERT INTO jogadores (nick, nome_real, rota, tier_elo, nacionalidade, time_atual, foto_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtStats  = $pdo->prepare("INSERT INTO estatisticas (jogador_id, partidas, vitorias, derrotas, taxa_vitoria_pct, kda_ratio, abates_media, mortes_media, assistencias_media, cs_por_minuto, dpm, visao_score, campeao_favorito, nota_media) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($playersData as $p) {
        $wr = round(($p['vitorias'] / $p['partidas']) * 100, 1);
        $stmtPlayer->execute([
            $p['nick'], $p['nome_real'], $p['rota'], $p['tier_elo'],
            $p['nacionalidade'], $p['time_atual'], null
        ]);
        $player_id = $pdo->lastInsertId();

        $stmtStats->execute([
            $player_id, $p['partidas'], $p['vitorias'], $p['derrotas'], $wr,
            $p['kda'], $p['abates'], $p['mortes'], $p['assistencias'],
            $p['cs'], $p['dpm'], $p['visao'], $p['campeao'], $p['nota']
        ]);
    }

    echo "✔ Dados iniciais de Pro Players de League of Legends inseridos com sucesso!\n";
}

echo "=== Migração Concluída com Sucesso! ===\n";
