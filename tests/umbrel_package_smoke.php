<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$package = $root . '/deploy/umbrel/affiliate-offers';
$required = [
    $root . '/.dockerignore',
    $root . '/deploy/container/Dockerfile',
    $root . '/deploy/container/apache-affiliate.conf',
    $root . '/deploy/container/entrypoint.sh',
    $root . '/database/migrate.php',
    $root . '/database/seeds/ensure_admin.php',
    $package . '/umbrel-app.yml',
    $package . '/docker-compose.yml',
    $package . '/exports.sh',
    $package . '/app.env.template',
    $package . '/data/mysql/.gitkeep',
    $package . '/data/storage/logs/.gitkeep',
    $package . '/data/storage/imports/.gitkeep',
    $package . '/data/storage/backups/.gitkeep',
];

foreach ($required as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Arquivo obrigatório ausente: ' . $file);
    }
}

$dockerfile = (string) file_get_contents($root . '/deploy/container/Dockerfile');
$compose = (string) file_get_contents($package . '/docker-compose.yml');
$manifest = (string) file_get_contents($package . '/umbrel-app.yml');
$exports = (string) file_get_contents($package . '/exports.sh');
$environment = (string) file_get_contents($package . '/app.env.template');

$assertions = [
    'PHP fixado por digest' => str_contains($dockerfile, 'php:8.2.29-apache-bookworm@sha256:'),
    'PDO MySQL instalado' => str_contains($dockerfile, 'docker-php-ext-install pdo_mysql'),
    'app_proxy configurado' => str_contains($compose, 'app_proxy:') && str_contains($compose, 'affiliate-offers_app_1'),
    'sem porta bruta publicada' => !preg_match('/^\s*ports\s*:/m', $compose),
    'sem Docker socket' => !str_contains($compose, '/var/run/docker.sock'),
    'sem modo privilegiado' => !preg_match('/privileged\s*:\s*true/i', $compose),
    'MariaDB fixado por digest' => str_contains($compose, 'mariadb:11.4.8@sha256:'),
    'persistência do banco' => str_contains($compose, '${APP_DATA_DIR}/data/mysql:/var/lib/mysql'),
    'persistência da aplicação' => str_contains($compose, '${APP_DATA_DIR}/data/storage:/var/www/html/storage'),
    'manifesto versão 1' => str_contains($manifest, 'manifestVersion: 1'),
    'ID estável' => str_contains($manifest, 'id: affiliate-offers'),
    'rota útil no navegador' => str_contains($manifest, 'path: "/public/"'),
    'senha determinística' => str_contains($manifest, 'deterministicPassword: true'),
    'segredos derivados separados' => substr_count($exports, 'derive_entropy') === 2,
    'produção sem debug' => str_contains($environment, 'APP_ENV=production') && str_contains($environment, 'APP_DEBUG=false'),
    'banco interno por DNS' => str_contains($environment, 'DB_HOST=db'),
    'OAuth desativado' => str_contains($environment, 'ML_OAUTH_ENABLED=false'),
];

foreach ($assertions as $name => $passed) {
    if (!$passed) {
        throw new RuntimeException('Falha na preparação Umbrel: ' . $name);
    }
}

if (!str_contains($compose, 'REPLACE_WITH_MULTIARCH_DIGEST')) {
    throw new RuntimeException('O marcador obrigatório da imagem ainda não publicada foi removido indevidamente.');
}

$runtimeFiles = [];

foreach (['app', 'admin', 'public', 'database'] as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $runtimeFiles[] = $file->getPathname();
        }
    }
}

foreach ($runtimeFiles as $file) {
    $content = (string) file_get_contents($file);

    if (preg_match('/[A-Za-z]:\\\\/', $content) === 1) {
        throw new RuntimeException('Caminho absoluto do Windows em código de execução: ' . $file);
    }
}

echo "Umbrel: estrutura, persistência, proxy, segredos e portabilidade estática = OK\n";
echo "Pendente intencional: publicar imagem multi-arquitetura e substituir os marcadores REPLACE_WITH_*.\n";
