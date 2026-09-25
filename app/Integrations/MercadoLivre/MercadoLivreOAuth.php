<?php

declare(strict_types=1);

namespace App\Integrations\MercadoLivre;

use RuntimeException;

final class MercadoLivreOAuth
{
    public const AUTHORIZATION_ENDPOINT = 'https://auth.mercadolivre.com.br/authorization';
    public const TOKEN_ENDPOINT = 'https://api.mercadolibre.com/oauth/token';

    public function __construct(private readonly MercadoLivreConfig $config)
    {
    }

    public function authorizationUrl(string $state, ?string $codeChallenge = null): string
    {
        if (!$this->config->ready()) {
            throw new RuntimeException('A integração Mercado Livre não está habilitada e configurada.');
        }

        if ($state === '' || strlen($state) < 32) {
            throw new RuntimeException('O state OAuth deve ser aleatório e ter pelo menos 32 caracteres.');
        }

        $parameters = [
            'response_type' => 'code',
            'client_id' => $this->config->clientId(),
            'redirect_uri' => $this->config->redirectUri(),
            'state' => $state,
        ];

        if ($codeChallenge !== null) {
            $parameters['code_challenge'] = $codeChallenge;
            $parameters['code_challenge_method'] = 'S256';
        }

        return self::AUTHORIZATION_ENDPOINT . '?' . http_build_query(
            $parameters,
            '',
            '&',
            PHP_QUERY_RFC3986,
        );
    }
}
