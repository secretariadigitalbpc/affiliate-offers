<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Offer
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public int $affiliateLinkId,
        public string $price,
        public ?string $oldPrice,
        public string $discountPercentage,
        public ?string $couponText,
        public ?string $shippingText,
        public string $status,
        public ?string $startsAt,
        public ?string $expiresAt,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $productTitle = null,
        public ?string $affiliateUrl = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['product_id'],
            (int) $row['affiliate_link_id'],
            (string) $row['price'],
            isset($row['old_price']) ? (string) $row['old_price'] : null,
            (string) $row['discount_percentage'],
            isset($row['coupon_text']) ? (string) $row['coupon_text'] : null,
            isset($row['shipping_text']) ? (string) $row['shipping_text'] : null,
            (string) $row['status'],
            isset($row['starts_at']) ? (string) $row['starts_at'] : null,
            isset($row['expires_at']) ? (string) $row['expires_at'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
            isset($row['product_title']) ? (string) $row['product_title'] : null,
            isset($row['affiliate_url']) ? (string) $row['affiliate_url'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'affiliate_link_id' => $this->affiliateLinkId,
            'price' => $this->price,
            'old_price' => $this->oldPrice,
            'discount_percentage' => $this->discountPercentage,
            'coupon_text' => $this->couponText,
            'shipping_text' => $this->shippingText,
            'status' => $this->status,
            'starts_at' => $this->startsAt,
            'expires_at' => $this->expiresAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'product_title' => $this->productTitle,
            'affiliate_url' => $this->affiliateUrl,
        ];
    }
}

