<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\CsvSaleImportService;
use App\Services\SaleValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('POST');
    Auth::requireApi();

    if (!Csrf::verify($_POST)) {
        JsonResponse::error('CSRF_INVALID', 'Token CSRF inválido ou expirado.', 403);
    }

    $file = $_FILES['sales_file'] ?? null;

    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new ValidationException(['sales_file' => 'Selecione um arquivo CSV válido.']);
    }

    $temporaryPath = (string) ($file['tmp_name'] ?? '');

    if (!is_uploaded_file($temporaryPath)) {
        throw new ValidationException(['sales_file' => 'O upload do arquivo não é válido.']);
    }

    $pdo = Database::connect();
    $service = new CsvSaleImportService(
        $pdo,
        new SaleRepository($pdo),
        new ProductRepository($pdo),
        new CampaignRepository($pdo),
        new SaleValidator()
    );
    $result = $service->import(
        $temporaryPath,
        (string) ($file['name'] ?? ''),
        (int) ($file['size'] ?? 0)
    );
    JsonResponse::success(['import' => $result], 201);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível importar as vendas.', 500);
}
