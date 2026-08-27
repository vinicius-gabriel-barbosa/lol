<?php
// dashboard.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();
$user = getUserSession();

// Consultas agregadas de estatísticas de LoL
$totalJogadores = $pdo->query("SELECT COUNT(*) FROM jogadores")->fetchColumn();
$totalPartidas  = $pdo->query("SELECT SUM(partidas) FROM estatisticas")->fetchColumn() ?: 0;
$avgWinRate     = round($pdo->query("SELECT AVG(taxa_vitoria_pct) FROM estatisticas")->fetchColumn() ?: 0, 1);
$topKdaPlayer   = $pdo->query("SELECT j.nick, e.kda_ratio FROM jogadores j JOIN estatisticas e ON j.id = e.jogador_id ORDER BY e.kda_ratio DESC LIMIT 1")->fetch();
$topScorer      = $pdo->query("SELECT j.nick, e.abates_media, e.campeao_favorito FROM jogadores j JOIN estatisticas e ON j.id = e.jogador_id ORDER BY e.abates_media DESC LIMIT 1")->fetch();

// Contagem por Rota
$rotasCount = $pdo->query("SELECT rota, COUNT(*) as qtd FROM jogadores GROUP BY rota")->fetchAll();

// Top 5 Jogadores por KDA
$topPlayers = $pdo->query("SELECT j.id, j.nick, j.rota, j.tier_elo, j.time_atual, j.nacionalidade, e.kda_ratio, e.taxa_vitoria_pct, e.campeao_favorito FROM jogadores j JOIN estatisticas e ON j.id = e.jogador_id ORDER BY e.kda_ratio DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Banner de Boas-Vindas (Igual Mockup) -->
<div class="welcome-banner">
    <h1 class="welcome-banner__title">
        Olá, <?= htmlspecialchars($user['nome']) ?>! 👋
    </h1>
    <p class="welcome-banner__text">
        Acompanhe estatísticas, metas, builds e siga o caminho competitivo de League of Legends no <strong>Recall.gg</strong>.
    </p>
</div>

<!-- KPIs Resumidos (RF010) -->
<div class="grid-4 mb-lg">
    <div class="card">
        <div class="kpi-card__label">Total Pro Players</div>
        <div class="kpi-card__value kpi-card__value--cyan">
            <?= $totalJogadores ?>
        </div>
        <div class="kpi-card__desc"><?= number_format($totalPartidas) ?> partidas analisadas</div>
    </div>

    <div class="card">
        <div class="kpi-card__label">Taxa Média de Vitória</div>
        <div class="kpi-card__value kpi-card__value--gold">
            <?= $avgWinRate ?>%
        </div>
        <div class="kpi-card__desc">Média geral de winrate dos atletas</div>
    </div>

    <div class="card">
        <div class="kpi-card__label">Maior KDA Ratio</div>
        <div class="kpi-card__value kpi-card__value--success">
            <?= number_format($topKdaPlayer['kda_ratio'] ?? 0, 1) ?>
        </div>
        <div class="kpi-card__desc">Destaque: <strong><?= htmlspecialchars($topKdaPlayer['nick'] ?? 'N/A') ?></strong></div>
    </div>

    <div class="card">
        <div class="kpi-card__label">Maior Média Abates</div>
        <div class="kpi-card__value kpi-card__value--danger">
            <?= number_format($topScorer['abates_media'] ?? 0, 1) ?>
        </div>
        <div class="kpi-card__desc"><?= htmlspecialchars($topScorer['nick'] ?? 'N/A') ?> (<?= htmlspecialchars($topScorer['campeao_favorito'] ?? 'N/A') ?>)</div>
    </div>
</div>

<!-- Layout Principal de Notícias & Meta (Igual ao Mockup enviado) -->
<div class="grid-3 mb-lg">
    <!-- Coluna 1: Notícias Recentes -->
    <div class="card">
        <h3 class="card-title">📰 NOTÍCIAS RECENTES</h3>
        <div class="news-list">
            <div class="news-item">
                <span class="badge badge--cyan news-item__tag">PATCH 14.10</span>
                <h4 class="news-item__title">Patch 14.10: Veja todas as mudanças</h4>
                <p class="news-item__excerpt">Buffs, nerfs e novos itens para a temporada competitiva.</p>
            </div>
            <div class="news-item">
                <span class="badge badge--gold news-item__tag">MSI 2024</span>
                <h4 class="news-item__title">MSI 2024: Times classificados</h4>
                <p class="news-item__excerpt">T1, Gen.G, LOUD e G2 confirmam presença no mundial.</p>
            </div>
        </div>
    </div>

    <!-- Coluna 2: Novo Meta (Patch 14.10) -->
    <div class="card">
        <h3 class="card-title">⚡ NOVO META (PATCH 14.10)</h3>
        <div class="meta-list">
            <div class="meta-item">
                <div>
                    <strong class="meta-item__name">Aatrox</strong> <small class="meta-item__role">(Top)</small>
                </div>
                <div><span class="badge badge--s-tier">Tier S</span> <strong>54.2%</strong></div>
            </div>
            <div class="meta-item">
                <div>
                    <strong class="meta-item__name">K'Sante</strong> <small class="meta-item__role">(Top)</small>
                </div>
                <div><span class="badge badge--a-tier">Tier A</span> <strong>51.2%</strong></div>
            </div>
            <div class="meta-item">
                <div>
                    <strong class="meta-item__name">Ahri</strong> <small class="meta-item__role">(Mid)</small>
                </div>
                <div><span class="badge badge--s-tier">Tier S</span> <strong>55.1%</strong></div>
            </div>
            <div class="meta-item">
                <div>
                    <strong class="meta-item__name">Jinx</strong> <small class="meta-item__role">(ADC)</small>
                </div>
                <div><span class="badge badge--s-tier">Tier S</span> <strong>53.8%</strong></div>
            </div>
        </div>
    </div>

    <!-- Coluna 3: Gráfico de Win Rate / Canvas -->
    <div class="card card--centered">
        <h3 class="card-title w-full">📊 DESEMPENHO DA PLATAFORMA</h3>
        <canvas id="dashboardDonutCanvas" width="220" height="220"></canvas>
    </div>
</div>

<!-- Tabela de Top Pro Players no Dashboard -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">🏆 Top Pro Players Cadastrados</h3>
        <a href="jogadores.php" class="btn btn-secondary btn--sm">Ver Todos os Jogadores →</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nick / Nome Real</th>
                    <th>Rota</th>
                    <th>Elo Tier</th>
                    <th>Time</th>
                    <th>País</th>
                    <th>KDA Ratio</th>
                    <th>Win Rate</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topPlayers as $p): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($p['nick']) ?></strong><br>
                            <span class="text-muted"><?= htmlspecialchars($p['campeao_favorito'] ?? 'N/A') ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?= strtolower($p['rota']) ?>">
                                <?= htmlspecialchars($p['rota']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= strtolower($p['tier_elo']) ?>">
                                <?= htmlspecialchars($p['tier_elo']) ?>
                            </span>
                        </td>
                        <td><strong><?= htmlspecialchars($p['time_atual']) ?></strong></td>
                        <td><?= htmlspecialchars($p['nacionalidade']) ?></td>
                        <td class="text-success fw-bold"><?= number_format($p['kda_ratio'], 1) ?></td>
                        <td class="text-cyan fw-bold"><?= number_format($p['taxa_vitoria_pct'], 1) ?>%</td>
                        <td>
                            <a href="jogador_detalhes.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn--sm">
                                Ver Ficha
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.RecallCharts) {
        window.RecallCharts.renderDonutChart('dashboardDonutCanvas', <?= $avgWinRate ?>, 'Win Rate Média');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
