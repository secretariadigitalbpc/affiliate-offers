<?php

declare(strict_types=1);

namespace App\Helpers;

final class Csrf
{
    public static function token(): string
    {
        Session::start();

        if (!isset($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    /** @param array<string, mixed> $input */
    public static function verify(array $input): bool
    {
        Session::start();
        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['_csrf'] ?? '';
        $stored = $_SESSION['_csrf'] ?? '';

        return is_string($provided)
            && is_string($stored)
            && $stored !== ''
            && hash_equals($stored, $provided);
    }

}

