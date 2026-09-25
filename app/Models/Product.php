<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Product
{
    public function __construct(
        public ?int $id,
        public string $marketplace,
        public string $marketplaceProductId,
        public string $title,
        public string $slug,
        public ?int $categoryId,
        public ?string $imageUrl,
        public ?string $sellerName,
        public ?string $rating,
        public ?int $salesCount,
        public bool $active,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $lastCheckedAt = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['marketplace'],
            (string) $row['marketplace_product_id'],
            (string) $row['title'],
            (string) $row['slug'],
            isset($row['category_id']) ? (int) $row['category_id'] : null,
            self::nullableString($row['image_url'] ?? null),
            self::nullableString($row['seller_name'] ?? null),
            self::nullableString($row['rating'] ?? null),
            isset($row['sales_count']) ? (int) $row['sales_count'] : null,
            (bool) $row['active'],
            self::nullableString($row['created_at'] ?? null),
            self::nullableString($row['updated_at'] ?? null),
            self::nullableString($row['last_checked_at'] ?? null),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'marketplace' => $this->marketplace,
            'marketplace_product_id' => $this->marketplaceProductId,
            'title' => $this->title,
            'slug' => $this->slug,
            'category_id' => $this->categoryId,
            'image_url' => $this->imageUrl,
            'seller_name' => $this->sellerName,
            'rating' => $this->rating,
            'sales_count' => $this->salesCount,
            'active' => $this->active,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_checked_at' => $this->lastCheckedAt,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}

