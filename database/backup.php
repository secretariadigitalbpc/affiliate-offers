<?php

declare(strict_types=1);

use App\Services\DatabaseBackupService;

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    $result = (new DatabaseBackupService(dirname(__DIR__)))->create();
    echo "Backup criado com sucesso.\n";
    echo 'Arquivo: ' . $result['dump'] . "\n";
    echo 'Manifesto: ' . $result['manifest'] . "\n";
    echo 'SHA-256: ' . $result['sha256'] . "\n";
    echo 'Tabelas: ' . count($result['tables']) . "\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Falha no backup: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
