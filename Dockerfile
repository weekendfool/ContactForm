# PHP 8.3 CLI（Laravelの動作要件を満たすベースイメージ）
FROM php:8.3-cli

# Composerを公式イメージからそのまま拝借
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Laravel実行に必要な拡張機能（SQLite・mbstring・xml）をインストール
RUN apt-get update && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
        libonig-dev \
        libxml2-dev \
        unzip \
        git \
    && docker-php-ext-install pdo_sqlite mbstring xml \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist \
    && mkdir -p database \
    && touch database/database.sqlite

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
