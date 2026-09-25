<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class Environment
{
    /**
     * Carrega variáveis simples no formato CHAVE=VALOR quando o arquivo existe.
     *
     * Em produção, contêineres podem fornecer toda a configuração diretamente
     * pelo ambiente e não precisam manter um arquivo .env no filesystem.
     */
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        if (!is_readable($path)) {
            throw new RuntimeException('Arquivo de configuração não pode ser lido.');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException('Não foi possível ler a configuração.');
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '' || !preg_match('/^[A-Z_][A-Z0-9_]*$/', $key)) {
                throw new RuntimeException('A configuração contém uma chave inválida.');
            }

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];

                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): string
    {
        $value = getenv($key);

        if ($value === false) {
            if ($default !== null) {
                return $default;
            }

            throw new RuntimeException('Configuração obrigatória ausente.');
        }

        return $value;
    }
}
