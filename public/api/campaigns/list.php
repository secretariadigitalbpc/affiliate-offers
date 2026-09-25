<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\CampaignController;
use App\Helpers\Auth;
use App\Helpers\JsonResponse;
use App\Helpers\Request;
use App\Repositories\CampaignRepository;
use App\Services\CampaignValidator;

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

try {
    Request::requireMethod('GET');
    Auth::requireApi();
    $controller = new CampaignController(
        new CampaignRepository(Database::connect()),
        new CampaignValidator()
    );
    JsonResponse::success(['campaigns' => $controller->list()]);
} catch (Throwable) {
    JsonResponse::error('INTERNAL_ERROR', 'Não foi possível listar as campanhas.', 500);
}

