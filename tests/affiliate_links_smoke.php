<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AffiliateLinkController;
use App\Helpers\ValidationException;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\ProductRepository;
use App\Services\AffiliateLinkValidator;
use App\Services\ProductValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$productRepository = new ProductRepository($pdo);
$linkRepository = new AffiliateLinkRepository($pdo);
$controller = new AffiliateLinkController(
    $linkRepository,
    $productRepository,
    new AffiliateLinkValidator()
);
$productValidation = (new ProductValidator())->validate([
    'marketplace' => 'shopee',
    'marketplace_product_id' => 'affiliate-smoke-' . bin2hex(random_bytes(5)),
    'title' => 'Produto para link de afiliado',
    'active' => '1',
]);

if ($productValidation['errors'] !== []) {
    throw new RuntimeException('O produto de teste não foi validado.');
}

$pdo->beginTransaction();

try {
    $product = $productRepository->create($productValidation['data']);
    $link = $controller->create([
        'product_id' => $product->id,
        'marketplace' => 'shopee',
        'affiliate_url' => 'https://shopee.example/affiliate/produto',
        'tag' => 'campanha-ação',
        'active' => '1',
    ]);

    if ($link->id === null || $link->productTitle !== $product->title) {
        throw new RuntimeException('O link não foi criado ou relacionado corretamente.');
    }

    $updated = $controller->update((int) $link->id, [
        'product_id' => $product->id,
        'marketplace' => 'shopee',
        'affiliate_url' => 'https://shopee.example/affiliate/atualizado',
        'tag' => 'atualizada',
        'active' => '0',
    ]);

    if ($updated->active || $updated->tag !== 'atualizada') {
        throw new RuntimeException('O link não foi atualizado corretamente.');
    }

    try {
        $controller->create([
            'product_id' => $product->id,
            'marketplace' => 'mercado_livre',
            'affiliate_url' => 'javascript:alert(1)',
        ]);
        throw new RuntimeException('A validação aceitou marketplace ou URL inválidos.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    try {
        $linkRepository->create([
            'product_id' => 999999999,
            'marketplace' => 'shopee',
            'affiliate_url' => 'https://shopee.example/inexistente',
            'campaign_id' => null,
            'tag' => null,
            'active' => true,
        ]);
        throw new RuntimeException('A chave estrangeira aceitou produto inexistente.');
    } catch (PDOException) {
        // Comportamento esperado.
    }

    echo "Links de afiliado: criar, listar, editar, validar e relacionar = OK\n";
} finally {
    $pdo->rollBack();
}

