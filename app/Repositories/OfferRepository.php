<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Offer;
use PDO;
use RuntimeException;

final class OfferRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<Offer> */
    public function all(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT offers.*, products.title AS product_title,
                    affiliate_links.affiliate_url AS affiliate_url
             FROM offers
             INNER JOIN products ON products.id = offers.product_id
             INNER JOIN affiliate_links ON affiliate_links.id = offers.affiliate_link_id
             ORDER BY offers.created_at DESC, offers.id DESC'
        );
        $statement->execute();

        return array_map(
            static fn (array $row): Offer => Offer::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function find(int $id): ?Offer
    {
        $statement = $this->pdo->prepare(
            'SELECT offers.*, products.title AS product_title,
                    affiliate_links.affiliate_url AS affiliate_url
             FROM offers
             INNER JOIN products ON products.id = offers.product_id
             INNER JOIN affiliate_links ON affiliate_links.id = offers.affiliate_link_id
             WHERE offers.id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Offer::fromArray($row);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Offer
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO offers (
                product_id, affiliate_link_id, price, old_price, discount_percentage,
                coupon_text, shipping_text, status, starts_at, expires_at
             ) VALUES (
                :product_id, :affiliate_link_id, :price, :old_price, :discount_percentage,
                :coupon_text, :shipping_text, :status, :starts_at, :expires_at
             )'
        );
        $statement->execute($this->persistenceData($data));
        $offer = $this->find((int) $this->pdo->lastInsertId());

        if ($offer === null) {
            throw new RuntimeException('Oferta criada não pôde ser recuperada.');
        }

        return $offer;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?Offer
    {
        $statement = $this->pdo->prepare(
            'UPDATE offers SET
                product_id = :product_id,
                affiliate_link_id = :affiliate_link_id,
                price = :price,
                old_price = :old_price,
                discount_percentage = :discount_percentage,
                coupon_text = :coupon_text,
                shipping_text = :shipping_text,
                status = :status,
                starts_at = :starts_at,
                expires_at = :expires_at
             WHERE id = :id'
        );
        $parameters = $this->persistenceData($data);
        $parameters['id'] = $id;
        $statement->execute($parameters);

        return $this->find($id);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function persistenceData(array $data): array
    {
        return [
            'product_id' => $data['product_id'],
            'affiliate_link_id' => $data['affiliate_link_id'],
            'price' => $data['price'],
            'old_price' => $data['old_price'],
            'discount_percentage' => $data['discount_percentage'],
            'coupon_text' => $data['coupon_text'],
            'shipping_text' => $data['shipping_text'],
            'status' => $data['status'],
            'starts_at' => $data['starts_at'],
            'expires_at' => $data['expires_at'],
        ];
    }
}

