<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ValidationException;
use DateTimeImmutable;

final class AnalyticsSummaryValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{from: string, to: string, from_start: string, to_exclusive: string, campaign_id: ?int}
     */
    public function validate(array $input): array
    {
        $today = new DateTimeImmutable('today');
        $defaultFrom = $today->modify('-29 days');
        $from = $this->date($input['from'] ?? $defaultFrom->format('Y-m-d'));
        $to = $this->date($input['to'] ?? $today->format('Y-m-d'));
        $campaignId = $this->campaignId($input['campaign_id'] ?? null);
        $errors = [];

        if ($from === null) {
            $errors['from'] = 'Informe uma data inicial válida.';
        }

        if ($to === null) {
            $errors['to'] = 'Informe uma data final válida.';
        }

        if (($input['campaign_id'] ?? '') !== '' && $campaignId === null) {
            $errors['campaign_id'] = 'Selecione uma campanha válida.';
        }

        if ($from !== null && $to !== null && $from > $to) {
            $errors['to'] = 'A data final deve ser igual ou posterior à inicial.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'from_start' => $from->format('Y-m-d 00:00:00'),
            'to_exclusive' => $to->modify('+1 day')->format('Y-m-d 00:00:00'),
            'campaign_id' => $campaignId,
        ];
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        $value = trim((string) $value);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $value
        ) {
            return null;
        }

        return $date;
    }

    private function campaignId(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer !== false && $integer > 0 ? $integer : null;
    }
}
