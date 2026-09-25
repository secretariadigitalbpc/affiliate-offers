USE affiliate_system;

CREATE TABLE IF NOT EXISTS sales (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    marketplace VARCHAR(30) NOT NULL,
    external_sale_reference VARCHAR(255) NULL,
    product_id BIGINT UNSIGNED NULL,
    campaign_id BIGINT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    gross_value DECIMAL(12,2) NOT NULL,
    commission_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    sale_date DATETIME NOT NULL,
    imported_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY sales_marketplace_reference_unique (marketplace, external_sale_reference),
    KEY sales_product_date_index (product_id, sale_date),
    KEY sales_campaign_date_index (campaign_id, sale_date),
    KEY sales_status_date_index (status, sale_date),
    CONSTRAINT sales_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT sales_campaign_foreign
        FOREIGN KEY (campaign_id) REFERENCES campaigns (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT sales_marketplace_check
        CHECK (marketplace IN ('mercado_livre', 'shopee')),
    CONSTRAINT sales_status_check
        CHECK (status IN ('pending', 'approved', 'cancelled', 'refunded')),
    CONSTRAINT sales_quantity_check CHECK (quantity > 0),
    CONSTRAINT sales_gross_value_check CHECK (gross_value > 0),
    CONSTRAINT sales_commission_value_check
        CHECK (commission_value >= 0 AND commission_value <= gross_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('008_create_sales');
