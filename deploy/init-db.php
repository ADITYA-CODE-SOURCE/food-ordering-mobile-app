<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'food_ordering_app';
$username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';
$schemaPath = '/var/www/html/database/food_ordering_startup.sql';

if ($host === '' || $username === '') {
    fwrite(STDERR, "Skipping database init: DB_HOST or DB_USERNAME is empty.\n");
    exit(0);
}

$serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
$databaseDsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

try {
    $serverPdo = new PDO($serverDsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $databasePdo = new PDO($databaseDsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $statement = $databasePdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :database AND table_name = :table'
    );
    $statement->execute([
        'database' => $database,
        'table' => 'users',
    ]);

    if ((int) $statement->fetchColumn() > 0) {
        fwrite(STDOUT, "Database already initialized.\n");
        exit(0);
    }

    $sql = file_get_contents($schemaPath);
    if ($sql === false) {
        throw new RuntimeException("Could not read schema file: {$schemaPath}");
    }

    $databasePdo->exec($sql);
    fwrite(STDOUT, "Database initialized successfully.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Database init failed: {$exception->getMessage()}\n");
    exit(1);
}
