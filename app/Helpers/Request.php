<?php

declare(strict_types=1);

namespace App\Helpers;

use JsonException;

final class Request
{
    /** @return array<string, mixed> */
    public static function input(): array
    {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (str_contains($contentType, 'application/json')) {
            $content = file_get_contents('php://input');

            if ($content === false || trim($content) === '') {
                return [];
            }

            try {
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw new ValidationException(['body' => 'O JSON enviado é inválido.']);
            }

            if (!is_array($data)) {
                throw new ValidationException(['body' => 'O corpo deve ser um objeto JSON.']);
            }

            return $data;
        }

        return $_POST;
    }

    public static function requireMethod(string $method): void
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== strtoupper($method)) {
            header('Allow: ' . strtoupper($method));
            JsonResponse::error('METHOD_NOT_ALLOWED', 'Método HTTP não permitido.', 405);
        }
    }

    public static function positiveInt(mixed $value, string $field = 'id'): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if ($integer === false || $integer < 1) {
            throw new ValidationException([$field => 'Informe um identificador válido.']);
        }

        return $integer;
    }
}

