<?php

declare(strict_types=1);

namespace App\Integrations\MercadoLivre;

use App\Config\Environment;

final class MercadoLivreConfig
{
    public function __construct(
        private readonly bool $oauthEnabled,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
    ) {
    }

    public static function fromEnvironment(): self
    {
        return new self(
            filter_var(Environment::get('ML_OAUTH_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
            trim(Environment::get('ML_CLIENT_ID', '')),
            trim(Environment::get('ML_CLIENT_SECRET', '')),
            trim(Environment::get('ML_REDIRECT_URI', '')),
        );
    }

    public function oauthEnabled(): bool
    {
        return $this->oauthEnabled;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function clientSecret(): string
    {
        return $this->clientSecret;
    }

    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    /** @return list<string> */
    public function missingFields(): array
    {
        $missing = [];

        if ($this->clientId === '') {
            $missing[] = 'ML_CLIENT_ID';
        }

        if ($this->clientSecret === '') {
            $missing[] = 'ML_CLIENT_SECRET';
        }

        if ($this->redirectUri === '' || filter_var($this->redirectUri, FILTER_VALIDATE_URL) === false) {
            $missing[] = 'ML_REDIRECT_URI';
        }

        return $missing;
    }

    public function ready(): bool
    {
        return $this->oauthEnabled && $this->missingFields() === [];
    }

    /** @return array<string, mixed> */
    public function publicStatus(): array
    {
        return [
            'status' => $this->ready() ? 'configuration_ready' : 'not_configured',
            'oauth_enabled' => $this->oauthEnabled,
            'client_id_configured' => $this->clientId !== '',
            'client_secret_configured' => $this->clientSecret !== '',
            'redirect_uri' => $this->redirectUri,
            'missing_fields' => $this->missingFields(),
            'affiliate_api' => 'not_confirmed_in_public_documentation',
            'automatic_sales_sync' => false,
            'csv_fallback' => true,
        ];
    }
}
