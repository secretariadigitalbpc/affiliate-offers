<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\OfferController;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PublicOfferRepository;
use App\Services\AffiliateLinkValidator;
use App\Services\OfferValidator;
use App\Services\ProductValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$productRepository = new ProductRepository($pdo);
$linkRepository = new AffiliateLinkRepository($pdo);
$offerRepository = new OfferRepository($pdo);
$publicRepository = new PublicOfferRepository($pdo);
$controller = new OfferController(
    $offerRepository,
    $productRepository,
    $linkRepository,
    new OfferValidator()
);

$pdo->beginTransaction();

try {
    $productData = (new ProductValidator())->validate([
        'marketplace' => 'shopee',
        'marketplace_product_id' => 'public-smoke-' . bin2hex(random_bytes(5)),
        'title' => 'Produto público <script>alert(1)</script>',
        'image_url' => 'https://example.com/produto.jpg',
        'seller_name' => 'Loja São José',
        'active' => '1',
    ]);
    $product = $productRepository->create($productData['data']);
    $linkData = (new AffiliateLinkValidator())->validate([
        'product_id' => $product->id,
        'marketplace' => 'shopee',
        'affiliate_url' => 'https://shopee.example/publica',
        'active' => '1',
    ]);
    $link = $linkRepository->create($linkData['data']);

    $baseOffer = [
        'product_id' => $product->id,
        'affiliate_link_id' => $link->id,
        'price' => '49.90',
        'old_price' => '99.80',
        'status' => 'published',
        'starts_at' => '',
        'expires_at' => '',
    ];
    $published = $controller->create($baseOffer);
    $draft = $controller->create(array_merge($baseOffer, ['status' => 'draft']));
    $future = $controller->create(array_merge($baseOffer, [
        'starts_at' => '2099-01-01T00:00',
        'expires_at' => '2099-01-02T00:00',
    ]));
    $expired = $controller->create(array_merge($baseOffer, [
        'starts_at' => '2000-01-01T00:00',
        'expires_at' => '2000-01-02T00:00',
    ]));

    $publicIds = array_map(
        static fn ($offer): int => $offer->offerId,
        $publicRepository->published()
    );

    if (!in_array($published->id, $publicIds, true)) {
        throw new RuntimeException('A oferta publicada válida não apareceu na consulta pública.');
    }

    foreach ([$draft->id, $future->id, $expired->id] as $hiddenId) {
        if (in_array($hiddenId, $publicIds, true)) {
            throw new RuntimeException('Uma oferta indisponível apareceu na consulta pública.');
        }
    }

    $detail = $publicRepository->findPublished((int) $published->id);

    if ($detail === null || $detail->affiliateUrl !== 'https://shopee.example/publica') {
        throw new RuntimeException('Os detalhes públicos não foram recuperados corretamente.');
    }

    if ($publicRepository->findPublished((int) $draft->id) !== null) {
        throw new RuntimeException('A consulta de detalhes expôs uma oferta em rascunho.');
    }

    echo "Vitrine pública: publicada visível e indisponíveis ocultas = OK\n";
} finally {
    $pdo->rollBack();
}

