<?php
// jogadores.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();

// Parâmetros de Filtro (RF006, RF007)
$busca     = trim($_GET['q'] ?? '');
$rota      = trim($_GET['rota'] ?? '');
$elo       = trim($_GET['elo'] ?? '');
$pais      = trim($_GET['pais'] ?? '');
$ordenar   = trim($_GET['ordenar'] ?? 'kda_desc');

// Monta Query SQL Dinâmica com Filtros
$sql = "SELECT j.*, e.partidas, e.vitorias, e.derrotas, e.taxa_vitoria_pct, e.kda_ratio, e.abates_media, e.mortes_media, e.assistencias_media, e.cs_por_minuto, e.dpm, e.campeao_favorito, e.nota_media 
        FROM jogadores j 
        LEFT JOIN estatisticas e ON j.id = e.jogador_id 
        WHERE 1=1";

$params = [];

if ($busca !== '') {
    $sql .= " AND (j.nick LIKE ? OR j.nome_real LIKE ? OR j.time_atual LIKE ? OR e.campeao_favorito LIKE ?)";
    $term = "%{$busca}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($rota !== '') {
    $sql .= " AND j.rota = ?";
    $params[] = $rota;
}

if ($elo !== '') {
    $sql .= " AND j.tier_elo = ?";
    $params[] = $elo;
}

if ($pais !== '') {
    $sql .= " AND j.nacionalidade = ?";
    $params[] = $pais;
}

// Ordenação (RF007)
switch ($ordenar) {
    case 'winrate_desc':
        $sql .= " ORDER BY e.taxa_vitoria_pct DESC";
        break;
    case 'abates_desc':
        $sql .= " ORDER BY e.abates_media DESC";
        break;
    case 'nota_desc':
        $sql .= " ORDER BY e.nota_media DESC";
        break;
    case 'nick_asc':
        $sql .= " ORDER BY j.nick ASC";
        break;
    case 'kda_desc':
    default:
        $sql .= " ORDER BY e.kda_ratio DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jogadores = $stmt->fetchAll();

// Listas para Filtros Select
$paisesList = $pdo->query("SELECT DISTINCT nacionalidade FROM jogadores ORDER BY nacionalidade ASC")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<div class="page-header-row mb-lg">
    <div class="page-header">
        <h1 class="page-header__title">
            🔍 Consulta e Pesquisa de Jogadores (RF005, RF006)
        </h1>
        <p class="page-header__subtitle">
            Filtre pro-players por rota, elo, nacionalidade ou ordene pelas melhores estatísticas.
        </p>
    </div>
    <div class="page-header__actions">
        <a href="jogador_novo.php" class="btn btn-primary">➕ Cadastrar Jogador Manualmente</a>
    </div>
</div>

<!-- Painel de Filtros Avançados (RF007) -->
<div class="card filter-panel mb-lg">
    <form method="GET" action="jogadores.php" class="filter-form">
        
        <div class="form-group mb-0">
            <label for="q">Pesquisa Texto</label>
            <input type="text" id="q" name="q" class="form-control" placeholder="Nick, time, campeão..." value="<?= htmlspecialchars($busca) ?>">
        </div>

        <div class="form-group mb-0">
            <label for="rota">Rota / Posição</label>
            <select id="rota" name="rota" class="form-control">
                <option value="">Todas as Rotas</option>
                <option value="Top" <?= ($rota === 'Top') ? 'selected' : '' ?>>Top (Topo)</option>
                <option value="Jungle" <?= ($rota === 'Jungle') ? 'selected' : '' ?>>Jungle (Caçador)</option>
                <option value="Mid" <?= ($rota === 'Mid') ? 'selected' : '' ?>>Mid (Meio)</option>
                <option value="ADC" <?= ($rota === 'ADC') ? 'selected' : '' ?>>ADC (Atirador)</option>
                <option value="Support" <?= ($rota === 'Support') ? 'selected' : '' ?>>Support (Suporte)</option>
            </select>
        </div>

        <div class="form-group mb-0">
            <label for="elo">Tier / Elo</label>
            <select id="elo" name="elo" class="form-control">
                <option value="">Todos os Elos</option>
                <option value="Challenger" <?= ($elo === 'Challenger') ? 'selected' : '' ?>>Challenger</option>
                <option value="Grandmaster" <?= ($elo === 'Grandmaster') ? 'selected' : '' ?>>Grandmaster</option>
                <option value="Master" <?= ($elo === 'Master') ? 'selected' : '' ?>>Master</option>
            </select>
        </div>

        <div class="form-group mb-0">
            <label for="pais">Nacionalidade</label>
            <select id="pais" name="pais" class="form-control">
                <option value="">Todos os Países</option>
                <?php foreach ($paisesList as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" <?= ($pais === $p) ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-0">
            <label for="ordenar">Ordenar Por</label>
            <select id="ordenar" name="ordenar" class="form-control">
                <option value="kda_desc" <?= ($ordenar === 'kda_desc') ? 'selected' : '' ?>>Maior KDA Ratio</option>
                <option value="winrate_desc" <?= ($ordenar === 'winrate_desc') ? 'selected' : '' ?>>Maior Win Rate %</option>
                <option value="abates_desc" <?= ($ordenar === 'abates_desc') ? 'selected' : '' ?>>Média de Abates (Kills)</option>
                <option value="nota_desc" <?= ($ordenar === 'nota_desc') ? 'selected' : '' ?>>Maior Nota Média</option>
                <option value="nick_asc" <?= ($ordenar === 'nick_asc') ? 'selected' : '' ?>>Nick (A-Z)</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary w-full">Filtrar</button>
            <a href="jogadores.php" class="btn btn-secondary">Limpar</a>
        </div>
    </form>
</div>

<!-- Tabela de Resultados -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">📋 Jogadores Encontrados (<?= count($jogadores) ?>)</h3>
    </div>

    <?php if (empty($jogadores)): ?>
        <div class="empty-state">
            <div class="empty-state__icon">🔍</div>
            <h3 class="empty-state__title">Nenhum jogador encontrado com os filtros selecionados.</h3>
            <p class="empty-state__text">Tente ajustar a busca ou limpe os filtros para visualizar a lista completa.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nick / Nome Real</th>
                        <th>Rota</th>
                        <th>Elo</th>
                        <th>Time</th>
                        <th>País</th>
                        <th>Partidas</th>
                        <th>KDA Ratio</th>
                        <th>Win Rate</th>
                        <th>Nota</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jogadores as $j): ?>
                        <tr class="player-card-item">
                            <td>
                                <strong class="player-nick"><?= htmlspecialchars($j['nick']) ?></strong><br>
                                <span class="player-sub"><?= htmlspecialchars($j['nome_real'] ?: 'N/A') ?></span>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($j['rota']) ?>">
                                    <?= htmlspecialchars($j['rota']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($j['tier_elo']) ?>">
                                    <?= htmlspecialchars($j['tier_elo']) ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($j['time_atual']) ?></strong></td>
                            <td><?= htmlspecialchars($j['nacionalidade']) ?></td>
                            <td><?= $j['partidas'] ?></td>
                            <td class="fw-bold text-success"><?= number_format($j['kda_ratio'], 1) ?></td>
                            <td class="fw-bold text-cyan"><?= number_format($j['taxa_vitoria_pct'], 1) ?>%</td>
                            <td><span class="score-badge"><?= number_format($j['nota_media'], 1) ?></span></td>
                            <td>
                                <a href="jogador_detalhes.php?id=<?= $j['id'] ?>" class="btn btn-secondary btn--sm">
                                    Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
