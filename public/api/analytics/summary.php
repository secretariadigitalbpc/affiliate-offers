<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\AnalyticsSummaryRepository;
use App\Repositories\CampaignRepository;
use App\Services\AnalyticsSummaryService;
use App\Services\AnalyticsSummaryValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $pdo = Database::connect();
    $service = new AnalyticsSummaryService(
        new AnalyticsSummaryRepository($pdo),
        new CampaignRepository($pdo),
        new AnalyticsSummaryValidator()
    );
    JsonResponse::success(['summary' => $service->summary($_GET)]);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível carregar o resumo de analytics.', 500);
}
