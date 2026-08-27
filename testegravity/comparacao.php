<?php
// comparacao.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();

// Lista todos os jogadores para os dropdowns de seleção
$allPlayers = $pdo->query("SELECT id, nick, rota, time_atual FROM jogadores ORDER BY nick ASC")->fetchAll();

$p1_id = (int)($_GET['p1'] ?? ($allPlayers[0]['id'] ?? 0));
$p2_id = (int)($_GET['p2'] ?? ($allPlayers[1]['id'] ?? 0));

$player1 = null;
$player2 = null;

if ($p1_id) {
    $stmt = $pdo->prepare("SELECT j.*, e.* FROM jogadores j LEFT JOIN estatisticas e ON j.id = e.jogador_id WHERE j.id = ?");
    $stmt->execute([$p1_id]);
    $player1 = $stmt->fetch();
}

if ($p2_id) {
    $stmt = $pdo->prepare("SELECT j.*, e.* FROM jogadores j LEFT JOIN estatisticas e ON j.id = e.jogador_id WHERE j.id = ?");
    $stmt->execute([$p2_id]);
    $player2 = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-lg">
    <h1 class="page-header__title">
        ⚔️ Comparador Lado a Lado de Pro Players
    </h1>
    <p class="page-header__subtitle">
        Selecione dois atletas para comparar métricas de KDA, farm, taxa de vitória e dano por minuto em tempo real.
    </p>
</div>

<!-- Seletor de Jogadores -->
<div class="card compare-selector mb-lg">
    <form method="GET" action="comparacao.php" class="grid-2">
        <div class="form-group mb-0">
            <label for="p1">🔵 Jogador 1 (Azul)</label>
            <select id="p1" name="p1" class="form-control" onchange="this.form.submit()">
                <?php foreach ($allPlayers as $pl): ?>
                    <option value="<?= $pl['id'] ?>" <?= ($p1_id === (int)$pl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['nick']) ?> (<?= htmlspecialchars($pl['rota']) ?> - <?= htmlspecialchars($pl['time_atual']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-0">
            <label for="p2">🔴 Jogador 2 (Vermelho)</label>
            <select id="p2" name="p2" class="form-control" onchange="this.form.submit()">
                <?php foreach ($allPlayers as $pl): ?>
                    <option value="<?= $pl['id'] ?>" <?= ($p2_id === (int)$pl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['nick']) ?> (<?= htmlspecialchars($pl['rota']) ?> - <?= htmlspecialchars($pl['time_atual']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($player1 && $player2): ?>
<div class="grid-2 mb-lg">
    <!-- Card Jogador 1 -->
    <div class="card compare-card compare-card--blue">
        <div class="compare-card__header">
            <div class="compare-card__avatar compare-card__avatar--blue">
                <?= strtoupper(substr($player1['nick'], 0, 1)) ?>
            </div>
            <div>
                <h2 class="compare-card__name">
                    <?= htmlspecialchars($player1['nick']) ?>
                </h2>
                <div class="compare-card__meta">
                    <span class="badge badge-<?= strtolower($player1['rota']) ?>"><?= htmlspecialchars($player1['rota']) ?></span>
                    <span class="text-muted"><?= htmlspecialchars($player1['time_atual']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Jogador 2 -->
    <div class="card compare-card compare-card--red">
        <div class="compare-card__header">
            <div class="compare-card__avatar compare-card__avatar--red">
                <?= strtoupper(substr($player2['nick'], 0, 1)) ?>
            </div>
            <div>
                <h2 class="compare-card__name">
                    <?= htmlspecialchars($player2['nick']) ?>
                </h2>
                <div class="compare-card__meta">
                    <span class="badge badge-<?= strtolower($player2['rota']) ?>"><?= htmlspecialchars($player2['rota']) ?></span>
                    <span class="text-muted"><?= htmlspecialchars($player2['time_atual']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Comparação Direct Head-to-Head -->
<div class="card">
    <h3 class="card-title">📊 Comparativo Direto de Estatísticas</h3>

    <?php
    $metrics = [
        ['label' => 'KDA Ratio', 'v1' => $player1['kda_ratio'], 'v2' => $player2['kda_ratio'], 'fmt' => 'float'],
        ['label' => 'Win Rate %', 'v1' => $player1['taxa_vitoria_pct'], 'v2' => $player2['taxa_vitoria_pct'], 'fmt' => 'pct'],
        ['label' => 'Abates Médios (Kills)', 'v1' => $player1['abates_media'], 'v2' => $player2['abates_media'], 'fmt' => 'float'],
        ['label' => 'Mortes Médias (Deaths - Menor é Melhor)', 'v1' => $player1['mortes_media'], 'v2' => $player2['mortes_media'], 'fmt' => 'float', 'invert' => true],
        ['label' => 'Assistências Médias', 'v1' => $player1['assistencias_media'], 'v2' => $player2['assistencias_media'], 'fmt' => 'float'],
        ['label' => 'CS por Minuto (Farm)', 'v1' => $player1['cs_por_minuto'], 'v2' => $player2['cs_por_minuto'], 'fmt' => 'float'],
        ['label' => 'Dano / Minuto (DPM)', 'v1' => $player1['dpm'], 'v2' => $player2['dpm'], 'fmt' => 'int'],
        ['label' => 'Visão Score', 'v1' => $player1['visao_score'], 'v2' => $player2['visao_score'], 'fmt' => 'float'],
        ['label' => 'Nota Média do Atleta', 'v1' => $player1['nota_media'], 'v2' => $player2['nota_media'], 'fmt' => 'float']
    ];
    ?>

    <div class="table-responsive">
        <table class="table compare-table">
            <thead>
                <tr>
                    <th class="compare-table__p1"><?= htmlspecialchars($player1['nick']) ?></th>
                    <th class="compare-table__metric">Métrica Estatística</th>
                    <th class="compare-table__p2"><?= htmlspecialchars($player2['nick']) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($metrics as $m): 
                    $v1 = (float)$m['v1'];
                    $v2 = (float)$m['v2'];
                    $invert = $m['invert'] ?? false;
                    
                    $p1Wins = $invert ? ($v1 < $v2) : ($v1 > $v2);
                    $p2Wins = $invert ? ($v2 < $v1) : ($v2 > $v1);

                    $strV1 = ($m['fmt'] === 'pct') ? "{$v1}%" : (($m['fmt'] === 'int') ? number_format($v1) : number_format($v1, 1));
                    $strV2 = ($m['fmt'] === 'pct') ? "{$v2}%" : (($m['fmt'] === 'int') ? number_format($v2) : number_format($v2, 1));
                ?>
                    <tr>
                        <td class="compare-value <?= $p1Wins ? 'compare-value--cyan compare-value--winner' : 'compare-value--muted' ?>">
                            <?= $p1Wins ? '🏆 ' : '' ?><?= $strV1 ?>
                        </td>
                        <td class="compare-table__metric">
                            <?= $m['label'] ?>
                        </td>
                        <td class="compare-value <?= $p2Wins ? 'compare-value--danger compare-value--winner' : 'compare-value--muted' ?>">
                            <?= $strV2 ?><?= $p2Wins ? ' 🏆' : '' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
