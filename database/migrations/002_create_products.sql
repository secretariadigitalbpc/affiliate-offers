USE affiliate_system;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    marketplace VARCHAR(30) NOT NULL,
    marketplace_product_id VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    image_url TEXT NULL,
    seller_name VARCHAR(255) NULL,
    rating DECIMAL(3,2) NULL,
    sales_count INT UNSIGNED NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_checked_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY products_marketplace_external_unique (marketplace, marketplace_product_id),
    KEY products_active_index (active),
    KEY products_title_index (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('002_create_products');

