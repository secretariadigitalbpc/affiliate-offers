<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Environment;
use PDO;
use RuntimeException;
use Throwable;

final class DatabaseBackupService
{
    private const VERIFY_PREFIX = 'affiliate_verify_';

    private readonly string $host;
    private readonly string $port;
    private readonly string $database;
    private readonly string $username;
    private readonly string $password;
    private readonly string $backupDirectory;

    public function __construct(string $projectRoot)
    {
        $root = realpath($projectRoot);

        if ($root === false) {
            throw new RuntimeException('A raiz do projeto não pôde ser localizada.');
        }

        $backupDirectory = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';

        if (!is_dir($backupDirectory)) {
            throw new RuntimeException('A pasta storage/backups não existe.');
        }

        $resolvedBackupDirectory = realpath($backupDirectory);

        if ($resolvedBackupDirectory === false || !str_starts_with($resolvedBackupDirectory, $root . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('A pasta de backup está fora do projeto.');
        }

        $this->host = Environment::get('DB_HOST', '127.0.0.1');
        $this->port = Environment::get('DB_PORT', '3306');
        $this->database = Environment::get('DB_NAME');
        $this->username = Environment::get('DB_USER');
        $this->password = Environment::get('DB_PASS', '');
        $this->backupDirectory = $resolvedBackupDirectory;
    }

    /** @return array{dump: string, manifest: string, sha256: string, tables: array<string, int>} */
    public function create(): array
    {
        $source = $this->databasePdo($this->database);
        $before = $this->tableCounts($source, $this->database);
        $suffix = date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        $dumpPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'affiliate_system_' . $suffix . '.sql';
        $manifestPath = $dumpPath . '.json';
        $credentialsPath = $this->createCredentialsFile();

        try {
            $this->runProcess([
                $this->binary('mysqldump'),
                '--defaults-extra-file=' . $credentialsPath,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--events',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                '--skip-lock-tables',
                $this->database,
            ], null, $dumpPath);

            if (!is_file($dumpPath) || filesize($dumpPath) === 0) {
                throw new RuntimeException('O mysqldump não gerou um arquivo válido.');
            }

            $after = $this->tableCounts($source, $this->database);

            if ($before !== $after) {
                throw new RuntimeException('As contagens mudaram durante o backup; gere um novo arquivo.');
            }

            $checksum = hash_file('sha256', $dumpPath);

            if ($checksum === false) {
                throw new RuntimeException('Não foi possível calcular o checksum do backup.');
            }

            $manifest = [
                'format_version' => 1,
                'created_at' => date(DATE_ATOM),
                'database' => $this->database,
                'dump_file' => basename($dumpPath),
                'sha256' => $checksum,
                'tables' => $before,
            ];
            $written = file_put_contents(
                $manifestPath,
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL,
                LOCK_EX,
            );

            if ($written === false) {
                throw new RuntimeException('Não foi possível gravar o manifesto do backup.');
            }

            return [
                'dump' => $dumpPath,
                'manifest' => $manifestPath,
                'sha256' => $checksum,
                'tables' => $before,
            ];
        } catch (Throwable $exception) {
            if (is_file($dumpPath)) {
                unlink($dumpPath);
            }

            if (is_file($manifestPath)) {
                unlink($manifestPath);
            }

            throw $exception;
        } finally {
            if (is_file($credentialsPath)) {
                unlink($credentialsPath);
            }
        }
    }

    /** @return array{database: string, tables: array<string, int>, dropped: bool} */
    public function verify(string $dumpPath): array
    {
        $dumpPath = $this->validatedBackupPath($dumpPath);
        $manifestPath = $dumpPath . '.json';

        if (!is_file($manifestPath)) {
            throw new RuntimeException('O manifesto correspondente não foi encontrado.');
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $expectedChecksum = is_array($manifest) ? ($manifest['sha256'] ?? null) : null;
        $expectedTables = is_array($manifest) ? ($manifest['tables'] ?? null) : null;

        if (!is_string($expectedChecksum) || !is_array($expectedTables)) {
            throw new RuntimeException('O manifesto do backup é inválido.');
        }

        $actualChecksum = hash_file('sha256', $dumpPath);

        if ($actualChecksum === false || !hash_equals($expectedChecksum, $actualChecksum)) {
            throw new RuntimeException('O checksum do backup não confere com o manifesto.');
        }

        $temporaryDatabase = self::VERIFY_PREFIX . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        $this->assertTemporaryDatabase($temporaryDatabase);
        $server = $this->serverPdo();
        $credentialsPath = $this->createCredentialsFile();
        $created = false;
        $counts = [];

        try {
            $server->exec("CREATE DATABASE `$temporaryDatabase` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $created = true;
            $this->runProcess([
                $this->binary('mysql'),
                '--defaults-extra-file=' . $credentialsPath,
                '--default-character-set=utf8mb4',
                $temporaryDatabase,
            ], $dumpPath, null);

            $restored = $this->databasePdo($temporaryDatabase);
            $counts = $this->tableCounts($restored, $temporaryDatabase);
            ksort($expectedTables);

            if ($counts !== $expectedTables) {
                throw new RuntimeException('As tabelas ou contagens restauradas divergem do manifesto.');
            }

            if (($counts['schema_migrations'] ?? 0) < 1) {
                throw new RuntimeException('O histórico de migrations não foi restaurado.');
            }
        } finally {
            if (is_file($credentialsPath)) {
                unlink($credentialsPath);
            }

            if ($created) {
                $this->assertTemporaryDatabase($temporaryDatabase);
                $server->exec("DROP DATABASE `$temporaryDatabase`");
            }
        }

        $check = $server->prepare('SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = :database');
        $check->execute(['database' => $temporaryDatabase]);
        $dropped = (int) $check->fetchColumn() === 0;

        if (!$dropped) {
            throw new RuntimeException('O banco temporário de verificação não foi removido.');
        }

        return ['database' => $temporaryDatabase, 'tables' => $counts, 'dropped' => true];
    }

    private function validatedBackupPath(string $path): string
    {
        $resolved = realpath($path);

        if (
            $resolved === false
            || pathinfo($resolved, PATHINFO_EXTENSION) !== 'sql'
            || !str_starts_with($resolved, $this->backupDirectory . DIRECTORY_SEPARATOR)
        ) {
            throw new RuntimeException('O backup deve ser um arquivo .sql dentro de storage/backups.');
        }

        return $resolved;
    }

    private function assertTemporaryDatabase(string $database): void
    {
        if (preg_match('/^' . self::VERIFY_PREFIX . '[0-9]{8}_[0-9]{6}_[a-f0-9]{8}$/', $database) !== 1) {
            throw new RuntimeException('Nome de banco temporário recusado por segurança.');
        }
    }

    /** @return array<string, int> */
    private function tableCounts(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            'SELECT table_name FROM information_schema.tables
             WHERE table_schema = :database AND table_type = :table_type
             ORDER BY table_name'
        );
        $statement->execute(['database' => $database, 'table_type' => 'BASE TABLE']);
        $counts = [];

        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $table) {
            if (!is_string($table) || preg_match('/^[a-zA-Z0-9_]+$/', $table) !== 1) {
                throw new RuntimeException('Nome de tabela inesperado durante o backup.');
            }

            $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        }

        ksort($counts);

        return $counts;
    }

    private function databasePdo(string $database): PDO
    {
        return new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $this->host, $this->port, $database),
            $this->username,
            $this->password,
            $this->pdoOptions(),
        );
    }

    private function serverPdo(): PDO
    {
        return new PDO(
            sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $this->host, $this->port),
            $this->username,
            $this->password,
            $this->pdoOptions(),
        );
    }

    /** @return array<int, mixed> */
    private function pdoOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    private function binary(string $name): string
    {
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $configured = trim(Environment::get('MYSQL_BIN_DIR', ''));
        $candidates = [];

        if ($configured !== '') {
            $candidates[] = rtrim($configured, '\\/') . DIRECTORY_SEPARATOR . $name . $extension;
        }

        $candidates[] = dirname(dirname(PHP_BINARY)) . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $name . $extension;

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $name . $extension;
    }

    private function createCredentialsFile(): string
    {
        foreach ([$this->host, $this->port, $this->username, $this->password] as $value) {
            if (str_contains($value, "\n") || str_contains($value, "\r")) {
                throw new RuntimeException('Uma credencial de banco contém quebra de linha inválida.');
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'affiliate-db-');

        if ($path === false) {
            throw new RuntimeException('Não foi possível criar o arquivo temporário de credenciais.');
        }

        $quote = static fn (string $value): string => '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        $content = "[client]\n"
            . 'host=' . $quote($this->host) . "\n"
            . 'port=' . $quote($this->port) . "\n"
            . 'user=' . $quote($this->username) . "\n"
            . 'password=' . $quote($this->password) . "\n";

        if (file_put_contents($path, $content, LOCK_EX) === false) {
            unlink($path);
            throw new RuntimeException('Não foi possível gravar credenciais temporárias.');
        }

        @chmod($path, 0600);

        return $path;
    }

    /** @param list<string> $command */
    private function runProcess(array $command, ?string $stdinFile, ?string $stdoutFile): void
    {
        $descriptors = [
            0 => $stdinFile === null ? ['pipe', 'r'] : ['file', $stdinFile, 'rb'],
            1 => $stdoutFile === null ? ['pipe', 'w'] : ['file', $stdoutFile, 'wb'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        $process = proc_open($command, $descriptors, $pipes, null, null, ['bypass_shell' => true]);

        if (!is_resource($process)) {
            throw new RuntimeException('Não foi possível iniciar a ferramenta do banco.');
        }

        if ($stdinFile === null && isset($pipes[0])) {
            fclose($pipes[0]);
        }

        $stdout = $stdoutFile === null && isset($pipes[1]) ? stream_get_contents($pipes[1]) : '';
        $stderr = isset($pipes[2]) ? stream_get_contents($pipes[2]) : '';

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'A ferramenta do banco falhou: ' . trim((string) ($stderr !== '' ? $stderr : $stdout))
            );
        }
    }
}
