<?php

declare(strict_types=1);

namespace App\Services;

final class AffiliateLinkValidator
{
    private const MARKETPLACES = ['mercado_livre', 'shopee'];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $productId = $this->positiveInteger($input['product_id'] ?? null);
        $marketplace = trim((string) ($input['marketplace'] ?? ''));
        $affiliateUrl = trim((string) ($input['affiliate_url'] ?? ''));
        $campaignId = $this->nullablePositiveInteger($input['campaign_id'] ?? null);
        $tag = trim((string) ($input['tag'] ?? ''));

        if ($productId === null) {
            $errors['product_id'] = 'Selecione um produto válido.';
        }

        if (!in_array($marketplace, self::MARKETPLACES, true)) {
            $errors['marketplace'] = 'Selecione um marketplace válido.';
        }

        if (!$this->isHttpUrl($affiliateUrl) || strlen($affiliateUrl) > 2048) {
            $errors['affiliate_url'] = 'Informe uma URL HTTP ou HTTPS válida com até 2048 caracteres.';
        }

        if (($input['campaign_id'] ?? '') !== '' && $campaignId === null) {
            $errors['campaign_id'] = 'Informe uma campanha válida.';
        }

        if ($this->length($tag) > 100) {
            $errors['tag'] = 'A tag deve ter até 100 caracteres.';
        }

        return [
            'data' => [
                'product_id' => $productId,
                'marketplace' => $marketplace,
                'affiliate_url' => $affiliateUrl,
                'campaign_id' => $campaignId,
                'tag' => $tag === '' ? null : $tag,
                'active' => in_array($input['active'] ?? true, [true, 1, '1', 'true', 'on'], true),
            ],
            'errors' => $errors,
        ];
    }

    private function positiveInteger(mixed $value): ?int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer !== false && $integer > 0 ? $integer : null;
    }

    private function nullablePositiveInteger(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : $this->positiveInteger($value);
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

