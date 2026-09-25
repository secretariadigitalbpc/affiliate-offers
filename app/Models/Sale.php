<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Sale
{
    public function __construct(
        public ?int $id,
        public string $marketplace,
        public ?string $externalSaleReference,
        public ?int $productId,
        public ?int $campaignId,
        public int $quantity,
        public string $grossValue,
        public string $commissionValue,
        public string $status,
        public string $saleDate,
        public ?string $importedAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $productTitle = null,
        public ?string $campaignName = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['marketplace'],
            isset($row['external_sale_reference']) ? (string) $row['external_sale_reference'] : null,
            isset($row['product_id']) ? (int) $row['product_id'] : null,
            isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            (int) $row['quantity'],
            (string) $row['gross_value'],
            (string) $row['commission_value'],
            (string) $row['status'],
            (string) $row['sale_date'],
            isset($row['imported_at']) ? (string) $row['imported_at'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
            isset($row['product_title']) ? (string) $row['product_title'] : null,
            isset($row['campaign_name']) ? (string) $row['campaign_name'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'marketplace' => $this->marketplace,
            'external_sale_reference' => $this->externalSaleReference,
            'product_id' => $this->productId,
            'campaign_id' => $this->campaignId,
            'quantity' => $this->quantity,
            'gross_value' => $this->grossValue,
            'commission_value' => $this->commissionValue,
            'status' => $this->status,
            'sale_date' => $this->saleDate,
            'imported_at' => $this->importedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'product_title' => $this->productTitle,
            'campaign_name' => $this->campaignName,
        ];
    }
}
