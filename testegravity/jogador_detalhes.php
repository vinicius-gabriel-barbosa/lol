<?php
// jogador_detalhes.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT j.*, e.* FROM jogadores j LEFT JOIN estatisticas e ON j.id = e.jogador_id WHERE j.id = ?");
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) {
    header('Location: jogadores.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-lg">
    <a href="jogadores.php" class="breadcrumb">← Voltar para lista de jogadores</a>
</div>

<!-- Header do Jogador -->
<div class="card player-profile-header mb-lg">
    <div class="player-profile-header__content">
        <div class="player-profile-header__info">
            <div class="player-profile-header__avatar player-profile-header__avatar--lg">
                <?= strtoupper(substr($player['nick'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="player-profile-header__name">
                    <?= htmlspecialchars($player['nick']) ?>
                </h1>
                <div class="player-profile-header__meta">
                    <span class="meta-item"><?= htmlspecialchars($player['nome_real'] ?: 'Atleta Pro') ?></span>
                    <span class="badge badge-<?= strtolower($player['rota']) ?>"><?= htmlspecialchars($player['rota']) ?></span>
                    <span class="badge badge-<?= strtolower($player['tier_elo']) ?>"><?= htmlspecialchars($player['tier_elo']) ?></span>
                    <span class="player-profile-header__team">🚩 <?= htmlspecialchars($player['time_atual']) ?> (<?= htmlspecialchars($player['nacionalidade']) ?>)</span>
                </div>
            </div>
        </div>

        <div class="player-profile-header__score">
            <div class="player-profile-header__score-label">Nota do Atleta</div>
            <div class="player-profile-header__score-value">
                <?= number_format($player['nota_media'], 1) ?>
            </div>
        </div>
    </div>
</div>

<!-- Métricas de Estatísticas Detalhadas (RF008) -->
<div class="grid-4 mb-lg">
    <div class="card kpi-card">
        <div class="kpi-card__label">KDA Ratio</div>
        <div class="kpi-card__value kpi-card__value--success">
            <?= number_format($player['kda_ratio'], 1) ?>
        </div>
        <div class="kpi-card__desc">
            <?= number_format($player['abates_media'], 1) ?> K / <?= number_format($player['mortes_media'], 1) ?> D / <?= number_format($player['assistencias_media'], 1) ?> A
        </div>
    </div>

    <div class="card kpi-card">
        <div class="kpi-card__label">Win Rate %</div>
        <div class="kpi-card__value kpi-card__value--cyan">
            <?= number_format($player['taxa_vitoria_pct'], 1) ?>%
        </div>
        <div class="kpi-card__desc">
            <?= $player['vitorias'] ?> Vitórias / <?= $player['derrotas'] ?> Derrotas (<?= $player['partidas'] ?> jogos)
        </div>
    </div>

    <div class="card kpi-card">
        <div class="kpi-card__label">Farm & Visão</div>
        <div class="kpi-card__value kpi-card__value--gold">
            <?= number_format($player['cs_por_minuto'], 1) ?> <span class="kpi-card__desc">CS/min</span>
        </div>
        <div class="kpi-card__desc">
            Visão Score: <?= number_format($player['visao_score'], 1) ?> pts
        </div>
    </div>

    <div class="card kpi-card">
        <div class="kpi-card__label">Dano por Minuto (DPM)</div>
        <div class="kpi-card__value kpi-card__value--danger">
            <?= number_format($player['dpm'], 0) ?>
        </div>
        <div class="kpi-card__desc">
            Main: <strong><?= htmlspecialchars($player['campeao_favorito'] ?? 'N/A') ?></strong>
        </div>
    </div>
</div>

<!-- Gráficos Visuais em Canvas -->
<div class="grid-2">
    <div class="card chart-card">
        <h3 class="card-title">📈 Estatísticas Médias por Partida</h3>
        <canvas id="playerBarCanvas" width="400" height="240"></canvas>
    </div>

    <div class="card chart-card chart-card--centered">
        <h3 class="card-title w-full">🎯 Taxa de Vitória</h3>
        <canvas id="playerDonutCanvas" width="220" height="220"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.RecallCharts) {
        // Gráfico de barras de atributos do jogador
        window.RecallCharts.renderBarChart(
            'playerBarCanvas',
            ['Abates (K)', 'Mortes (D)', 'Assist. (A)', 'CS/Min', 'Visão'],
            [
                <?= (float)$player['abates_media'] ?>,
                <?= (float)$player['mortes_media'] ?>,
                <?= (float)$player['assistencias_media'] ?>,
                <?= (float)$player['cs_por_minuto'] ?>,
                <?= (float)($player['visao_score'] / 5) ?>
            ],
            ['#10b981', '#ff4655', '#38bdf8', '#c8aa6e', '#0ac8b9']
        );

        // Donut de Winrate
        window.RecallCharts.renderDonutChart('playerDonutCanvas', <?= (float)$player['taxa_vitoria_pct'] ?>, 'Win Rate');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
