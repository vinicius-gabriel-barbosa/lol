<?php
// ranking.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();

// 1. Ranking de Jogadores (RF009)
$rankingJogadores = $pdo->query("
    SELECT j.id, j.nick, j.rota, j.tier_elo, j.time_atual, j.nacionalidade, 
           e.kda_ratio, e.taxa_vitoria_pct, e.abates_media, e.nota_media, e.campeao_favorito,
           (e.kda_ratio * 10 + e.taxa_vitoria_pct + e.nota_media * 5) as score_calculado
    FROM jogadores j 
    JOIN estatisticas e ON j.id = e.jogador_id 
    ORDER BY score_calculado DESC
")->fetchAll();

// 2. Ranking de Países com Melhores Estatísticas (RF013 - Requisito Diferencial!)
$rankingPaises = $pdo->query("
    SELECT j.nacionalidade, 
           COUNT(j.id) as total_jogadores,
           ROUND(AVG(e.taxa_vitoria_pct), 1) as media_winrate,
           ROUND(AVG(e.kda_ratio), 2) as media_kda,
           ROUND(AVG(e.nota_media), 2) as media_nota,
           ROUND(AVG(e.dpm), 0) as media_dpm,
           SUM(e.vitorias) as total_vitorias
    FROM jogadores j
    JOIN estatisticas e ON j.id = e.jogador_id
    GROUP BY j.nacionalidade
    ORDER BY media_kda DESC, media_winrate DESC
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-lg">
    <h1 class="page-header__title">
        🏆 Rankings Gerais e de Desempenho (RF009, RF013)
    </h1>
    <p class="page-header__subtitle">
        Classificação geral calculada com base no desempenho individual de atletas e **Ranking de Países dominantes (RF013)**.
    </p>
</div>

<!-- Requisito Diferencial RF013: Tabela com Ranking dos Países com Melhores Estatísticas -->
<div class="card card--featured mb-xl">
    <div class="card-header">
        <h2 class="card-title text-gold mb-0">
            🌎 RF013: Ranking de Países com Melhores Estatísticas
        </h2>
        <span class="badge badge--featured">Diferencial RF013</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>País / Região</th>
                    <th>Pro Players</th>
                    <th>Média Win Rate %</th>
                    <th>Média KDA Ratio</th>
                    <th>Média DPM (Dano)</th>
                    <th>Nota Média</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankingPaises as $idx => $pais): ?>
                    <tr>
                        <td class="rank-position <?= ($idx === 0) ? 'rank-position--gold' : (($idx === 1) ? 'rank-position--silver' : (($idx === 2) ? 'rank-position--bronze' : 'rank-position--default')) ?>">
                            #<?= ($idx + 1) ?>
                        </td>
                        <td class="country-name">
                            🚩 <?= htmlspecialchars($pais['nacionalidade']) ?>
                        </td>
                        <td><strong><?= $pais['total_jogadores'] ?></strong> atletas</td>
                        <td class="text-cyan fw-bold"><?= $pais['media_winrate'] ?>%</td>
                        <td class="text-success fw-bold"><?= $pais['media_kda'] ?></td>
                        <td><?= number_format($pais['media_dpm']) ?></td>
                        <td><span class="score-badge"><?= $pais['media_nota'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- RF009: Ranking de Jogadores -->
<div class="card">
    <h3 class="card-title">🎖️ Ranking Global de Pro Players (RF009)</h3>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Nick</th>
                    <th>Rota</th>
                    <th>Elo</th>
                    <th>Time</th>
                    <th>País</th>
                    <th>KDA Ratio</th>
                    <th>Win Rate %</th>
                    <th>Score Geral</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankingJogadores as $idx => $j): ?>
                    <tr>
                        <td class="rank-position <?= ($idx === 0) ? 'rank-position--gold' : (($idx === 1) ? 'rank-position--silver' : (($idx === 2) ? 'rank-position--bronze' : 'rank-position--default')) ?>">
                            #<?= ($idx + 1) ?>
                        </td>
                        <td>
                            <strong class="player-name"><?= htmlspecialchars($j['nick']) ?></strong><br>
                            <span class="text-muted meta-item"><?= htmlspecialchars($j['campeao_favorito'] ?? 'N/A') ?></span>
                        </td>
                        <td><span class="badge badge-<?= strtolower($j['rota']) ?>"><?= htmlspecialchars($j['rota']) ?></span></td>
                        <td><span class="badge badge-<?= strtolower($j['tier_elo']) ?>"><?= htmlspecialchars($j['tier_elo']) ?></span></td>
                        <td><strong><?= htmlspecialchars($j['time_atual']) ?></strong></td>
                        <td><?= htmlspecialchars($j['nacionalidade']) ?></td>
                        <td class="text-success fw-bold"><?= number_format($j['kda_ratio'], 1) ?></td>
                        <td class="text-cyan fw-bold"><?= number_format($j['taxa_vitoria_pct'], 1) ?>%</td>
                        <td><strong class="text-gold fw-bold"><?= number_format($j['score_calculado'], 1) ?> pts</strong></td>
                        <td>
                            <a href="jogador_detalhes.php?id=<?= $j['id'] ?>" class="btn btn-secondary btn--sm">Ficha</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
