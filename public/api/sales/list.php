<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\SaleController;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\SaleValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $pdo = Database::connect();
    $controller = new SaleController(
        new SaleRepository($pdo),
        new ProductRepository($pdo),
        new CampaignRepository($pdo),
        new SaleValidator()
    );
    JsonResponse::success(['sales' => $controller->list()]);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível listar as vendas.', 500);
}
