<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    /** @param array<string, string> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Os dados informados são inválidos.');
    }
}

