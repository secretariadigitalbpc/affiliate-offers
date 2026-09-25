<?php

declare(strict_types=1);

use App\Integrations\MercadoLivre\MercadoLivreConfig;
use App\Integrations\MercadoLivre\MercadoLivreOAuth;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$empty = new MercadoLivreConfig(false, '', '', '');
$emptyStatus = $empty->publicStatus();

if ($empty->ready() || $emptyStatus['status'] !== 'not_configured' || !$emptyStatus['csv_fallback']) {
    throw new RuntimeException('A configuração ausente foi considerada pronta.');
}

$secret = 'segredo-que-nao-pode-aparecer';
$configured = new MercadoLivreConfig(
    true,
    '123456789',
    $secret,
    'http://localhost/whatsappAF/admin/mercado-livre-callback.php',
);
$publicJson = json_encode($configured->publicStatus(), JSON_THROW_ON_ERROR);

if (!$configured->ready() || str_contains($publicJson, $secret)) {
    throw new RuntimeException('O status público expôs o segredo ou rejeitou configuração válida.');
}

$state = bin2hex(random_bytes(32));
$url = (new MercadoLivreOAuth($configured))->authorizationUrl($state, 'desafio-pkce');
$query = [];
parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

if (
    !str_starts_with($url, MercadoLivreOAuth::AUTHORIZATION_ENDPOINT)
    || ($query['client_id'] ?? null) !== '123456789'
    || ($query['state'] ?? null) !== $state
    || ($query['code_challenge_method'] ?? null) !== 'S256'
) {
    throw new RuntimeException('A URL de autorização OAuth está incorreta.');
}

echo "Mercado Livre: configuração segura, segredo oculto e URL OAuth com state/PKCE = OK\n";
