<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Config\Database;
use App\Models\Administrator;
use App\Repositories\AdministratorRepository;

final class Auth
{
    private const SESSION_KEY = 'administrator_id';

    public static function login(Administrator $administrator): void
    {
        if ($administrator->id === null) {
            return;
        }

        Session::regenerate();
        $_SESSION[self::SESSION_KEY] = $administrator->id;
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function id(): ?int
    {
        Session::start();
        $id = $_SESSION[self::SESSION_KEY] ?? null;

        return is_int($id) || ctype_digit((string) $id) ? (int) $id : null;
    }

    public static function user(): ?Administrator
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        $administrator = (new AdministratorRepository(Database::connect()))->findById($id);

        if ($administrator === null || !$administrator->active) {
            self::logout();

            return null;
        }

        return $administrator;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requirePage(): Administrator
    {
        $administrator = self::user();

        if ($administrator === null) {
            header('Location: login.php');
            exit;
        }

        return $administrator;
    }

    public static function requireApi(): Administrator
    {
        $administrator = self::user();

        if ($administrator === null) {
            JsonResponse::error('AUTH_REQUIRED', 'Autenticação administrativa obrigatória.', 401);
        }

        return $administrator;
    }
}

