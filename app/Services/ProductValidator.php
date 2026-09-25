<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Slug;

final class ProductValidator
{
    private const MARKETPLACES = ['mercado_livre', 'shopee'];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $marketplace = trim((string) ($input['marketplace'] ?? ''));
        $externalId = trim((string) ($input['marketplace_product_id'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $imageUrl = $this->nullableTrim($input['image_url'] ?? null);
        $sellerName = $this->nullableTrim($input['seller_name'] ?? null);
        $errors = [];

        if (!in_array($marketplace, self::MARKETPLACES, true)) {
            $errors['marketplace'] = 'Selecione um marketplace válido.';
        }

        if ($externalId === '' || $this->length($externalId) > 100) {
            $errors['marketplace_product_id'] = 'Informe um identificador com até 100 caracteres.';
        }

        if ($title === '' || $this->length($title) > 255) {
            $errors['title'] = 'Informe um título com até 255 caracteres.';
        }

        if ($slug === '') {
            $slug = Slug::from($title);
        }

        if ($slug === '' || $this->length($slug) > 255) {
            $errors['slug'] = 'Informe um slug válido com até 255 caracteres.';
        }

        if ($imageUrl !== null && !$this->isHttpUrl($imageUrl)) {
            $errors['image_url'] = 'Informe uma URL HTTP ou HTTPS válida.';
        }

        if ($sellerName !== null && $this->length($sellerName) > 255) {
            $errors['seller_name'] = 'O vendedor deve ter até 255 caracteres.';
        }

        $rating = $this->nullableDecimal($input['rating'] ?? null, 0, 5, 'rating', $errors);
        $salesCount = $this->nullableInteger($input['sales_count'] ?? null, 'sales_count', $errors);
        $categoryId = $this->nullableInteger($input['category_id'] ?? null, 'category_id', $errors, 1);

        return [
            'data' => [
                'marketplace' => $marketplace,
                'marketplace_product_id' => $externalId,
                'title' => $title,
                'slug' => $slug,
                'category_id' => $categoryId,
                'image_url' => $imageUrl,
                'seller_name' => $sellerName,
                'rating' => $rating,
                'sales_count' => $salesCount,
                'active' => $this->booleanValue($input['active'] ?? true),
            ],
            'errors' => $errors,
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /** @param array<string, string> $errors */
    private function nullableDecimal(
        mixed $value,
        float $minimum,
        float $maximum,
        string $field,
        array &$errors,
    ): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value) || (float) $value < $minimum || (float) $value > $maximum) {
            $errors[$field] = 'Informe uma avaliação entre 0 e 5.';

            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /** @param array<string, string> $errors */
    private function nullableInteger(
        mixed $value,
        string $field,
        array &$errors,
        int $minimum = 0,
    ): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if ($integer === false || $integer < $minimum) {
            $errors[$field] = 'Informe um número inteiro válido.';

            return null;
        }

        return $integer;
    }

    private function booleanValue(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on'], true);
    }

    private function isHttpUrl(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
