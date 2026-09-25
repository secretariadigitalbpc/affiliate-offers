<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use PDO;
use RuntimeException;

final class ProductRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<Product> */
    public function all(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM products ORDER BY created_at DESC, id DESC'
        );
        $statement->execute();

        return array_map(
            static fn (array $row): Product => Product::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function find(int $id): ?Product
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Product::fromArray($row);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Product
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products (
                marketplace, marketplace_product_id, title, slug, category_id,
                image_url, seller_name, rating, sales_count, active
            ) VALUES (
                :marketplace, :marketplace_product_id, :title, :slug, :category_id,
                :image_url, :seller_name, :rating, :sales_count, :active
            )'
        );
        $statement->execute($this->persistenceData($data));

        $product = $this->find((int) $this->pdo->lastInsertId());

        if ($product === null) {
            throw new RuntimeException('Produto criado não pôde ser recuperado.');
        }

        return $product;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?Product
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET
                marketplace = :marketplace,
                marketplace_product_id = :marketplace_product_id,
                title = :title,
                slug = :slug,
                category_id = :category_id,
                image_url = :image_url,
                seller_name = :seller_name,
                rating = :rating,
                sales_count = :sales_count,
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
            'marketplace' => $data['marketplace'],
            'marketplace_product_id' => $data['marketplace_product_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
            'image_url' => $data['image_url'],
            'seller_name' => $data['seller_name'],
            'rating' => $data['rating'],
            'sales_count' => $data['sales_count'],
            'active' => $data['active'] ? 1 : 0,
        ];
    }
}

