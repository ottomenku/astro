# VPS deploy (MVP)

Ez egy Laravel 12 + Breeze (Blade) alapú app, Python subprocess-szel horoszkóp számításhoz, és OpenAI chat API-val.

## Követelmények

- PHP 8.2+
- Composer
- MySQL / MariaDB (vagy Postgres)
- Python 3.11+ (a `python/horoscope_calc.py` futtatásához)
- Node NEM kötelező a futtatáshoz, csak buildhez (ha Vite buildet szeretnél). Jelen repo-ban `npm` nincs elérhető helyben.

## .env

Indulásnak másold át:

```bash
cp .env.example .env
```

Majd állítsd be legalább ezeket:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=astro
DB_USERNAME=astro
DB_PASSWORD=***

OPENAI_API_KEY=***
OPENAI_MODEL=gpt-4o-mini

# python bin / venv python
HOROSCOPE_PYTHON_BIN=/opt/astro/.venv/bin/python
```

## Telepítés

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Webszerver

### Nginx + PHP-FPM (javasolt)

- `public/` legyen a document root.
- Forward `index.php`-ba.

### Queue / scheduler

Jelenleg nincs dedikált queue job / scheduler feladat.

## Admin felhasználó

Local környezetben készítettünk egy admin usert:

- email: `admin@example.com`
- jelszó: `admin123`

Élesben hozz létre egy saját admin usert, vagy CLI-n tinkerrel állítsd be.
