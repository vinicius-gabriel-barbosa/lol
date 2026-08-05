<?php
// ============================================================
// teste_conexao.php — Script rápido para validar seu banco
// ============================================================

$host = 'localhost';
$db   = 'estatisticas_esportivas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexão OK\n\n";

    // 1) Testar inserção de perfil
    $pdo->exec("INSERT INTO perfis (nome, descricao) VALUES ('Administrador', 'Acesso total')");
    echo "✅ Perfil inserido\n";

    // 2) Testar inserção de usuário com hash
    $hash = password_hash('senha123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil_id) VALUES (?, ?, ?, ?)");
    $stmt->execute(['João Silva', 'joao@teste.com', $hash, 1]);
    echo "✅ Usuário inserido (hash: " . substr($hash, 0, 20) . "...)\n";

    // 3) Verificar hash
    $stmt = $pdo->query("SELECT senha_hash FROM usuarios WHERE email='joao@teste.com'");
    $row = $stmt->fetch();
    echo password_verify('senha123', $row['senha_hash']) ? "✅ Hash verificado com sucesso\n" : "❌ Hash falhou\n";

    // 4) Testar jogador + estatísticas
    $pdo->exec("INSERT INTO jogadores (nome, posicao, time_atual) VALUES ('Pedro', 'Atacante', 'Time A')");
    $jogadorId = $pdo->lastInsertId();
    echo "✅ Jogador inserido (ID: $jogadorId)\n";

    $stmt = $pdo->prepare("INSERT INTO estatisticas (jogador_id, temporada, jogos_disputados, metrica_principal, media_geral) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$jogadorId, '2025', 20, 15.50, 8.75]);
    echo "✅ Estatísticas inseridas\n";

    // 5) Consulta JOIN
    $stmt = $pdo->query("
        SELECT j.nome, j.posicao, e.temporada, e.media_geral
        FROM jogadores j
        LEFT JOIN estatisticas e ON j.id = e.jogador_id
        WHERE j.ativo = 1
    ");
    echo "\n📊 Jogadores ativos com estatísticas:\n";
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  • {$r['nome']} ({$r['posicao']}) — {$r['temporada']}: {$r['media_geral']}\n";
    }

    // 6) Testar log de importação
    $stmt = $pdo->prepare("INSERT INTO logs_importacao (usuario_id, nome_arquivo, qtd_registros, status, mensagem) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([1, 'jogadores.xlsx', 42, 'sucesso', 'Importação concluída']);
    echo "\n✅ Log de importação registrado\n";

    // 7) Ranking simples (top 5)
    echo "\n🏆 Top jogadores por média geral:\n";
    $stmt = $pdo->query("
        SELECT j.nome, e.media_geral
        FROM jogadores j
        JOIN estatisticas e ON j.id = e.jogador_id
        ORDER BY e.media_geral DESC
        LIMIT 5
    ");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  • {$r['nome']}: {$r['media_geral']}\n";
    }

} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
