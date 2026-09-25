<?php

declare(strict_types=1);

use App\Config\Database;
use App\Repositories\AdministratorRepository;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$email = strtolower(trim((string) ($argv[1] ?? '')));
$password = (string) (getenv('ADMIN_BOOTSTRAP_PASSWORD') ?: '');

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Uso: defina ADMIN_BOOTSTRAP_PASSWORD e execute php database/seeds/create_admin.php email@exemplo.com\n");
    exit(1);
}

if (strlen($password) < 12) {
    fwrite(STDERR, "ADMIN_BOOTSTRAP_PASSWORD deve ter pelo menos 12 caracteres.\n");
    exit(1);
}

$repository = new AdministratorRepository(Database::connect());

if ($repository->findByEmail($email) !== null) {
    fwrite(STDERR, "Já existe um administrador com esse e-mail.\n");
    exit(1);
}

$administrator = $repository->create($email, password_hash($password, PASSWORD_DEFAULT));
fwrite(STDOUT, "Administrador criado com ID {$administrator->id}.\n");

