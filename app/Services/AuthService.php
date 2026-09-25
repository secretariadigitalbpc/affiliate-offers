<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Auth;
use App\Repositories\AdministratorRepository;
use DomainException;

final class AuthService
{
    public function __construct(private readonly AdministratorRepository $repository)
    {
    }

    public function attempt(string $email, string $password): void
    {
        $email = strtolower(trim($email));
        $administrator = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $this->repository->findByEmail($email)
            : null;
        $hash = $administrator?->passwordHash ?? password_hash(
            bin2hex(random_bytes(16)),
            PASSWORD_DEFAULT
        );
        $passwordMatches = password_verify($password, $hash);

        if ($administrator === null || !$administrator->active || !$passwordMatches) {
            throw new DomainException('E-mail ou senha inválidos.');
        }

        if (password_needs_rehash($administrator->passwordHash, PASSWORD_DEFAULT)) {
            $this->repository->updatePasswordHash(
                (int) $administrator->id,
                password_hash($password, PASSWORD_DEFAULT)
            );
        }

        $this->repository->recordLogin((int) $administrator->id);
        Auth::login($administrator);
    }
}

