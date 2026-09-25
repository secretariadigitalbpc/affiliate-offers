<?php

declare(strict_types=1);

namespace App\Models;

final readonly class PublicOffer
{
    public function __construct(
        public int $offerId,
        public int $productId,
        public int $affiliateLinkId,
        public string $title,
        public string $slug,
        public string $marketplace,
        public ?string $imageUrl,
        public ?string $sellerName,
        public string $price,
        public ?string $oldPrice,
        public string $discountPercentage,
        public ?string $couponText,
        public ?string $shippingText,
        public ?string $expiresAt,
        public string $affiliateUrl,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['offer_id'],
            (int) $row['product_id'],
            (int) $row['affiliate_link_id'],
            (string) $row['title'],
            (string) $row['slug'],
            (string) $row['marketplace'],
            isset($row['image_url']) ? (string) $row['image_url'] : null,
            isset($row['seller_name']) ? (string) $row['seller_name'] : null,
            (string) $row['price'],
            isset($row['old_price']) ? (string) $row['old_price'] : null,
            (string) $row['discount_percentage'],
            isset($row['coupon_text']) ? (string) $row['coupon_text'] : null,
            isset($row['shipping_text']) ? (string) $row['shipping_text'] : null,
            isset($row['expires_at']) ? (string) $row['expires_at'] : null,
            (string) $row['affiliate_url'],
        );
    }
}
