<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Sale;
use PDO;
use RuntimeException;

final class SaleRepository
{
    private const SELECT =
        'SELECT sales.*, products.title AS product_title, campaigns.name AS campaign_name
         FROM sales
         LEFT JOIN products ON products.id = sales.product_id
         LEFT JOIN campaigns ON campaigns.id = sales.campaign_id';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<Sale> */
    public function all(): array
    {
        $statement = $this->pdo->prepare(
            self::SELECT . ' ORDER BY sales.sale_date DESC, sales.id DESC'
        );
        $statement->execute();

        return array_map(
            static fn (array $row): Sale => Sale::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function find(int $id): ?Sale
    {
        $statement = $this->pdo->prepare(self::SELECT . ' WHERE sales.id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Sale::fromArray($row);
    }

    public function existsByReference(string $marketplace, string $reference): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM sales
             WHERE marketplace = :marketplace AND external_sale_reference = :reference
             LIMIT 1'
        );
        $statement->execute([
            'marketplace' => $marketplace,
            'reference' => $reference,
        ]);

        return $statement->fetchColumn() !== false;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Sale
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sales (
                marketplace, external_sale_reference, product_id, campaign_id, quantity,
                gross_value, commission_value, status, sale_date, imported_at
             ) VALUES (
                :marketplace, :external_sale_reference, :product_id, :campaign_id, :quantity,
                :gross_value, :commission_value, :status, :sale_date, :imported_at
             )'
        );
        $parameters = $data;
        $parameters['imported_at'] = $data['imported_at'] ?? null;
        $statement->execute($parameters);
        $sale = $this->find((int) $this->pdo->lastInsertId());

        if ($sale === null) {
            throw new RuntimeException('Venda criada não pôde ser recuperada.');
        }

        return $sale;
    }
}
