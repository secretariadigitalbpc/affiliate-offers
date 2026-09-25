<?php

declare(strict_types=1);

use App\Config\Database;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$lockName = 'affiliate_system_schema_migrations';
$lock = $pdo->prepare('SELECT GET_LOCK(:lock_name, 30)');
$lock->execute(['lock_name' => $lockName]);

if ((int) $lock->fetchColumn() !== 1) {
    fwrite(STDERR, "Não foi possível obter o bloqueio de migrations.\n");
    exit(1);
}

try {
    $files = glob(__DIR__ . '/migrations/*.sql');

    if ($files === false || $files === []) {
        throw new RuntimeException('Nenhuma migration foi encontrada.');
    }

    sort($files, SORT_NATURAL);

    foreach ($files as $file) {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException('Não foi possível ler ' . basename($file) . '.');
        }

        $sql = preg_replace('/CREATE\s+DATABASE\s+IF\s+NOT\s+EXISTS\s+[^;]+;/is', '', $sql);
        $sql = preg_replace('/^\s*USE\s+[a-zA-Z0-9_]+\s*;\s*$/mi', '', (string) $sql);
        $statements = preg_split('/;\s*(?:\R|$)/', trim((string) $sql));

        if ($statements === false) {
            throw new RuntimeException('Não foi possível interpretar ' . basename($file) . '.');
        }

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }

        echo 'Migration verificada: ' . basename($file) . PHP_EOL;
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Falha nas migrations: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    $release = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
    $release->execute(['lock_name' => $lockName]);
}
