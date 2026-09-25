<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Integrations\MercadoLivre\MercadoLivreConfig;
use App\Integrations\MercadoLivre\MercadoLivreOAuth;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();

    JsonResponse::success([
        'integration' => MercadoLivreConfig::fromEnvironment()->publicStatus(),
        'official_endpoints' => [
            'authorization' => MercadoLivreOAuth::AUTHORIZATION_ENDPOINT,
            'token' => MercadoLivreOAuth::TOKEN_ENDPOINT,
        ],
    ]);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível consultar a integração.', 500);
}
