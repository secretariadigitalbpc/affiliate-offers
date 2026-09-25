<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\ValidationException;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\AnalyticsSummaryRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Services\AffiliateLinkValidator;
use App\Services\AnalyticsSummaryService;
use App\Services\AnalyticsSummaryValidator;
use App\Services\CampaignValidator;
use App\Services\OfferValidator;
use App\Services\ProductValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$products = new ProductRepository($pdo);
$links = new AffiliateLinkRepository($pdo);
$offers = new OfferRepository($pdo);
$campaigns = new CampaignRepository($pdo);
$service = new AnalyticsSummaryService(
    new AnalyticsSummaryRepository($pdo),
    $campaigns,
    new AnalyticsSummaryValidator()
);

$pdo->beginTransaction();

try {
    $productData = (new ProductValidator())->validate([
        'marketplace' => 'mercado_livre',
        'marketplace_product_id' => 'summary-smoke-' . bin2hex(random_bytes(5)),
        'title' => 'Produto do resumo',
        'active' => '1',
    ]);
    $product = $products->create($productData['data']);
    $linkData = (new AffiliateLinkValidator())->validate([
        'product_id' => $product->id,
        'marketplace' => 'mercado_livre',
        'affiliate_url' => 'https://mercadolivre.example/summary',
        'active' => '1',
    ]);
    $link = $links->create($linkData['data']);
    $offerData = (new OfferValidator())->validate([
        'product_id' => $product->id,
        'affiliate_link_id' => $link->id,
        'price' => '100.00',
        'status' => 'published',
    ]);
    $offer = $offers->create($offerData['data']);
    $campaignData = (new CampaignValidator())->validate([
        'name' => 'Resumo controlado',
        'slug' => 'resumo-' . bin2hex(random_bytes(4)),
        'source' => 'whatsapp',
        'active' => '1',
    ]);
    $campaign = $campaigns->create($campaignData['data']);

    $view = $pdo->prepare(
        'INSERT INTO page_views (product_id, offer_id, campaign_id, source, session_id, created_at)
         VALUES (:product_id, :offer_id, :campaign_id, :source, :session_id, :created_at)'
    );
    $click = $pdo->prepare(
        'INSERT INTO clicks (product_id, offer_id, affiliate_link_id, campaign_id, source, session_id, created_at)
         VALUES (:product_id, :offer_id, :affiliate_link_id, :campaign_id, :source, :session_id, :created_at)'
    );
    $today = (new DateTimeImmutable('today'))->format('Y-m-d 12:00:00');

    for ($index = 1; $index <= 10; $index++) {
        $view->execute([
            'product_id' => $product->id,
            'offer_id' => $offer->id,
            'campaign_id' => $index <= 6 ? $campaign->id : null,
            'source' => 'smoke',
            'session_id' => 'view-' . $index,
            'created_at' => $today,
        ]);
    }

    for ($index = 1; $index <= 2; $index++) {
        $click->execute([
            'product_id' => $product->id,
            'offer_id' => $offer->id,
            'affiliate_link_id' => $link->id,
            'campaign_id' => $campaign->id,
            'source' => 'smoke',
            'session_id' => 'click-' . $index,
            'created_at' => $today,
        ]);
    }

    $from = (new DateTimeImmutable('today'))->format('Y-m-d');
    $summary = $service->summary(['from' => $from, 'to' => $from]);
    $campaignSummary = $service->summary([
        'from' => $from,
        'to' => $from,
        'campaign_id' => $campaign->id,
    ]);

    if (
        $summary['totals']['views'] !== 10
        || $summary['totals']['clicks'] !== 2
        || $summary['totals']['ctr'] !== 20.0
        || count($summary['offers']) !== 1
    ) {
        throw new RuntimeException('Os totais gerais ou o CTR estão incorretos.');
    }

    if (
        $campaignSummary['totals']['views'] !== 6
        || $campaignSummary['totals']['clicks'] !== 2
        || $campaignSummary['totals']['ctr'] !== 33.33
        || $campaignSummary['filters']['campaign_name'] !== 'Resumo controlado'
    ) {
        throw new RuntimeException('O filtro de campanha ou seu CTR estão incorretos.');
    }

    try {
        $service->summary(['from' => '2026-09-30', 'to' => '2026-09-01']);
        throw new RuntimeException('O filtro aceitou um período invertido.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    echo "Dashboard: totais, CTR, período, campanha e ofertas = OK\n";
} finally {
    $pdo->rollBack();
}
