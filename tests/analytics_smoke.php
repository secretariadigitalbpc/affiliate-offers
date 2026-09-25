<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\ValidationException;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\AnalyticsRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PublicOfferRepository;
use App\Services\AffiliateLinkValidator;
use App\Services\AnalyticsEventValidator;
use App\Services\AnalyticsService;
use App\Services\CampaignValidator;
use App\Services\OfferValidator;
use App\Services\ProductValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$products = new ProductRepository($pdo);
$links = new AffiliateLinkRepository($pdo);
$offers = new OfferRepository($pdo);
$campaigns = new CampaignRepository($pdo);
$analytics = new AnalyticsRepository($pdo);
$service = new AnalyticsService(
    $analytics,
    new PublicOfferRepository($pdo),
    $campaigns,
    new AnalyticsEventValidator()
);

$pdo->beginTransaction();

try {
    $productData = (new ProductValidator())->validate([
        'marketplace' => 'shopee',
        'marketplace_product_id' => 'analytics-smoke-' . bin2hex(random_bytes(5)),
        'title' => 'Produto de analytics',
        'active' => '1',
    ]);
    $product = $products->create($productData['data']);

    $linkData = (new AffiliateLinkValidator())->validate([
        'product_id' => $product->id,
        'marketplace' => 'shopee',
        'affiliate_url' => 'https://shopee.example/analytics',
        'active' => '1',
    ]);
    $link = $links->create($linkData['data']);

    $offerData = (new OfferValidator())->validate([
        'product_id' => $product->id,
        'affiliate_link_id' => $link->id,
        'price' => '79.90',
        'status' => 'published',
    ]);
    $offer = $offers->create($offerData['data']);

    $campaignData = (new CampaignValidator())->validate([
        'name' => 'Analytics Smoke',
        'slug' => 'analytics-smoke-' . bin2hex(random_bytes(4)),
        'source' => 'whatsapp',
        'active' => '1',
    ]);
    $campaign = $campaigns->create($campaignData['data']);

    $input = [
        'offer_id' => $offer->id,
        'campaign' => $campaign->slug,
        'source' => 'whatsapp_status',
        'session_id' => 'smoke-session-01',
    ];
    $viewId = $service->recordView($input, 'Mozilla/5.0 Firefox/143.0');
    $clickId = $service->recordClick($input);

    $viewStatement = $pdo->prepare('SELECT * FROM page_views WHERE id = :id');
    $viewStatement->execute(['id' => $viewId]);
    $view = $viewStatement->fetch();
    $clickStatement = $pdo->prepare('SELECT * FROM clicks WHERE id = :id');
    $clickStatement->execute(['id' => $clickId]);
    $click = $clickStatement->fetch();

    if (
        $view === false
        || (int) $view['product_id'] !== $product->id
        || (int) $view['campaign_id'] !== $campaign->id
        || $view['user_agent_family'] !== 'Firefox'
    ) {
        throw new RuntimeException('A visualização não foi atribuída corretamente.');
    }

    if (
        $click === false
        || (int) $click['affiliate_link_id'] !== $link->id
        || (int) $click['offer_id'] !== $offer->id
    ) {
        throw new RuntimeException('O clique não foi atribuído corretamente.');
    }

    try {
        $service->recordView(['offer_id' => $offer->id, 'source' => 'origem inválida'], '');
        throw new RuntimeException('A validação aceitou uma origem inválida.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    echo "Analytics: visualização, clique, atribuição e privacidade = OK\n";
} finally {
    $pdo->rollBack();
}
