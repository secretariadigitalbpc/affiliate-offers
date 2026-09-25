<?php

declare(strict_types=1);

namespace App\Helpers;

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public static function success(array $data, int $status = 200): never
    {
        self::send(['success' => true, 'data' => $data], $status);
    }

    /** @param array<string, string> $fields */
    public static function error(
        string $code,
        string $message,
        int $status,
        array $fields = [],
    ): never {
        $error = ['code' => $code, 'message' => $message];

        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        self::send(['success' => false, 'error' => $error], $status);
    }

    /** @param array<string, mixed> $payload */
    private static function send(array $payload, int $status): never
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
}

