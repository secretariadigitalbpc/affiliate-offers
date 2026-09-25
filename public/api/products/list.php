<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProductController;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Repositories\ProductRepository;
use App\Services\ProductValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $controller = new ProductController(
        new ProductRepository(Database::connect()),
        new ProductValidator()
    );
    JsonResponse::success(['products' => $controller->list()]);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível listar os produtos.', 500);
}
