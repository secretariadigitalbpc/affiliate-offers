<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

final class OfferValidator
{
    private const STATUSES = ['draft', 'approved', 'published', 'expired', 'rejected'];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $productId = $this->positiveInteger($input['product_id'] ?? null);
        $affiliateLinkId = $this->positiveInteger($input['affiliate_link_id'] ?? null);
        $price = $this->money($input['price'] ?? null);
        $oldPrice = $this->nullableMoney($input['old_price'] ?? null);
        $couponText = $this->nullableText($input['coupon_text'] ?? null);
        $shippingText = $this->nullableText($input['shipping_text'] ?? null);
        $status = trim((string) ($input['status'] ?? ''));
        $startsAt = $this->dateTime($input['starts_at'] ?? null, 'starts_at', $errors);
        $expiresAt = $this->dateTime($input['expires_at'] ?? null, 'expires_at', $errors);

        if ($productId === null) {
            $errors['product_id'] = 'Selecione um produto válido.';
        }

        if ($affiliateLinkId === null) {
            $errors['affiliate_link_id'] = 'Selecione um link de afiliado válido.';
        }

        if ($price === null || $this->moneyToCents($price) < 1) {
            $errors['price'] = 'Informe um preço válido maior que zero, com até duas casas decimais.';
        }

        if (($input['old_price'] ?? '') !== '' && $oldPrice === null) {
            $errors['old_price'] = 'Informe um preço anterior válido, com até duas casas decimais.';
        }

        if ($price !== null && $oldPrice !== null && $this->moneyToCents($oldPrice) < $this->moneyToCents($price)) {
            $errors['old_price'] = 'O preço anterior não pode ser menor que o preço atual.';
        }

        if ($couponText !== null && $this->length($couponText) > 255) {
            $errors['coupon_text'] = 'O cupom deve ter até 255 caracteres.';
        }

        if ($shippingText !== null && $this->length($shippingText) > 255) {
            $errors['shipping_text'] = 'O texto de frete deve ter até 255 caracteres.';
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Selecione um status válido.';
        }

        if ($startsAt !== null && $expiresAt !== null && $expiresAt <= $startsAt) {
            $errors['expires_at'] = 'O fim da oferta deve ser posterior ao início.';
        }

        $discount = $price !== null && $oldPrice !== null
            ? $this->discountPercentage($price, $oldPrice)
            : '0.00';

        return [
            'data' => [
                'product_id' => $productId,
                'affiliate_link_id' => $affiliateLinkId,
                'price' => $price,
                'old_price' => $oldPrice,
                'discount_percentage' => $discount,
                'coupon_text' => $couponText,
                'shipping_text' => $shippingText,
                'status' => $status,
                'starts_at' => $startsAt?->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
            ],
            'errors' => $errors,
        ];
    }

    private function money(mixed $value): ?string
    {
        $value = str_replace(',', '.', trim((string) ($value ?? '')));

        if (!preg_match('/^(0|[1-9][0-9]{0,9})(?:\.([0-9]{1,2}))?$/', $value, $matches)) {
            return null;
        }

        return $matches[1] . '.' . str_pad($matches[2] ?? '', 2, '0');
    }

    private function nullableMoney(mixed $value): ?string
    {
        return $value === null || trim((string) $value) === '' ? null : $this->money($value);
    }

    private function moneyToCents(string $value): int
    {
        [$whole, $decimal] = explode('.', $value);

        return ((int) $whole * 100) + (int) $decimal;
    }

    private function discountPercentage(string $price, string $oldPrice): string
    {
        $priceCents = $this->moneyToCents($price);
        $oldPriceCents = $this->moneyToCents($oldPrice);

        if ($oldPriceCents <= $priceCents) {
            return '0.00';
        }

        $hundredths = (int) round((($oldPriceCents - $priceCents) * 10000) / $oldPriceCents);

        return number_format($hundredths / 100, 2, '.', '');
    }

    /** @param array<string, string> $errors */
    private function dateTime(mixed $value, string $field, array &$errors): ?DateTimeImmutable
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        $format = str_contains($value, 'T') ? '!Y-m-d\TH:i' : '!Y-m-d H:i:s';
        $date = DateTimeImmutable::createFromFormat($format, $value);
        $dateErrors = DateTimeImmutable::getLastErrors();

        if ($date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            $errors[$field] = 'Informe uma data e hora válidas.';

            return null;
        }

        return $date;
    }

    private function positiveInteger(mixed $value): ?int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer !== false && $integer > 0 ? $integer : null;
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}

