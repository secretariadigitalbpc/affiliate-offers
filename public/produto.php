<?php

declare(strict_types=1);

use App\Config\Database;
use App\Repositories\PublicOfferRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$offerId = filter_input(INPUT_GET, 'offer_id', FILTER_VALIDATE_INT);
$offer = is_int($offerId) && $offerId > 0
    ? (new PublicOfferRepository(Database::connect()))->findPublished($offerId)
    : null;
$campaign = detailAttribution($_GET['campaign'] ?? null, 255, '/^[a-z0-9]+(?:-[a-z0-9]+)*$/');
$source = detailAttribution($_GET['source'] ?? null, 100, '/^[a-zA-Z0-9._-]+$/');

if ($offer === null) {
    http_response_code(404);
}

function detailEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function detailMoney(string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function detailAttribution(mixed $value, int $maximumLength, string $pattern): ?string
{
    $value = trim((string) $value);
    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    return $value !== '' && $length <= $maximumLength && preg_match($pattern, $value) === 1
        ? $value
        : null;
}

function offersUrl(?string $campaign, ?string $source): string
{
    $parameters = [];

    if ($campaign !== null) {
        $parameters['campaign'] = $campaign;
    }

    if ($source !== null) {
        $parameters['source'] = $source;
    }

    return 'ofertas.php' . ($parameters === []
        ? ''
        : '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986));
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $offer === null ? 'Oferta não encontrada' : detailEscape($offer->title) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body
    class="storefront-body"
    <?php if ($offer !== null): ?>
        data-offer-id="<?= $offer->offerId ?>"
        data-events-base="api/events"
        <?php if ($campaign !== null): ?>data-campaign="<?= detailEscape($campaign) ?>"<?php endif; ?>
        <?php if ($source !== null): ?>data-source="<?= detailEscape($source) ?>"<?php endif; ?>
    <?php endif; ?>
>
    <header class="storefront-header">
        <a class="storefront-brand" href="<?= detailEscape(offersUrl($campaign, $source)) ?>">Ofertas selecionadas</a>
        <nav><a href="<?= detailEscape(offersUrl($campaign, $source)) ?>">Voltar às ofertas</a></nav>
    </header>

    <main class="storefront-main">
        <?php if ($offer === null): ?>
            <section class="public-empty">
                <h1>Oferta não encontrada</h1>
                <p>Ela pode ter expirado ou deixado de ser publicada.</p>
                <a class="primary-button public-button" href="ofertas.php">Ver ofertas disponíveis</a>
            </section>
        <?php else: ?>
            <article class="offer-detail">
                <div class="offer-detail-image">
                    <?php if ($offer->imageUrl !== null): ?>
                        <img src="<?= detailEscape($offer->imageUrl) ?>" alt="<?= detailEscape($offer->title) ?>">
                    <?php else: ?>
                        <span aria-hidden="true">Sem imagem</span>
                    <?php endif; ?>
                </div>
                <div class="offer-detail-content">
                    <span class="marketplace-badge">
                        <?= $offer->marketplace === 'mercado_livre' ? 'Mercado Livre' : 'Shopee' ?>
                    </span>
                    <h1><?= detailEscape($offer->title) ?></h1>
                    <?php if ($offer->sellerName !== null): ?>
                        <p class="seller-name">Vendido por <?= detailEscape($offer->sellerName) ?></p>
                    <?php endif; ?>
                    <div class="detail-price">
                        <?php if ($offer->oldPrice !== null): ?>
                            <del><?= detailEscape(detailMoney($offer->oldPrice)) ?></del>
                        <?php endif; ?>
                        <strong><?= detailEscape(detailMoney($offer->price)) ?></strong>
                        <?php if ((float) $offer->discountPercentage > 0): ?>
                            <span class="discount-badge"><?= detailEscape($offer->discountPercentage) ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($offer->couponText !== null): ?>
                        <p class="offer-note"><strong>Cupom:</strong> <?= detailEscape($offer->couponText) ?></p>
                    <?php endif; ?>
                    <?php if ($offer->shippingText !== null): ?>
                        <p class="offer-note"><?= detailEscape($offer->shippingText) ?></p>
                    <?php endif; ?>
                    <?php if ($offer->expiresAt !== null): ?>
                        <p class="offer-expiration">Oferta válida enquanto durar a disponibilidade.</p>
                    <?php endif; ?>
                    <a
                        class="primary-button affiliate-button"
                        href="<?= detailEscape($offer->affiliateUrl) ?>"
                        target="_blank"
                        rel="sponsored noopener noreferrer"
                        data-affiliate-link
                    >
                        Ir para a oferta oficial
                    </a>
                    <p class="affiliate-disclosure">Este é um link de afiliado. O preço não muda para você.</p>
                </div>
            </article>
        <?php endif; ?>
    </main>
    <?php if ($offer !== null): ?>
        <script src="assets/js/public-tracking.js" defer></script>
    <?php endif; ?>
</body>
</html>
