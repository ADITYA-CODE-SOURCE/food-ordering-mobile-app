<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'foodapp_db';
$username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';
$schemaPath = '/var/www/html/database/food_ordering_startup.sql';
$adminName = trim((string) (getenv('ADMIN_NAME') ?: ''));
$adminEmail = trim((string) (getenv('ADMIN_EMAIL') ?: ''));
$adminPhone = trim((string) (getenv('ADMIN_PHONE') ?: ''));
$adminPassword = (string) (getenv('ADMIN_PASSWORD') ?: '');

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

    $schemaExists = (int) $statement->fetchColumn() > 0;

    if (!$schemaExists) {
        $sql = file_get_contents($schemaPath);
        if ($sql === false) {
            throw new RuntimeException("Could not read schema file: {$schemaPath}");
        }

        $databasePdo->exec($sql);
        fwrite(STDOUT, "Database initialized successfully.\n");
    }

    $hasAdminSeed = $adminEmail !== '' && $adminPassword !== '' && $adminName !== '' && $adminPhone !== '';
    if ($hasAdminSeed) {
        $adminLookup = $databasePdo->prepare('SELECT id FROM users WHERE (role = :role AND status = "active") OR email = :email LIMIT 1');
        $adminLookup->execute([
            'role' => 'admin',
            'email' => $adminEmail,
        ]);

        if ((int) $adminLookup->fetchColumn() === 0) {
            $insertAdmin = $databasePdo->prepare('INSERT INTO users (role, name, email, phone, password, status) VALUES ("admin", :name, :email, :phone, :password, "active")');
            $insertAdmin->execute([
                'name' => $adminName,
                'email' => $adminEmail,
                'phone' => $adminPhone,
                'password' => password_hash($adminPassword, PASSWORD_DEFAULT),
            ]);
            fwrite(STDOUT, "Admin user created successfully.\n");
        }
    }

    if ($schemaExists) {
        fwrite(STDOUT, "Database already initialized.\n");
    }
} catch (Throwable $exception) {
    fwrite(STDERR, "Database init failed: {$exception->getMessage()}\n");
    exit(1);
}
