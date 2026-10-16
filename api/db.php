<?php
// /api/db.php
declare(strict_types=1);

function db(): PDO {
    $host = getenv('DB_HOST') ?: 'localhost';
    $name = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    try {
        if (!$name || !$user || !$pass) {
            throw new RuntimeException('Configuración de base de datos incompleta');
        }
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'DB_CONNECTION_FAILED',
            'message' => 'No se pudo conectar a la base de datos'
        ]);
        exit;
    }
}
