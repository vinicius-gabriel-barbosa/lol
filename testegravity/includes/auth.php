<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function getUserSession() {
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['usuario_id'],
        'nome'   => $_SESSION['usuario_nome'],
        'email'  => $_SESSION['usuario_email'],
        'perfil' => $_SESSION['usuario_perfil']
    ];
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'admin';
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php?msg=necessario_login');
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: dashboard.php?error=acesso_negado');
        exit;
    }
}

function loginUser($email, $senha) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();

    // RNF008: Valida a senha utilizando a função password_verify() do PHP
    if ($user && password_verify($senha, $user['senha_hash'])) {
        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nome']   = $user['nome'];
        $_SESSION['usuario_email']  = $user['email'];
        $_SESSION['usuario_perfil'] = $user['perfil'];
        return true;
    }
    
    return false;
}

function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
