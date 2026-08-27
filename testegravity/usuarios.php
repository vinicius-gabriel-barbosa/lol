<?php
// usuarios.php
require_once __DIR__ . '/includes/auth.php';
requireAdmin(); // Apenas administradores podem cadastrar e gerenciar usuários (RF012)

$pdo = getDbConnection();
$msg = '';
$error = '';

// Inserir novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';
    $perfil = $_POST['perfil'] ?? 'user';

    if (empty($nome) || empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        // RNF007: Utiliza a função password_hash() do PHP para gerar o hash das senhas
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senhaHash, $perfil]);
            $msg = "Usuário '{$nome}' cadastrado com sucesso!";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                $error = 'Este e-mail já está cadastrado no sistema.';
            } else {
                $error = 'Erro ao cadastrar usuário: ' . $e->getMessage();
            }
        }
    }
}

// Excluir usuário
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $current = getUserSession();
    
    if ($deleteId === $current['id']) {
        $error = 'Você não pode excluir sua própria conta de administrador.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$deleteId]);
        $msg = 'Usuário removido com sucesso.';
    }
}

// Buscar todos os usuários
$usuarios = $pdo->query("SELECT id, nome, email, perfil, criado_em FROM usuarios ORDER BY id DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-header__title">
        ⚙️ Controle & Gerenciamento de Usuários (RF002)
    </h1>
    <p class="page-header__subtitle">
        Área restrita para <strong>Administradores (RF012)</strong>. Cadastre e gerencie o controle de acesso à plataforma.
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

<div class="grid-2">
    <!-- Formulário de Cadastro de Novo Usuário (Apenas Admin) -->
    <div class="card">
        <h3 class="card-title">➕ Cadastrar Novo Usuário</h3>
        
        <form method="POST" action="usuarios.php">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: Lucas Silva" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail de Acesso</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="usuario@recall.gg" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha de Acesso</label>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                <small class="text-muted text-sm">A senha será criptografada no BD com <code>password_hash()</code> (RNF007).</small>
            </div>

            <div class="form-group">
                <label for="perfil">Perfil de Permissão (RF011)</label>
                <select id="perfil" name="perfil" class="form-control" required>
                    <option value="user">Usuário Comum (Analista)</option>
                    <option value="admin">Administrador (Acesso Total)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-full mt-3">
                Salvar Novo Usuário
            </button>
        </form>
    </div>

    <!-- Tabela de Usuários Cadastrados -->
    <div class="card">
        <h3 class="card-title">👥 Usuários do Sistema (<?= count($usuarios) ?>)</h3>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome / E-mail</th>
                        <th>Perfil</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($u['nome']) ?></strong><br>
                                <span class="text-muted text-sm"><?= htmlspecialchars($u['email']) ?></span>
                            </td>
                            <td>
                                <?php if ($u['perfil'] === 'admin'): ?>
                                    <span class="badge badge--admin">👑 Admin</span>
                                <?php else: ?>
                                    <span class="badge badge--user">👤 Comum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['id'] !== $user['id']): ?>
                                    <a href="usuarios.php?delete=<?= $u['id'] ?>" class="btn btn-danger btn-confirm-delete btn--sm">
                                        Excluir
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted text-sm">(Você)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
