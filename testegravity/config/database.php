<?php
// config/database.php

define('DB_DIR', __DIR__ . '/../database');
define('DB_PATH', DB_DIR . '/database.sqlite');

function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        if (!file_exists(DB_DIR)) {
            mkdir(DB_DIR, 0777, true);
        }

        try {
            // Usa SQLite via PDO por padrão (100% offline, zero dependência de servidor MySQL externo - RNF001)
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("PRAGMA foreign_keys = ON;");
        } catch (PDOException $e) {
            die("Erro de conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
