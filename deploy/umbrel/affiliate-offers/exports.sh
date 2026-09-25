export APP_AFFILIATE_OFFERS_DB_PASSWORD="$(derive_entropy "${app_entropy_identifier}-database-password")"
export APP_AFFILIATE_OFFERS_DB_ROOT_PASSWORD="$(derive_entropy "${app_entropy_identifier}-database-root-password")"
