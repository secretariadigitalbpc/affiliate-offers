<?php

declare(strict_types=1);

namespace App\Models;

final readonly class AffiliateLink
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public string $marketplace,
        public string $affiliateUrl,
        public ?int $campaignId,
        public ?string $tag,
        public bool $active,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $productTitle = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['product_id'],
            (string) $row['marketplace'],
            (string) $row['affiliate_url'],
            isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            isset($row['tag']) ? (string) $row['tag'] : null,
            (bool) $row['active'],
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
            isset($row['product_title']) ? (string) $row['product_title'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'marketplace' => $this->marketplace,
            'affiliate_url' => $this->affiliateUrl,
            'campaign_id' => $this->campaignId,
            'tag' => $this->tag,
            'active' => $this->active,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'product_title' => $this->productTitle,
        ];
    }
}

