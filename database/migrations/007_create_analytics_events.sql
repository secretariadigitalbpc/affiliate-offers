USE affiliate_system;

CREATE TABLE IF NOT EXISTS page_views (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    offer_id BIGINT UNSIGNED NULL,
    campaign_id BIGINT UNSIGNED NULL,
    source VARCHAR(100) NULL,
    session_id VARCHAR(128) NULL,
    user_agent_family VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY page_views_product_created_index (product_id, created_at),
    KEY page_views_offer_created_index (offer_id, created_at),
    KEY page_views_campaign_created_index (campaign_id, created_at),
    CONSTRAINT page_views_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT page_views_offer_foreign
        FOREIGN KEY (offer_id) REFERENCES offers (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT page_views_campaign_foreign
        FOREIGN KEY (campaign_id) REFERENCES campaigns (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clicks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    offer_id BIGINT UNSIGNED NULL,
    affiliate_link_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED NULL,
    source VARCHAR(100) NULL,
    session_id VARCHAR(128) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY clicks_product_created_index (product_id, created_at),
    KEY clicks_offer_created_index (offer_id, created_at),
    KEY clicks_affiliate_link_created_index (affiliate_link_id, created_at),
    KEY clicks_campaign_created_index (campaign_id, created_at),
    CONSTRAINT clicks_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT clicks_offer_foreign
        FOREIGN KEY (offer_id) REFERENCES offers (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT clicks_affiliate_link_foreign
        FOREIGN KEY (affiliate_link_id) REFERENCES affiliate_links (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT clicks_campaign_foreign
        FOREIGN KEY (campaign_id) REFERENCES campaigns (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('007_create_analytics_events');
