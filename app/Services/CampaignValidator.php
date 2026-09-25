<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Slug;

final class CampaignValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $source = trim((string) ($input['source'] ?? ''));
        $medium = trim((string) ($input['medium'] ?? ''));

        if ($name === '' || $this->length($name) > 255) {
            $errors['name'] = 'Informe um nome com até 255 caracteres.';
        }

        if ($slug === '') {
            $slug = Slug::from($name);
        } else {
            $slug = Slug::from($slug);
        }

        if ($slug === '' || $this->length($slug) > 255) {
            $errors['slug'] = 'Informe um slug válido com até 255 caracteres.';
        }

        if ($source === '' || $this->length($source) > 100) {
            $errors['source'] = 'Informe uma origem com até 100 caracteres.';
        }

        if ($this->length($medium) > 100) {
            $errors['medium'] = 'A mídia deve ter até 100 caracteres.';
        }

        return [
            'data' => [
                'name' => $name,
                'slug' => $slug,
                'source' => strtolower($source),
                'medium' => $medium === '' ? null : strtolower($medium),
                'active' => in_array($input['active'] ?? true, [true, 1, '1', 'true', 'on'], true),
            ],
            'errors' => $errors,
        ];
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
