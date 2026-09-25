#!/bin/sh
set -eu

required_variables="DB_HOST DB_PORT DB_NAME DB_USER DB_PASS ADMIN_BOOTSTRAP_EMAIL ADMIN_BOOTSTRAP_PASSWORD"

for variable in $required_variables; do
    eval "value=\${$variable:-}"

    if [ -z "$value" ]; then
        echo "Configuração obrigatória ausente: $variable" >&2
        exit 1
    fi
done

attempt=0
until php -r '
    try {
        new PDO(
            sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", getenv("DB_HOST"), getenv("DB_PORT"), getenv("DB_NAME")),
            getenv("DB_USER"),
            getenv("DB_PASS"),
            [PDO::ATTR_TIMEOUT => 2]
        );
    } catch (Throwable) {
        exit(1);
    }
'; do
    attempt=$((attempt + 1))

    if [ "$attempt" -ge 60 ]; then
        echo "Banco indisponível após 60 tentativas." >&2
        exit 1
    fi

    sleep 2
done

php database/migrate.php
php database/seeds/ensure_admin.php

mkdir -p storage/logs storage/imports storage/backups
chown -R www-data:www-data storage

exec "$@"
