<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\SaleController;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\SaleValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('POST');
    Auth::requireApi();
    $input = Request::input();

    if (!Csrf::verify($input)) {
        JsonResponse::error('CSRF_INVALID', 'Token CSRF inválido ou expirado.', 403);
    }

    $pdo = Database::connect();
    $controller = new SaleController(
        new SaleRepository($pdo),
        new ProductRepository($pdo),
        new CampaignRepository($pdo),
        new SaleValidator()
    );
    JsonResponse::success(['sale' => $controller->create($input)->toArray()], 201);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (DomainException $exception) {
    JsonResponse::error('SALE_ALREADY_EXISTS', $exception->getMessage(), 409);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível registrar a venda.', 500);
}
