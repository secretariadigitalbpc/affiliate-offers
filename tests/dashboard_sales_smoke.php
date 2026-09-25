<?php

declare(strict_types=1);

use App\Config\Database;
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
        'marketplace_product_id' => 'dashboard-sales-' . bin2hex(random_bytes(5)),
        'title' => 'Produto de conversão',
        'active' => '1',
    ]);
    $product = $products->create($productData['data']);
    $linkData = (new AffiliateLinkValidator())->validate([
        'product_id' => $product->id,
        'marketplace' => 'mercado_livre',
        'affiliate_url' => 'https://mercadolivre.example/conversao',
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
        'name' => 'Conversão controlada',
        'slug' => 'conversao-' . bin2hex(random_bytes(4)),
        'source' => 'whatsapp',
        'active' => '1',
    ]);
    $campaign = $campaigns->create($campaignData['data']);
    $moment = (new DateTimeImmutable('today'))->format('Y-m-d 12:00:00');

    $view = $pdo->prepare(
        'INSERT INTO page_views (product_id, offer_id, campaign_id, source, session_id, created_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $click = $pdo->prepare(
        'INSERT INTO clicks (product_id, offer_id, affiliate_link_id, campaign_id, source, session_id, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $sale = $pdo->prepare(
        'INSERT INTO sales (
            marketplace, external_sale_reference, product_id, campaign_id, quantity,
            gross_value, commission_value, status, sale_date
         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    for ($index = 1; $index <= 100; $index++) {
        $view->execute([
            $product->id,
            $offer->id,
            $index <= 60 ? $campaign->id : null,
            'controlado',
            'view-' . $index,
            $moment,
        ]);
    }

    for ($index = 1; $index <= 20; $index++) {
        $click->execute([
            $product->id,
            $offer->id,
            $link->id,
            $index <= 10 ? $campaign->id : null,
            'controlado',
            'click-' . $index,
            $moment,
        ]);
    }

    for ($index = 1; $index <= 4; $index++) {
        $sale->execute([
            'mercado_livre',
            'CONTROL-' . $index . '-' . bin2hex(random_bytes(3)),
            $product->id,
            $index <= 3 ? $campaign->id : null,
            1,
            '100.00',
            '10.00',
            'approved',
            $moment,
        ]);
    }

    $sale->execute([
        'mercado_livre',
        'CANCELLED-' . bin2hex(random_bytes(3)),
        $product->id,
        $campaign->id,
        1,
        '999.00',
        '99.00',
        'cancelled',
        $moment,
    ]);

    $date = (new DateTimeImmutable('today'))->format('Y-m-d');
    $summary = $service->summary(['from' => $date, 'to' => $date]);
    $campaignSummary = $service->summary([
        'from' => $date,
        'to' => $date,
        'campaign_id' => $campaign->id,
    ]);

    if (
        $summary['totals']['views'] !== 100
        || $summary['totals']['clicks'] !== 20
        || $summary['totals']['ctr'] !== 20.0
        || $summary['totals']['sales'] !== 4
        || $summary['totals']['gross_value'] !== '400.00'
        || $summary['totals']['commission_value'] !== '40.00'
        || $summary['totals']['conversion'] !== 20.0
    ) {
        throw new RuntimeException('As métricas gerais de venda estão incorretas.');
    }

    if (
        $campaignSummary['totals']['views'] !== 60
        || $campaignSummary['totals']['clicks'] !== 10
        || $campaignSummary['totals']['sales'] !== 3
        || $campaignSummary['totals']['gross_value'] !== '300.00'
        || $campaignSummary['totals']['commission_value'] !== '30.00'
        || $campaignSummary['totals']['conversion'] !== 30.0
    ) {
        throw new RuntimeException('As métricas filtradas por campanha estão incorretas.');
    }

    echo "Dashboard de vendas: 100 views, 20 cliques, 4 vendas, 20% conversão e R$ 40 = OK\n";
} finally {
    $pdo->rollBack();
}
