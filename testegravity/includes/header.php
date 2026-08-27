<?php
// includes/header.php
require_once __DIR__ . '/auth.php';

$user = getUserSession();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>Recall.gg — League of Legends Analytics & Stats</title>
    <meta name="description" content="Plataforma de estatísticas, rankings, metas e análise competitiva de League of Legends.">
    <meta name="theme-color" content="#06101e">
    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>
<body>
<div class="app-container">
    <?php if ($user): ?>
    <!-- Sidebar Recall Navigation -->
    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="brand-logo">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            Recall<span>.gg</span>
        </a>

        <div class="nav-group-title">Navegação Principal</div>
        <ul class="nav-menu">
            <li>
                <a href="dashboard.php" class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Home / Dashboard
                </a>
            </li>
            <li>
                <a href="jogadores.php" class="nav-link <?= ($currentPage == 'jogadores.php' || $currentPage == 'jogador_detalhes.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Consulta de Jogadores
                </a>
            </li>
            <li>
                <a href="ranking.php" class="nav-link <?= ($currentPage == 'ranking.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    Ranking & Países
                </a>
            </li>
            <li>
                <a href="comparacao.php" class="nav-link <?= ($currentPage == 'comparacao.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Comparador
                </a>
            </li>
            <li>
                <a href="importacao.php" class="nav-link <?= ($currentPage == 'importacao.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Importar Planilha
                </a>
            </li>
            <li>
                <a href="jogador_novo.php" class="nav-link <?= ($currentPage == 'jogador_novo.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Cadastrar Jogador
                </a>
            </li>
        </ul>

        <?php if (isAdmin()): ?>
        <div class="nav-group-title">Administração</div>
        <ul class="nav-menu">
            <li>
                <a href="usuarios.php" class="nav-link <?= ($currentPage == 'usuarios.php') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Controle de Usuários
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <div class="sidebar-bottom">
            <button class="nav-link" id="themeToggleBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                Alternar Tema
            </button>
            <a href="logout.php" class="nav-link text-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Sair
            </a>
        </div>
    </aside>

    <div class="main-wrapper">
        <!-- Top Bar Header -->
        <header class="top-header">
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="globalSearchInput" placeholder="Pesquisar pro-players, rotas, elos...">
            </div>

            <div class="header-counters">
                <div class="counter-badge">🛡️ <strong>168</strong> Jogadores</div>
                <div class="counter-badge">⚔️ <strong>167</strong> Campeões</div>
                <div class="counter-badge">🔮 <strong>130</strong> Runas</div>
                <div class="counter-badge">💎 <strong>41</strong> Itens</div>
            </div>

            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr($user['nome'], 0, 1)) ?></div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['nome']) ?></span>
                    <span class="user-role"><?= htmlspecialchars($user['perfil']) ?></span>
                </div>
            </div>
        </header>

        <main class="content-body">
    <?php endif; ?>
