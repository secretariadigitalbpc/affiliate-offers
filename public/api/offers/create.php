<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\OfferController;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Services\OfferValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('POST');
    Auth::requireApi();
    $input = Request::input();

    if (!Csrf::verify($input)) {
        JsonResponse::error('CSRF_INVALID', 'Token CSRF inválido ou expirado.', 403);
    }

    $pdo = Database::connect();
    $controller = new OfferController(
        new OfferRepository($pdo),
        new ProductRepository($pdo),
        new AffiliateLinkRepository($pdo),
        new OfferValidator()
    );
    JsonResponse::success(['offer' => $controller->create($input)->toArray()], 201);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível criar a oferta.', 500);
}

