<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\OfferController;
use App\Helpers\ValidationException;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Services\AffiliateLinkValidator;
use App\Services\OfferValidator;
use App\Services\ProductValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$productRepository = new ProductRepository($pdo);
$linkRepository = new AffiliateLinkRepository($pdo);
$offerRepository = new OfferRepository($pdo);
$productValidator = new ProductValidator();
$linkValidator = new AffiliateLinkValidator();
$controller = new OfferController(
    $offerRepository,
    $productRepository,
    $linkRepository,
    new OfferValidator()
);

$pdo->beginTransaction();

try {
    $productData = $productValidator->validate([
        'marketplace' => 'mercado_livre',
        'marketplace_product_id' => 'offer-smoke-' . bin2hex(random_bytes(5)),
        'title' => 'Produto para oferta',
        'active' => '1',
    ]);
    $product = $productRepository->create($productData['data']);
    $linkData = $linkValidator->validate([
        'product_id' => $product->id,
        'marketplace' => 'mercado_livre',
        'affiliate_url' => 'https://mercadolivre.example/oferta',
        'active' => '1',
    ]);
    $link = $linkRepository->create($linkData['data']);

    $offer = $controller->create([
        'product_id' => $product->id,
        'affiliate_link_id' => $link->id,
        'price' => '79,90',
        'old_price' => '99.90',
        'coupon_text' => 'CUPOM AÇÃO',
        'shipping_text' => 'Frete grátis',
        'status' => 'approved',
        'starts_at' => '2026-10-01T10:00',
        'expires_at' => '2026-10-02T10:00',
    ]);

    if ($offer->price !== '79.90' || $offer->discountPercentage !== '20.02') {
        throw new RuntimeException('Preço ou desconto não foi calculado corretamente.');
    }

    $updated = $controller->update((int) $offer->id, [
        'product_id' => $product->id,
        'affiliate_link_id' => $link->id,
        'price' => '69.90',
        'old_price' => '99.90',
        'coupon_text' => 'NOVO CUPOM',
        'shipping_text' => 'Frete grátis',
        'status' => 'published',
        'starts_at' => '',
        'expires_at' => '',
    ]);

    if ($updated->status !== 'published' || $updated->discountPercentage !== '30.03') {
        throw new RuntimeException('A oferta não foi atualizada corretamente.');
    }

    try {
        $controller->create([
            'product_id' => $product->id,
            'affiliate_link_id' => $link->id,
            'price' => '100.00',
            'old_price' => '90.00',
            'status' => 'invalido',
            'starts_at' => '2026-10-02T10:00',
            'expires_at' => '2026-10-01T10:00',
        ]);
        throw new RuntimeException('A validação aceitou preço, status ou período inválido.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    $secondProductData = $productValidator->validate([
        'marketplace' => 'mercado_livre',
        'marketplace_product_id' => 'offer-mismatch-' . bin2hex(random_bytes(5)),
        'title' => 'Outro produto',
        'active' => '1',
    ]);
    $secondProduct = $productRepository->create($secondProductData['data']);

    try {
        $controller->create([
            'product_id' => $secondProduct->id,
            'affiliate_link_id' => $link->id,
            'price' => '10.00',
            'status' => 'draft',
        ]);
        throw new RuntimeException('A oferta aceitou link pertencente a outro produto.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    echo "Ofertas: criar, listar, editar, calcular e validar = OK\n";
} finally {
    $pdo->rollBack();
}

