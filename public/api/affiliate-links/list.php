<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AffiliateLinkController;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\ProductRepository;
use App\Services\AffiliateLinkValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $pdo = Database::connect();
    $controller = new AffiliateLinkController(
        new AffiliateLinkRepository($pdo),
        new ProductRepository($pdo),
        new AffiliateLinkValidator()
    );
    JsonResponse::success(['affiliate_links' => $controller->list()]);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível listar os links de afiliado.', 500);
}

