<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\OfferController;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Services\OfferValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $pdo = Database::connect();
    $controller = new OfferController(
        new OfferRepository($pdo),
        new ProductRepository($pdo),
        new AffiliateLinkRepository($pdo),
        new OfferValidator()
    );
    JsonResponse::success(['offers' => $controller->list()]);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível listar as ofertas.', 500);
}

