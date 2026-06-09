# Koroni Portal Deployment

This project is prepared for Laravel Cloud with Neon Postgres.

## Production environment variables

Set these in Laravel Cloud environment settings:

```env
APP_NAME="Koroni Portal"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=

DB_CONNECTION=pgsql
DB_URL=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

Generate `APP_KEY` locally with:

```bash
php artisan key:generate --show
```

Use a rotated Neon connection string for `DB_URL`.

## Build command

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize
```

## Deploy command

```bash
php artisan migrate --force
php artisan db:seed --force
```

The deployment command intentionally runs migrations and seeders so the online preview always has the required departments, users, roles, approval rules, and portal bootstrap data.
