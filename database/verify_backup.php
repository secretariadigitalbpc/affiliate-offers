<?php

declare(strict_types=1);

use App\Services\DatabaseBackupService;

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$dumpPath = $argv[1] ?? '';

if ($dumpPath === '') {
    fwrite(STDERR, "Uso: php database/verify_backup.php storage/backups/arquivo.sql\n");
    exit(1);
}

try {
    $result = (new DatabaseBackupService(dirname(__DIR__)))->verify($dumpPath);
    echo "Backup restaurado e validado com sucesso.\n";
    echo 'Banco temporário: ' . $result['database'] . "\n";
    echo 'Tabelas comparadas: ' . count($result['tables']) . "\n";
    echo 'Banco temporário removido: ' . ($result['dropped'] ? 'sim' : 'não') . "\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Falha na verificação: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
