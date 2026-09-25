<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\CampaignController;
use App\Helpers\ValidationException;
use App\Repositories\CampaignRepository;
use App\Services\CampaignValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$repository = new CampaignRepository($pdo);
$controller = new CampaignController($repository, new CampaignValidator());

$pdo->beginTransaction();

try {
    $campaign = $controller->create([
        'name' => 'Promoção de Ação',
        'slug' => '',
        'source' => 'WhatsApp',
        'medium' => 'Social',
        'active' => '1',
    ]);

    if ($campaign->slug !== 'promocao-de-acao' || $campaign->source !== 'whatsapp') {
        throw new RuntimeException('O slug ou a origem não foram normalizados corretamente.');
    }

    if ($repository->findActiveBySlug('promocao-de-acao')?->id !== $campaign->id) {
        throw new RuntimeException('A campanha ativa não foi encontrada pelo slug.');
    }

    $updated = $controller->update((int) $campaign->id, [
        'name' => 'Promoção atualizada',
        'slug' => 'promocao-atualizada',
        'source' => 'Instagram',
        'medium' => 'Social',
        'active' => '0',
    ]);

    if ($updated->active || $updated->source !== 'instagram') {
        throw new RuntimeException('A campanha não foi atualizada corretamente.');
    }

    try {
        $controller->create(['name' => '', 'source' => '']);
        throw new RuntimeException('A validação aceitou uma campanha inválida.');
    } catch (ValidationException) {
        // Comportamento esperado.
    }

    $controller->create([
        'name' => 'Campanha única',
        'slug' => 'slug-unico',
        'source' => 'site',
    ]);

    try {
        $controller->create([
            'name' => 'Campanha duplicada',
            'slug' => 'slug-unico',
            'source' => 'site',
        ]);
        throw new RuntimeException('A unicidade do slug não foi aplicada.');
    } catch (DomainException) {
        // Comportamento esperado.
    }

    echo "Campanhas: criar, listar, editar, validar e garantir slug único = OK\n";
} finally {
    $pdo->rollBack();
}

