# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — build front-end assets (Vite + Tailwind)
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2 — PHP application (Laravel 12 + SQLite)
# ---------------------------------------------------------------------------
FROM php:8.2-cli AS app

# System deps + PHP extensions required by the app.
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip libsqlite3-dev libzip-dev libpng-dev \
    && docker-php-ext-install pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official image).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies first (better layer caching).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# Application source + built assets.
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev

# Prepare a ready-to-run demo environment with a freshly seeded SQLite database.
RUN cp .env.example .env \
    && php artisan key:generate \
    && mkdir -p database \
    && touch database/database.sqlite \
    && php artisan migrate --force --seed \
    && chmod -R ug+rw storage bootstrap/cache database

ENV APP_ENV=production
ENV APP_DEBUG=false

# Render (and most PaaS) inject $PORT. Default to 8000 for local runs.
EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
