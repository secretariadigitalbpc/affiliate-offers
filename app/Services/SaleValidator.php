<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

final class SaleValidator
{
    private const MARKETPLACES = ['mercado_livre', 'shopee'];
    private const STATUSES = ['pending', 'approved', 'cancelled', 'refunded'];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $marketplace = trim((string) ($input['marketplace'] ?? ''));
        $reference = $this->nullableText($input['external_sale_reference'] ?? null);
        $productId = $this->nullablePositiveInteger($input['product_id'] ?? null);
        $campaignId = $this->nullablePositiveInteger($input['campaign_id'] ?? null);
        $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_INT);
        $grossValue = $this->money($input['gross_value'] ?? null);
        $commissionValue = $this->money($input['commission_value'] ?? null);
        $status = trim((string) ($input['status'] ?? ''));
        $saleDate = $this->dateTime($input['sale_date'] ?? null);

        if (!in_array($marketplace, self::MARKETPLACES, true)) {
            $errors['marketplace'] = 'Selecione um marketplace válido.';
        }

        if ($reference !== null && $this->length($reference) > 255) {
            $errors['external_sale_reference'] = 'A referência deve ter até 255 caracteres.';
        }

        if (($input['product_id'] ?? '') !== '' && $productId === null) {
            $errors['product_id'] = 'Selecione um produto válido.';
        }

        if (($input['campaign_id'] ?? '') !== '' && $campaignId === null) {
            $errors['campaign_id'] = 'Selecione uma campanha válida.';
        }

        if ($quantity === false || $quantity < 1 || $quantity > 1000000) {
            $errors['quantity'] = 'Informe uma quantidade entre 1 e 1.000.000.';
        }

        if ($grossValue === null || $this->moneyToCents($grossValue) < 1) {
            $errors['gross_value'] = 'Informe um valor bruto maior que zero, com até duas casas decimais.';
        }

        if ($commissionValue === null) {
            $errors['commission_value'] = 'Informe uma comissão válida, com até duas casas decimais.';
        }

        if (
            $grossValue !== null
            && $commissionValue !== null
            && $this->moneyToCents($commissionValue) > $this->moneyToCents($grossValue)
        ) {
            $errors['commission_value'] = 'A comissão não pode ser maior que o valor bruto.';
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Selecione um status válido.';
        }

        if ($saleDate === null) {
            $errors['sale_date'] = 'Informe uma data e hora de venda válidas.';
        }

        return [
            'data' => [
                'marketplace' => $marketplace,
                'external_sale_reference' => $reference,
                'product_id' => $productId,
                'campaign_id' => $campaignId,
                'quantity' => $quantity === false ? null : (int) $quantity,
                'gross_value' => $grossValue,
                'commission_value' => $commissionValue,
                'status' => $status,
                'sale_date' => $saleDate?->format('Y-m-d H:i:s'),
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

    private function moneyToCents(string $value): int
    {
        [$whole, $decimal] = explode('.', $value);

        return ((int) $whole * 100) + (int) $decimal;
    }

    private function nullablePositiveInteger(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer !== false && $integer > 0 ? $integer : null;
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function dateTime(mixed $value): ?DateTimeImmutable
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        $format = str_contains($value, 'T') ? '!Y-m-d\TH:i' : '!Y-m-d H:i:s';
        $date = DateTimeImmutable::createFromFormat($format, $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            ? $date
            : null;
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
