<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ValidationException;

final class AnalyticsEventValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{offer_id: int, campaign: ?string, source: ?string, session_id: ?string}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $offerId = filter_var($input['offer_id'] ?? null, FILTER_VALIDATE_INT);
        $campaign = $this->optional($input['campaign'] ?? null);
        $source = $this->optional($input['source'] ?? null);
        $sessionId = $this->optional($input['session_id'] ?? null);

        if ($offerId === false || $offerId < 1) {
            $errors['offer_id'] = 'Informe uma oferta válida.';
        }

        if ($campaign !== null && (
            $this->length($campaign) > 255
            || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $campaign) !== 1
        )) {
            $errors['campaign'] = 'Informe um slug de campanha válido.';
        }

        if ($source !== null && (
            $this->length($source) > 100
            || preg_match('/^[a-zA-Z0-9._-]+$/', $source) !== 1
        )) {
            $errors['source'] = 'Informe uma origem válida com até 100 caracteres.';
        }

        if ($sessionId !== null && (
            $this->length($sessionId) > 128
            || preg_match('/^[a-zA-Z0-9._:-]+$/', $sessionId) !== 1
        )) {
            $errors['session_id'] = 'Informe um identificador de sessão válido.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'offer_id' => (int) $offerId,
            'campaign' => $campaign,
            'source' => $source === null ? null : strtolower($source),
            'session_id' => $sessionId,
        ];
    }

    private function optional(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
