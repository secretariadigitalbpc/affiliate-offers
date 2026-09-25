<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Repositories\AdministratorRepository;
use App\Services\AuthService;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$repository = new AdministratorRepository($pdo);
$service = new AuthService($repository);
$email = 'auth-smoke-' . bin2hex(random_bytes(5)) . '@local.test';
$password = 'Senha-temporaria-123!';
$hash = password_hash($password, PASSWORD_DEFAULT);

if (!password_verify($password, $hash) || password_verify('incorreta', $hash)) {
    throw new RuntimeException('As funções de hash de senha não responderam como esperado.');
}

$pdo->beginTransaction();

try {
    $administrator = $repository->create($email, $hash);

    try {
        $service->attempt($email, 'senha-incorreta');
        throw new RuntimeException('O login aceitou uma senha incorreta.');
    } catch (DomainException) {
        // Comportamento esperado.
    }

    $service->attempt($email, $password);

    if (Auth::id() !== $administrator->id) {
        throw new RuntimeException('A sessão não registrou o administrador autenticado.');
    }

    Auth::logout();

    if (Auth::id() !== null) {
        throw new RuntimeException('O logout não removeu a autenticação da sessão.');
    }

    echo "Autenticação: hash, login, sessão e logout = OK\n";
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

