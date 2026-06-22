# Telepítés szerverre (Debian 13 + Apache2 + PHP-FPM + MySQL)

Ez a projekt egy **Laravel 12 + Breeze (Blade)** alkalmazás.

Tartalmaz:
- OpenAI chat integrációt
- Python subprocess-t a horoszkóp számításhoz (`python/horoscope_calc.py`)
- Frontend buildet Vite-tal (`npm run build`)

## 0) Előfeltételek

- Debian 13
- Apache2
- PHP 8.2+ (javasolt: 8.3)
- PHP-FPM (javasolt, Apache alatt is)
- MySQL/MariaDB
- Git
- Composer
- Node + npm (csak buildhez)
- Python 3.11+ (venv-hez is)

> Megjegyzés: a parancsok alatt a `sudo` használata feltételezi, hogy van sudo jogod.

## 1) Szükséges csomagok telepítése

Példa (PHP 8.3-ra; ha nálad 8.2 van, akkor a csomagnevek/sock útvonal változhat):

```bash
sudo apt update
sudo apt install -y \
  apache2 \
  git curl unzip \
  mysql-client \
  php php-cli php-fpm \
  php-mysql php-mbstring php-xml php-curl php-zip php-bcmath \
  composer \
  python3 python3-venv python3-pip \
  nodejs npm
```

Apache modulok (Laravelhez kell a rewrite, PHP-FPM proxyzás):

```bash
sudo a2enmod rewrite proxy_fcgi setenvif
```

## 2) Kód letöltése (git)

Ajánlott hely:

```bash
sudo mkdir -p /var/www/astro
sudo chown -R $USER:www-data /var/www/astro
cd /var/www/astro
git clone <A_REPO_URL> .
```

Ha már klónozva van és frissítesz:

```bash
cd /var/www/astro
git pull
```

## 3) Laravel környezeti fájl (.env)

```bash
cd /var/www/astro
cp .env.example .env
php artisan key:generate
```

Szerkeszd a `.env`-et (legalább ezeket):

```env
APP_NAME=Astro
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain.tld

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=astro
DB_USERNAME=astro
DB_PASSWORD=***

OPENAI_API_KEY=***
OPENAI_MODEL=gpt-4o-mini

# Python venv python bin (lásd 5. pont)
HOROSCOPE_PYTHON_BIN=/var/www/astro/.venv/bin/python
```

## 4) PHP függőségek (Composer)

```bash
cd /var/www/astro
composer install --no-dev --optimize-autoloader
```

## 5) Python környezet (venv) a horoszkóphoz

```bash
cd /var/www/astro
python3 -m venv .venv
source .venv/bin/activate

# Ha van requirements:
if [ -f python/requirements.txt ]; then pip install -r python/requirements.txt; fi

deactivate
```

> Ha nincs `python/requirements.txt`, akkor a `python/horoscope_calc.py` függőségeit külön kell feltenni.

## 6) Adatbázis + migráció

```bash
cd /var/www/astro
php artisan migrate --force
php artisan storage:link
```

## 7) Frontend build (Vite)

```bash
cd /var/www/astro
npm ci
npm run build
```

## 8) Cache optimalizálás (production)

```bash
cd /var/www/astro
php artisan optimize
```

## 9) Jogosultságok

Laravelnek írnia kell ide:
- `storage/`
- `bootstrap/cache/`

```bash
cd /var/www/astro
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

## 10) Apache VirtualHost (DocumentRoot = public)

Fontos: a `DocumentRoot` mindig a projekt `public/` könyvtára.

### 10.1) VHost fájl

Hozd létre:

```bash
sudo nano /etc/apache2/sites-available/astro.conf
```

Tartalom (PHP-FPM socketot igazítsd a saját PHP verziódhoz):

```apache
<VirtualHost *:80>
    ServerName domain.tld
    DocumentRoot /var/www/astro/public

    <Directory /var/www/astro/public>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost/"
        # Példa konkrét verzióra:
        # SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/astro_error.log
    CustomLog ${APACHE_LOG_DIR}/astro_access.log combined
</VirtualHost>
```

### 10.2) Engedélyezés + reload

```bash
sudo a2ensite astro
sudo systemctl reload apache2
```

## 11) HTTPS (Let’s Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d mitakarunk.hu
```

## 12) Smoke teszt

```bash
cd /var/www/astro
php artisan about
php artisan config:show services.openai
```

Weben:
- nyisd meg: `https://domain.tld`
- próbáld ki a chat-et és a horoszkóp számítást

## 13) Hibakeresés

Laravel log:
```bash
tail -f /var/www/astro/storage/logs/laravel.log
```

Apache log:
```bash
sudo tail -f /var/log/apache2/astro_error.log
```

## 14) Frissítés (deploy update)

```bash
cd /var/www/astro
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
sudo systemctl reload apache2
```
