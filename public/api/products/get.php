<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProductController;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\ProductRepository;
use App\Services\ProductValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $id = Request::positiveInt($_GET['id'] ?? null);
    $controller = new ProductController(
        new ProductRepository(Database::connect()),
        new ProductValidator()
    );
    JsonResponse::success(['product' => $controller->get($id)->toArray()]);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (OutOfBoundsException $exception) {
    JsonResponse::error('PRODUCT_NOT_FOUND', $exception->getMessage(), 404);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível consultar o produto.', 500);
}
