<?php

declare(strict_types=1);

use App\Config\Database;
use App\Repositories\PublicOfferRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$offers = (new PublicOfferRepository(Database::connect()))->published();
$campaign = publicAttribution($_GET['campaign'] ?? null, 255, '/^[a-z0-9]+(?:-[a-z0-9]+)*$/');
$source = publicAttribution($_GET['source'] ?? null, 100, '/^[a-zA-Z0-9._-]+$/');

function publicAttribution(mixed $value, int $maximumLength, string $pattern): ?string
{
    $value = trim((string) $value);
    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    return $value !== '' && $length <= $maximumLength && preg_match($pattern, $value) === 1
        ? $value
        : null;
}

function offerDetailUrl(int $offerId, ?string $campaign, ?string $source): string
{
    $parameters = ['offer_id' => $offerId];

    if ($campaign !== null) {
        $parameters['campaign'] = $campaign;
    }

    if ($source !== null) {
        $parameters['source'] = $source;
    }

    return 'produto.php?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function marketplaceLabel(string $marketplace): string
{
    return $marketplace === 'mercado_livre' ? 'Mercado Livre' : 'Shopee';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ofertas selecionadas</title>
    <meta name="description" content="Ofertas selecionadas do Mercado Livre e Shopee.">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="storefront-body">
    <header class="storefront-header">
        <a class="storefront-brand" href="ofertas.php">Ofertas selecionadas</a>
        <nav aria-label="Navegação principal">
            <a href="ofertas.php">Ofertas</a>
            <a href="../admin/login.php">Administrar</a>
        </nav>
    </header>

    <main class="storefront-main">
        <section class="storefront-hero">
            <span class="eyebrow">Mercado Livre + Shopee</span>
            <h1>Boas ofertas, sem complicação.</h1>
            <p>Confira os produtos publicados e acesse o link oficial de afiliado.</p>
        </section>

        <?php if ($offers === []): ?>
            <section class="public-empty">
                <h2>Nenhuma oferta disponível agora</h2>
                <p>As próximas ofertas publicadas aparecerão aqui.</p>
            </section>
        <?php else: ?>
            <section class="offer-grid" aria-label="Ofertas disponíveis">
                <?php foreach ($offers as $offer): ?>
                    <?php $detailUrl = offerDetailUrl($offer->offerId, $campaign, $source); ?>
                    <article class="offer-card">
                        <a class="offer-image" href="<?= e($detailUrl) ?>">
                            <?php if ($offer->imageUrl !== null): ?>
                                <img src="<?= e($offer->imageUrl) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <span aria-hidden="true">Sem imagem</span>
                            <?php endif; ?>
                        </a>
                        <div class="offer-card-content">
                            <span class="marketplace-badge"><?= e(marketplaceLabel($offer->marketplace)) ?></span>
                            <h2><a href="<?= e($detailUrl) ?>"><?= e($offer->title) ?></a></h2>
                            <div class="price-row">
                                <strong><?= e(money($offer->price)) ?></strong>
                                <?php if ($offer->oldPrice !== null): ?>
                                    <del><?= e(money($offer->oldPrice)) ?></del>
                                <?php endif; ?>
                            </div>
                            <?php if ((float) $offer->discountPercentage > 0): ?>
                                <span class="discount-badge"><?= e($offer->discountPercentage) ?>% OFF</span>
                            <?php endif; ?>
                            <?php if ($offer->shippingText !== null): ?>
                                <p class="shipping-text"><?= e($offer->shippingText) ?></p>
                            <?php endif; ?>
                            <a class="primary-button public-button" href="<?= e($detailUrl) ?>">
                                Ver oferta
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
