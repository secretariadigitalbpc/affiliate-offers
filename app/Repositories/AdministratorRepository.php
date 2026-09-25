<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Administrator;
use PDO;
use RuntimeException;

final class AdministratorRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?Administrator
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM administrators WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => strtolower(trim($email))]);
        $row = $statement->fetch();

        return $row === false ? null : Administrator::fromArray($row);
    }

    public function findById(int $id): ?Administrator
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM administrators WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Administrator::fromArray($row);
    }

    public function create(string $email, string $passwordHash): Administrator
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO administrators (email, password_hash) VALUES (:email, :password_hash)'
        );
        $statement->execute([
            'email' => strtolower(trim($email)),
            'password_hash' => $passwordHash,
        ]);

        $administrator = $this->findById((int) $this->pdo->lastInsertId());

        if ($administrator === null) {
            throw new RuntimeException('Administrador criado não pôde ser recuperado.');
        }

        return $administrator;
    }

    public function updatePasswordHash(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE administrators SET password_hash = :password_hash WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'password_hash' => $passwordHash]);
    }

    public function recordLogin(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE administrators SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }
}

