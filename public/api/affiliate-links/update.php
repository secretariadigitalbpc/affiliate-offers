<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AffiliateLinkController;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\ProductRepository;
use App\Services\AffiliateLinkValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('POST');
    Auth::requireApi();
    $input = Request::input();

    if (!Csrf::verify($input)) {
        JsonResponse::error('CSRF_INVALID', 'Token CSRF inválido ou expirado.', 403);
    }

    $id = Request::positiveInt($input['id'] ?? null);
    $pdo = Database::connect();
    $controller = new AffiliateLinkController(
        new AffiliateLinkRepository($pdo),
        new ProductRepository($pdo),
        new AffiliateLinkValidator()
    );
    JsonResponse::success(['affiliate_link' => $controller->update($id, $input)->toArray()]);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (OutOfBoundsException $exception) {
    JsonResponse::error('AFFILIATE_LINK_NOT_FOUND', $exception->getMessage(), 404);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível atualizar o link de afiliado.', 500);
}

