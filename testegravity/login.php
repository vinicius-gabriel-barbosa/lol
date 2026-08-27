<?php
// login.php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Por favor, preencha o e-mail e a senha.';
    } else {
        if (loginUser($email, $senha)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'E-mail ou senha incorretos!';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="login-page">
    <div class="login-container">
        
        <!-- Lado Esquerdo: Callout Recall (Estilo do Mockup enviado) -->
        <div class="login-hero">
            <div class="brand-logo brand-logo--lg">
                Recall<span>.gg</span>
            </div>
            
            <h1 class="login-hero__title">
                Mais de 75% de jogadores profissionais de LOL utilizam a plataforma hoje em dia!
            </h1>
            
            <p class="login-hero__subtitle">
                Venha fazer esse número aumentar!
            </p>

            <div class="login-hero__endorsement">
                <p class="text-muted mb-0">
                    ⭐ Recomendado por analistas e jogadores profissionais como <strong>FAKER</strong>, <strong>CHOVY</strong> e <strong>TINOWNS</strong>.
                </p>
            </div>
        </div>

        <!-- Lado Direito: Form de Login (RF001, RNF006, RNF008) -->
        <div class="login-form-panel">
            
            <h2 class="login-form-panel__title">
                Acessar Plataforma
            </h2>
            <p class="login-form-panel__desc">
                Entre com suas credenciais para visualizar rankings e estatísticas.
            </p>

            <?php if ($error): ?>
                <div class="alert alert--error">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">E-mail de Usuário</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@sistema.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn--full btn--lg">
                    Fazer Login
                </button>
            </form>

            <!-- Box com Acesso Rápido para Teste -->
            <div class="login-creds">
                <div class="login-creds__title">🔑 Credenciais de Teste Rápidas:</div>
                <div class="login-creds__row">
                    <span>👑 Admin: <code>admin@sistema.com</code></span>
                    <span>Senha: <code>admin123</code></span>
                </div>
                <div class="login-creds__row">
                    <span>👤 User: <code>user@sistema.com</code></span>
                    <span>Senha: <code>user123</code></span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
