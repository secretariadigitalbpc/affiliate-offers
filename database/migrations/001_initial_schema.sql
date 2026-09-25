CREATE DATABASE IF NOT EXISTS affiliate_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE affiliate_system;

CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(100) NOT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('001_initial_schema');

