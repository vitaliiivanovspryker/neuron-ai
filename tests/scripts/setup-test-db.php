<?php

$host = '127.0.0.1';
$port = 5432;
$user = 'postgres';
$password = 'postgres';
$database = 'neuron_ai_test';

$pdo = new PDO("pgsql:host=$host;port=$port", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$stmt = $pdo->query("SELECT datname FROM pg_database WHERE datname = '{$pdo->quote($database)}'");
$exists = $stmt->fetch();

if (!$exists) {
    echo "🛠️  Database '$database' does not exist, creating...\n";
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ Database '$database' created successfully.\n";
} else {
    echo "✅ Database '$database' already exists. Everything is ready.\n";
}
