<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProductController;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\ProductRepository;
use App\Services\ProductValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('POST');
    Auth::requireApi();
    $input = Request::input();

    if (!Csrf::verify($input)) {
        JsonResponse::error('CSRF_INVALID', 'Token CSRF inválido ou expirado.', 403);
    }

    $id = Request::positiveInt($input['id'] ?? null);
    $controller = new ProductController(
        new ProductRepository(Database::connect()),
        new ProductValidator()
    );
    JsonResponse::success(['product' => $controller->update($id, $input)->toArray()]);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (OutOfBoundsException $exception) {
    JsonResponse::error('PRODUCT_NOT_FOUND', $exception->getMessage(), 404);
} catch (DomainException $exception) {
    JsonResponse::error('PRODUCT_ALREADY_EXISTS', $exception->getMessage(), 409);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível atualizar o produto.', 500);
}
