<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Campaign;
use PDO;
use RuntimeException;

final class CampaignRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<Campaign> */
    public function all(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM campaigns ORDER BY created_at DESC, id DESC'
        );
        $statement->execute();

        return array_map(
            static fn (array $row): Campaign => Campaign::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function find(int $id): ?Campaign
    {
        $statement = $this->pdo->prepare('SELECT * FROM campaigns WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Campaign::fromArray($row);
    }

    public function findActiveBySlug(string $slug): ?Campaign
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM campaigns WHERE slug = :slug AND active = 1 LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : Campaign::fromArray($row);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Campaign
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO campaigns (name, slug, source, medium, active)
             VALUES (:name, :slug, :source, :medium, :active)'
        );
        $statement->execute($this->persistenceData($data));
        $campaign = $this->find((int) $this->pdo->lastInsertId());

        if ($campaign === null) {
            throw new RuntimeException('Campanha criada não pôde ser recuperada.');
        }

        return $campaign;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?Campaign
    {
        $statement = $this->pdo->prepare(
            'UPDATE campaigns SET
                name = :name,
                slug = :slug,
                source = :source,
                medium = :medium,
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
            'name' => $data['name'],
            'slug' => $data['slug'],
            'source' => $data['source'],
            'medium' => $data['medium'],
            'active' => $data['active'] ? 1 : 0,
        ];
    }
}

