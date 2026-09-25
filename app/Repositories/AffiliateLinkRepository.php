<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AffiliateLink;
use PDO;
use RuntimeException;

final class AffiliateLinkRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<AffiliateLink> */
    public function all(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT affiliate_links.*, products.title AS product_title
             FROM affiliate_links
             INNER JOIN products ON products.id = affiliate_links.product_id
             ORDER BY affiliate_links.created_at DESC, affiliate_links.id DESC'
        );
        $statement->execute();

        return array_map(
            static fn (array $row): AffiliateLink => AffiliateLink::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function find(int $id): ?AffiliateLink
    {
        $statement = $this->pdo->prepare(
            'SELECT affiliate_links.*, products.title AS product_title
             FROM affiliate_links
             INNER JOIN products ON products.id = affiliate_links.product_id
             WHERE affiliate_links.id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : AffiliateLink::fromArray($row);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): AffiliateLink
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO affiliate_links (
                product_id, marketplace, affiliate_url, campaign_id, tag, active
            ) VALUES (
                :product_id, :marketplace, :affiliate_url, :campaign_id, :tag, :active
            )'
        );
        $statement->execute($this->persistenceData($data));
        $link = $this->find((int) $this->pdo->lastInsertId());

        if ($link === null) {
            throw new RuntimeException('Link criado não pôde ser recuperado.');
        }

        return $link;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?AffiliateLink
    {
        $statement = $this->pdo->prepare(
            'UPDATE affiliate_links SET
                product_id = :product_id,
                marketplace = :marketplace,
                affiliate_url = :affiliate_url,
                campaign_id = :campaign_id,
                tag = :tag,
                active = :active
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
            'marketplace' => $data['marketplace'],
            'affiliate_url' => $data['affiliate_url'],
            'campaign_id' => $data['campaign_id'],
            'tag' => $data['tag'],
            'active' => $data['active'] ? 1 : 0,
        ];
    }
}

