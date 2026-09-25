USE affiliate_system;

CREATE TABLE IF NOT EXISTS offers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    affiliate_link_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    old_price DECIMAL(12,2) NULL,
    discount_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    coupon_text VARCHAR(255) NULL,
    shipping_text VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY offers_product_index (product_id),
    KEY offers_affiliate_link_index (affiliate_link_id),
    KEY offers_status_period_index (status, starts_at, expires_at),
    CONSTRAINT offers_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT offers_affiliate_link_foreign
        FOREIGN KEY (affiliate_link_id) REFERENCES affiliate_links (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT offers_status_check
        CHECK (status IN ('draft', 'approved', 'published', 'expired', 'rejected')),
    CONSTRAINT offers_price_check CHECK (price > 0),
    CONSTRAINT offers_old_price_check CHECK (old_price IS NULL OR old_price >= price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('005_create_offers');

