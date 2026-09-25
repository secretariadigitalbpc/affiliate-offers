USE affiliate_system;

CREATE TABLE IF NOT EXISTS affiliate_links (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    marketplace VARCHAR(30) NOT NULL,
    affiliate_url TEXT NOT NULL,
    campaign_id BIGINT UNSIGNED NULL,
    tag VARCHAR(100) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY affiliate_links_product_index (product_id),
    KEY affiliate_links_marketplace_active_index (marketplace, active),
    CONSTRAINT affiliate_links_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('004_create_affiliate_links');

