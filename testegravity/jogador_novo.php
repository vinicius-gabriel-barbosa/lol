<?php
// jogador_novo.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nick          = trim($_POST['nick'] ?? '');
    $nome_real     = trim($_POST['nome_real'] ?? '');
    $rota          = $_POST['rota'] ?? 'Mid';
    $tier_elo      = $_POST['tier_elo'] ?? 'Challenger';
    $nacionalidade = trim($_POST['nacionalidade'] ?? 'Brasil');
    $time_atual    = trim($_POST['time_atual'] ?? 'LOUD');
    
    // Estatísticas
    $partidas      = (int)($_POST['partidas'] ?? 0);
    $vitorias      = (int)($_POST['vitorias'] ?? 0);
    $derrotas      = (int)($_POST['derrotas'] ?? 0);
    $abates        = (float)($_POST['abates_media'] ?? 0.0);
    $mortes        = (float)($_POST['mortes_media'] ?? 1.0);
    $assistencias  = (float)($_POST['assistencias_media'] ?? 0.0);
    $cs            = (float)($_POST['cs_por_minuto'] ?? 0.0);
    $dpm           = (float)($_POST['dpm'] ?? 0.0);
    $visao         = (float)($_POST['visao_score'] ?? 0.0);
    $campeao       = trim($_POST['campeao_favorito'] ?? '');
    $nota          = (float)($_POST['nota_media'] ?? 8.0);

    if (empty($nick) || empty($time_atual)) {
        $error = 'Por favor, preencha o Nick e o Time do jogador.';
    } else {
        // Cálculo de KDA e Winrate
        $wr = ($partidas > 0) ? round(($vitorias / $partidas) * 100, 1) : 0.0;
        $mortesDiv = ($mortes > 0) ? $mortes : 1.0;
        $kda = round(($abates + $assistencias) / $mortesDiv, 2);

        try {
            $pdo->beginTransaction();

            $stmtJ = $pdo->prepare("INSERT INTO jogadores (nick, nome_real, rota, tier_elo, nacionalidade, time_atual) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtJ->execute([$nick, $nome_real, $rota, $tier_elo, $nacionalidade, $time_atual]);
            $player_id = $pdo->lastInsertId();

            $stmtE = $pdo->prepare("INSERT INTO estatisticas (jogador_id, partidas, vitorias, derrotas, taxa_vitoria_pct, kda_ratio, abates_media, mortes_media, assistencias_media, cs_por_minuto, dpm, visao_score, campeao_favorito, nota_media) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtE->execute([$player_id, $partidas, $vitorias, $derrotas, $wr, $kda, $abates, $mortes, $assistencias, $cs, $dpm, $visao, $campeao, $nota]);

            $pdo->commit();
            $msg = "Jogador '{$nick}' cadastrado com sucesso! KDA calculado: {$kda}";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erro ao cadastrar jogador: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-lg">
    <h1 class="page-header__title">
        ➕ Cadastro Manual de Jogadores (RF003)
    </h1>
    <p class="page-header__subtitle">
        Insira os dados do atleta pro ou ranked de League of Legends para inclusão no banco de dados SQL.
    </p>
</div>

<?php if ($msg): ?>
    <div class="alert alert--success">
        ✅ <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert--error">
        ⚠️ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card card--form">
    <form method="POST" action="jogador_novo.php">
        <h3 class="card-title">👤 Perfil do Jogador</h3>
        <div class="grid-2">
            <div class="form-group">
                <label for="nick">Nick do Atleta (In-Game Name) *</label>
                <input type="text" id="nick" name="nick" class="form-control" placeholder="Ex: Faker" required>
            </div>
            <div class="form-group">
                <label for="nome_real">Nome Real</label>
                <input type="text" id="nome_real" name="nome_real" class="form-control" placeholder="Ex: Lee Sang-hyeok">
            </div>
            <div class="form-group">
                <label for="rota">Rota / Posição</label>
                <select id="rota" name="rota" class="form-control" required>
                    <option value="Top">Top (Topo)</option>
                    <option value="Jungle">Jungle (Caçador)</option>
                    <option value="Mid" selected>Mid (Meio)</option>
                    <option value="ADC">ADC (Atirador)</option>
                    <option value="Support">Support (Suporte)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tier_elo">Tier / Elo Atual</label>
                <select id="tier_elo" name="tier_elo" class="form-control" required>
                    <option value="Challenger" selected>Challenger</option>
                    <option value="Grandmaster">Grandmaster</option>
                    <option value="Master">Master</option>
                    <option value="Diamond">Diamond</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nacionalidade">Nacionalidade / País</label>
                <input type="text" id="nacionalidade" name="nacionalidade" class="form-control" placeholder="Ex: Coreia do Sul, Brasil, China" value="Brasil" required>
            </div>
            <div class="form-group">
                <label for="time_atual">Time / Organização *</label>
                <input type="text" id="time_atual" name="time_atual" class="form-control" placeholder="Ex: T1, LOUD, G2" required>
            </div>
        </div>

        <h3 class="section-title">📊 Estatísticas Iniciais</h3>
        <div class="grid-3">
            <div class="form-group">
                <label for="partidas">Total Partidas</label>
                <input type="number" id="partidas" name="partidas" class="form-control" value="50" min="0">
            </div>
            <div class="form-group">
                <label for="vitorias">Vitórias</label>
                <input type="number" id="vitorias" name="vitorias" class="form-control" value="35" min="0">
            </div>
            <div class="form-group">
                <label for="derrotas">Derrotas</label>
                <input type="number" id="derrotas" name="derrotas" class="form-control" value="15" min="0">
            </div>
            <div class="form-group">
                <label for="abates_media">Abates Médios (Kills)</label>
                <input type="number" step="0.1" id="abates_media" name="abates_media" class="form-control" value="5.5">
            </div>
            <div class="form-group">
                <label for="mortes_media">Mortes Médias (Deaths)</label>
                <input type="number" step="0.1" id="mortes_media" name="mortes_media" class="form-control" value="2.0">
            </div>
            <div class="form-group">
                <label for="assistencias_media">Assistências Médias</label>
                <input type="number" step="0.1" id="assistencias_media" name="assistencias_media" class="form-control" value="6.5">
            </div>
            <div class="form-group">
                <label for="cs_por_minuto">CS por Minuto</label>
                <input type="number" step="0.1" id="cs_por_minuto" name="cs_por_minuto" class="form-control" value="9.0">
            </div>
            <div class="form-group">
                <label for="dpm">Dano / Minuto (DPM)</label>
                <input type="number" step="1" id="dpm" name="dpm" class="form-control" value="650">
            </div>
            <div class="form-group">
                <label for="campeao_favorito">Campeão Favorito (Main)</label>
                <input type="text" id="campeao_favorito" name="campeao_favorito" class="form-control" placeholder="Ex: Ahri, Aatrox, Lee Sin">
            </div>
        </div>

        <button type="submit" class="btn btn-primary form-submit-btn">
            Salvar Jogador no Banco de Dados
        </button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
