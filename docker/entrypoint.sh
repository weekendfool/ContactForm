#!/bin/sh
set -e

# .envが無ければ.env.exampleから生成
if [ ! -f .env ]; then
    cp .env.example .env
fi

# SQLiteのDBファイルが無ければ作成
mkdir -p database
touch database/database.sqlite

# APP_KEYが未設定なら生成
if ! grep -q '^APP_KEY=base64' .env; then
    php artisan key:generate --force
fi

# マイグレーション実行（既に適用済みのものはスキップされる）
php artisan migrate --force

# 開発サーバーを0.0.0.0で起動し、コンテナ外（ホスト）からアクセスできるようにする
exec php artisan serve --host=0.0.0.0 --port=8000
