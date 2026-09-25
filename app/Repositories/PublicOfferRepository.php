<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\PublicOffer;
use PDO;

final class PublicOfferRepository
{
    private const SELECT_COLUMNS =
        'SELECT offers.id AS offer_id, offers.product_id, offers.affiliate_link_id,
                offers.price, offers.old_price,
                offers.discount_percentage, offers.coupon_text, offers.shipping_text,
                offers.expires_at, products.title, products.slug, products.marketplace,
                products.image_url, products.seller_name, affiliate_links.affiliate_url
         FROM offers
         INNER JOIN products ON products.id = offers.product_id
         INNER JOIN affiliate_links ON affiliate_links.id = offers.affiliate_link_id';

    private const PUBLIC_FILTER =
        "offers.status = 'published'
         AND products.active = 1
         AND affiliate_links.active = 1
         AND (offers.starts_at IS NULL OR offers.starts_at <= CURRENT_TIMESTAMP)
         AND (offers.expires_at IS NULL OR offers.expires_at > CURRENT_TIMESTAMP)";

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<PublicOffer> */
    public function published(): array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . ' WHERE ' . self::PUBLIC_FILTER .
            ' ORDER BY offers.updated_at DESC, offers.id DESC'
        );
        $statement->execute();

        return array_map(
            static fn (array $row): PublicOffer => PublicOffer::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function findPublished(int $offerId): ?PublicOffer
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . ' WHERE ' . self::PUBLIC_FILTER . ' AND offers.id = :id'
        );
        $statement->execute(['id' => $offerId]);
        $row = $statement->fetch();

        return $row === false ? null : PublicOffer::fromArray($row);
    }
}
