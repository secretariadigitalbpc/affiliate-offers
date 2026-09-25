<?php

declare(strict_types=1);

use App\Config\Database;
use App\Repositories\AdministratorRepository;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$email = strtolower(trim((string) (getenv('ADMIN_BOOTSTRAP_EMAIL') ?: '')));
$password = (string) (getenv('ADMIN_BOOTSTRAP_PASSWORD') ?: '');

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($password) < 12) {
    fwrite(STDERR, "ADMIN_BOOTSTRAP_EMAIL e senha de pelo menos 12 caracteres são obrigatórios.\n");
    exit(1);
}

$repository = new AdministratorRepository(Database::connect());
$existing = $repository->findByEmail($email);

if ($existing !== null) {
    fwrite(STDOUT, "Administrador inicial já existe; senha preservada.\n");
    exit(0);
}

$administrator = $repository->create($email, password_hash($password, PASSWORD_DEFAULT));
fwrite(STDOUT, "Administrador inicial criado com ID {$administrator->id}.\n");
