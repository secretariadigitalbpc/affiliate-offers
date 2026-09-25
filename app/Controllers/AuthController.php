<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use DomainException;

final class AuthController
{
    public function __construct(private readonly AuthService $service)
    {
    }

    /** @return array<string, string> */
    public function login(string $email, string $password): array
    {
        if (trim($email) === '' || $password === '') {
            return ['credentials' => 'Informe e-mail e senha.'];
        }

        try {
            $this->service->attempt($email, $password);
        } catch (DomainException $exception) {
            return ['credentials' => $exception->getMessage()];
        }

        return [];
    }
}

