<?php

declare(strict_types=1);

use App\Config\Environment;

require_once dirname(__DIR__) . '/app/Config/Environment.php';

$key = 'AFFILIATE_OFFERS_ENVIRONMENT_SMOKE';
$value = 'container-runtime-value';

putenv($key . '=' . $value);

Environment::load(__DIR__ . '/missing-environment-file.env');

if (Environment::get($key) !== $value) {
    fwrite(STDERR, "Falha ao preservar variável fornecida pelo ambiente.\n");
    exit(1);
}

putenv($key);

fwrite(STDOUT, "Environment sem .env = OK\n");
