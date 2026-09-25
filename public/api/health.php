<?php

declare(strict_types=1);

use App\Config\Database;

header('Content-Type: application/json; charset=utf-8');

try {
    require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

    $pdo = Database::connect();
    $statement = $pdo->prepare('SELECT 1');
    $statement->execute();

    http_response_code(200);
    echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    http_response_code(503);
    echo json_encode(
        ['status' => 'error'],
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );
}

