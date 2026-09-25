<?php

declare(strict_types=1);

use App\Config\Database;
use App\Repositories\ProductRepository;
use App\Services\ProductValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$repository = new ProductRepository($pdo);
$validator = new ProductValidator();
$externalId = 'smoke-' . bin2hex(random_bytes(6));

$validation = $validator->validate([
    'marketplace' => 'mercado_livre',
    'marketplace_product_id' => $externalId,
    'title' => 'Câmera de teste',
    'slug' => '',
    'image_url' => 'https://example.com/camera.jpg',
    'seller_name' => 'Loja de teste',
    'rating' => '4.75',
    'sales_count' => '12',
    'active' => '1',
]);

if ($validation['errors'] !== []) {
    throw new RuntimeException('A validação rejeitou dados válidos.');
}

$pdo->beginTransaction();

try {
    $product = $repository->create($validation['data']);

    if ($product->id === null || $product->title !== 'Câmera de teste') {
        throw new RuntimeException('O produto não foi persistido corretamente.');
    }

    $updateData = $validation['data'];
    $updateData['title'] = 'Câmera de teste atualizada';
    $updated = $repository->update($product->id, $updateData);

    if ($updated === null || $updated->title !== 'Câmera de teste atualizada') {
        throw new RuntimeException('O produto não foi atualizado corretamente.');
    }

    if ($repository->find($product->id)?->marketplaceProductId !== $externalId) {
        throw new RuntimeException('O produto não foi consultado corretamente.');
    }

    $invalid = $validator->validate([
        'marketplace' => 'invalido',
        'marketplace_product_id' => '',
        'title' => '',
        'rating' => '8',
    ]);

    if ($invalid['errors'] === []) {
        throw new RuntimeException('A validação aceitou dados inválidos.');
    }

    echo "Produtos: criar, consultar, editar e validar = OK\n";
} finally {
    $pdo->rollBack();
}

