<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Helpers\ValidationException;
use App\Repositories\AnalyticsRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\PublicOfferRepository;
use App\Services\AnalyticsEventValidator;
use App\Services\AnalyticsService;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('POST');
    $pdo = Database::connect();
    $service = new AnalyticsService(
        new AnalyticsRepository($pdo),
        new PublicOfferRepository($pdo),
        new CampaignRepository($pdo),
        new AnalyticsEventValidator()
    );
    $service->recordClick(Request::input());
    JsonResponse::success(['recorded' => true], 202);
} catch (ValidationException $exception) {
    JsonResponse::error('VALIDATION_ERROR', $exception->getMessage(), 422, $exception->errors);
} catch (OutOfBoundsException $exception) {
    JsonResponse::error('OFFER_NOT_FOUND', $exception->getMessage(), 404);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível registrar o clique.', 500);
}
