<?php
// importacao.php
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pdo = getDbConnection();
$msg = '';
$error = '';
$importedCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['planilha'])) {
    $file = $_FILES['planilha'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Erro no envio do arquivo. Por favor, tente novamente.';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'txt'])) {
            $error = 'Formato de arquivo não suportado. Por favor, envie um arquivo .CSV (ou exportado do Excel).';
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle !== false) {
                // Lê cabeçalho
                $header = fgetcsv($handle, 1000, ',');

                try {
                    $pdo->beginTransaction();

                    $stmtJ = $pdo->prepare("INSERT INTO jogadores (nick, nome_real, rota, tier_elo, nacionalidade, time_atual) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtE = $pdo->prepare("INSERT INTO estatisticas (jogador_id, partidas, vitorias, derrotas, taxa_vitoria_pct, kda_ratio, abates_media, mortes_media, assistencias_media, cs_por_minuto, dpm, visao_score, campeao_favorito, nota_media) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        if (count($data) < 6) continue;

                        $nick          = trim($data[0]);
                        $nome_real     = trim($data[1] ?? '');
                        $rota          = trim($data[2] ?? 'Mid');
                        $tier_elo      = trim($data[3] ?? 'Challenger');
                        $nacionalidade = trim($data[4] ?? 'Brasil');
                        $time_atual    = trim($data[5] ?? 'LOUD');
                        
                        $partidas      = (int)($data[6] ?? 50);
                        $vitorias      = (int)($data[7] ?? 30);
                        $derrotas      = (int)($data[8] ?? 20);
                        $abates        = (float)($data[9] ?? 5.0);
                        $mortes        = (float)($data[10] ?? 2.0);
                        $assistencias  = (float)($data[11] ?? 6.0);
                        $cs            = (float)($data[12] ?? 8.5);
                        $dpm           = (float)($data[13] ?? 600.0);
                        $visao         = (float)($data[14] ?? 30.0);
                        $campeao       = trim($data[15] ?? 'Ahri');
                        $nota          = (float)($data[16] ?? 8.5);

                        $wr = ($partidas > 0) ? round(($vitorias / $partidas) * 100, 1) : 0.0;
                        $mortesDiv = ($mortes > 0) ? $mortes : 1.0;
                        $kda = round(($abates + $assistencias) / $mortesDiv, 2);

                        // Inserir Jogador
                        $stmtJ->execute([$nick, $nome_real, $rota, $tier_elo, $nacionalidade, $time_atual]);
                        $player_id = $pdo->lastInsertId();

                        // Inserir Estatísticas
                        $stmtE->execute([$player_id, $partidas, $vitorias, $derrotas, $wr, $kda, $abates, $mortes, $assistencias, $cs, $dpm, $visao, $campeao, $nota]);
                        $importedCount++;
                    }

                    $pdo->commit();
                    fclose($handle);
                    $msg = "Sucesso! Total de <strong>{$importedCount} jogadores</strong> importados da planilha para o Banco de Dados SQL.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    fclose($handle);
                    $error = 'Erro ao processar planilha: ' . $e->getMessage();
                }
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-lg">
    <h1 class="page-header__title">
        📥 Importação de Jogadores por Planilha Excel/CSV (RF004)
    </h1>
    <p class="page-header__subtitle">
        Carregue um arquivo de planilha (.csv) contendo listas de atletas pro ou dados competitivos para popular automaticamente o banco de dados.
    </p>
</div>

<?php if ($msg): ?>
    <div class="alert alert--success">
        ✅ <?= $msg ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert--error">
        ⚠️ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="grid-2">
    <!-- Card de Upload da Planilha -->
    <div class="card">
        <h3 class="card-title">📄 Upload do Arquivo (.CSV ou .XLSX)</h3>
        
        <form method="POST" action="importacao.php" enctype="multipart/form-data">
            <div class="form-group upload-dropzone">
                <div class="upload-dropzone__icon">📁</div>
                <label for="planilha" class="upload-dropzone__label">
                    Clique para selecionar a planilha no computador
                </label>
                <p class="upload-dropzone__hint">
                    Formatos aceitos: <code>.csv</code> (exportado do Microsoft Excel ou Google Sheets).
                </p>
                <input type="file" id="planilha" name="planilha" accept=".csv" required class="upload-dropzone__input" onchange="document.getElementById('fileNameSpan').innerText = this.files[0] ? this.files[0].name : '';">
                <div id="fileNameSpan" class="upload-dropzone__filename"></div>
            </div>

            <button type="submit" class="btn btn-primary btn--full">
                🚀 Processar e Importar para o Banco SQL
            </button>
        </form>
    </div>

    <!-- Instructions & Template Download Card -->
    <div class="card instructions-card">
        <h3 class="card-title">💡 Instruções & Modelo Exemplo</h3>
        <p class="instructions-card__desc">
            A planilha deve conter colunas separadas por vírgula no seguinte formato:
        </p>

        <ul class="instructions-card__list">
            <li><code>nick</code>, <code>nome_real</code>, <code>rota</code>, <code>tier_elo</code></li>
            <li><code>nacionalidade</code>, <code>time_atual</code>, <code>partidas</code></li>
            <li><code>vitorias</code>, <code>derrotas</code>, <code>abates_media</code></li>
            <li><code>mortes_media</code>, <code>assistencias_media</code>, <code>cs_por_minuto</code></li>
            <li><code>dpm</code>, <code>visao_score</code>, <code>campeao_favorito</code>, <code>nota_media</code></li>
        </ul>

        <div class="template-download">
            <div class="template-download__title">📥 Baixar Planilha Exemplo Pronta:</div>
            <p class="template-download__text">
                Baixe o arquivo de demonstração pré-configurado com atletas como ShowMaker, Viper e Bwipo para testar a importação instantaneamente.
            </p>
            <a href="sample_lol_players.csv" download class="btn btn-secondary template-download__btn">
                💾 Download sample_lol_players.csv
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
