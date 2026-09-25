<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\SaleController;
use App\Helpers\ValidationException;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\CampaignValidator;
use App\Services\ProductValidator;
use App\Services\SaleValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$products = new ProductRepository($pdo);
$campaigns = new CampaignRepository($pdo);
$sales = new SaleRepository($pdo);
$controller = new SaleController($sales, $products, $campaigns, new SaleValidator());

$pdo->beginTransaction();

try {
    $productData = (new ProductValidator())->validate([
        'marketplace' => 'mercado_livre',
        'marketplace_product_id' => 'sale-smoke-' . bin2hex(random_bytes(5)),
        'title' => 'Produto de venda',
        'active' => '1',
    ]);
    $product = $products->create($productData['data']);
    $campaignData = (new CampaignValidator())->validate([
        'name' => 'Campanha de venda',
        'slug' => 'sale-smoke-' . bin2hex(random_bytes(4)),
        'source' => 'whatsapp',
        'active' => '1',
    ]);
    $campaign = $campaigns->create($campaignData['data']);
    $reference = 'ML-' . bin2hex(random_bytes(6));
    $input = [
        'marketplace' => 'mercado_livre',
        'external_sale_reference' => $reference,
        'product_id' => $product->id,
        'campaign_id' => $campaign->id,
        'quantity' => '2',
        'gross_value' => '199,90',
        'commission_value' => '24,50',
        'status' => 'approved',
        'sale_date' => '2026-09-25T10:30',
    ];
    $sale = $controller->create($input);

    if (
        $sale->grossValue !== '199.90'
        || $sale->commissionValue !== '24.50'
        || $sale->quantity !== 2
        || $sale->productTitle !== 'Produto de venda'
        || $sale->campaignName !== 'Campanha de venda'
        || $sale->importedAt !== null
    ) {
        throw new RuntimeException('A venda manual não foi normalizada ou relacionada corretamente.');
    }

    $withoutRelations = $controller->create([
        'marketplace' => 'shopee',
        'external_sale_reference' => '',
        'product_id' => '',
        'campaign_id' => '',
        'quantity' => '1',
        'gross_value' => '50.00',
        'commission_value' => '0',
        'status' => 'pending',
        'sale_date' => '2026-09-25 11:00:00',
    ]);

    if ($withoutRelations->productId !== null || $withoutRelations->campaignId !== null) {
        throw new RuntimeException('Os relacionamentos opcionais não foram preservados como nulos.');
    }

    try {
        $controller->create(array_merge($input, ['commission_value' => '250.00']));
        throw new RuntimeException('A validação aceitou comissão maior que o valor bruto.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    try {
        $controller->create(array_merge($input, ['marketplace' => 'shopee', 'external_sale_reference' => 'SHOPEE-MISMATCH']));
        throw new RuntimeException('A validação aceitou produto de outro marketplace.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    try {
        $controller->create($input);
        throw new RuntimeException('A referência duplicada foi aceita.');
    } catch (DomainException) {
        // Comportamento esperado.
    }

    if (count($controller->list()) < 2) {
        throw new RuntimeException('A listagem de vendas não retornou os registros criados.');
    }

    echo "Vendas: cadastro manual, valores, status, vínculos e duplicidade = OK\n";
} finally {
    $pdo->rollBack();
}
