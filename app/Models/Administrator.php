<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Administrator
{
    public function __construct(
        public ?int $id,
        public string $email,
        public string $passwordHash,
        public bool $active,
        public ?string $lastLoginAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['email'],
            (string) $row['password_hash'],
            (bool) $row['active'],
            isset($row['last_login_at']) ? (string) $row['last_login_at'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        );
    }
}

