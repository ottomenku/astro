# Deploy runbook (Debian 13 + Apache2 + PHP-FPM + MySQL)

Ez a fájl egy **gyakorlati, hibákat is lefedő** telepítési/runbook leírás az Astro (Laravel) projekthez.

Kiemelten tartalmazza a tapasztalatok alapján gyakori buktatókat:
- első user/admin létrehozása (ha nincs regisztráció)
- Python/swisseph (pyswisseph) telepítése és a `HOROSCOPE_PYTHON_BIN` helyes megadása
- cache és PHP-FPM újraindítás, ha `.env` változik

---

## 0) Rövid checklista (ha már fent van a szerveren)

**Web működik, de valami nem?**

1. Log:
   ```bash
   tail -n 80 /var/www/astro/storage/logs/laravel.log
   ```
2. Cache reset:
   ```bash
   cd /var/www/astro
   php artisan optimize:clear
   ```
3. Ha `.env`-et módosítottál: **PHP-FPM restart**:
   ```bash
   sudo systemctl restart php8.4-fpm || sudo systemctl restart php8.3-fpm || sudo systemctl restart php8.2-fpm
   sudo systemctl reload apache2
   ```

---

## 1) Követelmények / csomagok

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

Apache modulok:

```bash
sudo a2enmod rewrite proxy_fcgi setenvif
```

---

## 2) Kód letöltés

```bash
sudo mkdir -p /var/www/astro
sudo chown -R $USER:www-data /var/www/astro
cd /var/www/astro
git clone <A_REPO_URL> .
```

---

## 3) .env beállítás (fontos!)

```bash
cd /var/www/astro
cp .env.example .env
php artisan key:generate
```

Minimum:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mitakarunk.hu

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=astro
DB_USERNAME=astro
DB_PASSWORD=***

OPENAI_API_KEY=***
OPENAI_MODEL=gpt-4o-mini

# FONTOS: csak az útvonal legyen, ne legyen benne "HOROSCOPE_PYTHON_BIN=" még egyszer!
HOROSCOPE_PYTHON_BIN=/var/www/astro/.venv/bin/python
```

**Gyakori hiba (rossz):**

```env
HOROSCOPE_PYTHON_BIN=HOROSCOPE_PYTHON_BIN=/var/www/astro/.venv/bin/python
```

`.env` módosítás után:

```bash
cd /var/www/astro
php artisan optimize:clear
sudo systemctl restart php8.4-fpm || sudo systemctl restart php8.3-fpm || sudo systemctl restart php8.2-fpm
sudo systemctl reload apache2
```

---

## 4) Composer

```bash
cd /var/www/astro
composer install --no-dev --optimize-autoloader
```

---

## 5) Python venv + pyswisseph (kötelező a horoszkóphoz)

```bash
cd /var/www/astro
python3 -m venv .venv
source .venv/bin/activate
pip install --upgrade pip
pip install pyswisseph
deactivate
```

Gyors ellenőrzés:

```bash
/var/www/astro/.venv/bin/python -c "import swisseph; print('swisseph OK')"
```

---

## 6) DB migráció

```bash
cd /var/www/astro
php artisan migrate --force
php artisan storage:link
```

---

## 7) NPM build (Vite)

```bash
cd /var/www/astro
npm ci
npm run build
```

---

## 8) Cache (production)

```bash
cd /var/www/astro
php artisan optimize
```

---

## 9) Jogosultságok

```bash
cd /var/www/astro
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

---

## 10) Apache VirtualHost (DocumentRoot = public)

VHost fájl:

```bash
sudo nano /etc/apache2/sites-available/astro.conf
```

Példa:

```apache
<VirtualHost *:80>
    ServerName mitakarunk.hu
    DocumentRoot /var/www/astro/public

    <Directory /var/www/astro/public>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        # Állítsd be a saját FPM socketedet (ls -la /run/php/)
        SetHandler "proxy:unix:/run/php/php8.4-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/astro_error.log
    CustomLog ${APACHE_LOG_DIR}/astro_access.log combined
</VirtualHost>
```

Aktiválás:

```bash
sudo a2ensite astro
sudo systemctl reload apache2
```

---

## 11) HTTPS (Let’s Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d mitakarunk.hu
```

---

## 12) Első admin user létrehozása (ha az `users` tábla üres)

Ha nincs regisztráció gomb, vagy a tábla üres, CLI-ből hozz létre admin usert:

```bash
cd /var/www/astro
php artisan tinker
```

Tinkerben:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
  'name' => 'Admin',
  'email' => 'admin@mitakarunk.hu',
  'password' => Hash::make('VALAMI_EROS_JELSZO'),
  'role' => 'admin',
  'token_quota_total' => 0,
  'token_quota_used' => 0,
]);
```

---

## 13) Hibaelhárítás

### 13.1 Horoszkóp számítás sikertelen

Logban tipikusan ez látszik:

- `ModuleNotFoundError: No module named 'swisseph'`  → hiányzik a `pyswisseph`
- `python":"/usr/bin/python3"` → nem a venv python fut

Mit tegyél:
1) `pip install pyswisseph` a venv-be (5. pont)
2) `.env`: `HOROSCOPE_PYTHON_BIN=/var/www/astro/.venv/bin/python`
3) `php artisan optimize:clear` + PHP-FPM restart

### 13.3 Horoszkóp számítás sikertelen (Python / swisseph)

**Tünet:** „A horoszkóp számítás sikertelen” az oldalon.

**Diagnosztika:**

```bash
cd /var/www/astro

# .env-ben legyen (dupla prefix NEM):
grep HOROSCOPE_PYTHON_BIN .env

# Python + swisseph működik?
/var/www/astro/.venv/bin/python -c "import swisseph; print('swisseph OK')"

# www-data is el tudja indítani?
sudo -u www-data /var/www/astro/.venv/bin/python -c "import swisseph; print('OK')"

# Teszt számítás stdin-nel
echo '{"natal":{"datetime_utc":"1990-05-15T12:30:00Z","lat":47.5,"lon":19.0},"transit":{"datetime_utc":"2026-06-29T12:00:00Z","lat":47.5,"lon":19.0},"sidereal":false,"ayanamsa":"lahiri","house_system":"placidus"}' | sudo -u www-data /var/www/astro/.venv/bin/python /var/www/astro/python/horoscope_calc.py | head -c 200

# Laravel log
tail -n 30 storage/logs/laravel.log | grep -i horoscope
```

**Telepítés (ha a venv hiányzik vagy nincs swisseph):**

```bash
cd /var/www/astro
python3 -m venv .venv
sudo -u www-data .venv/bin/pip install --upgrade pip
sudo -u www-data .venv/bin/pip install -r python/requirements.txt
```

**.env** (csak az útvonal!):

```env
HOROSCOPE_PYTHON_BIN=/var/www/astro/.venv/bin/python
```

Cache + PHP-FPM (config cache után kötelező):

```bash
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize
sudo systemctl restart php8.4-fpm || sudo systemctl restart php8.3-fpm
sudo systemctl reload apache2
```

---

### 13.2 Hol a php-fpm socket?

```bash
ls -la /run/php/
systemctl status php*-fpm --no-pager
```

---

## 14) Frissítés (deploy update)

```bash
cd /var/www/astro
git pull
sudo mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
composer install --no-dev --optimize-autoloader --no-scripts
sudo -u www-data php artisan package:discover --ansi
sudo -u www-data php artisan migrate --force
npm ci
npm run build
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
sudo systemctl reload apache2
```

### 14.1 Gyakori hiba: PailServiceProvider / Permission denied (log)

Ha ezt látod:
- `Class "Laravel\Pail\PailServiceProvider" not found`
- `storage/logs/laravel.log ... Permission denied`

**Ok:** elavult cache (`bootstrap/cache`) dev csomagokkal, vagy a `storage/` nem írható a web usernek.

**Javítás** — **először jogosultság**, utána composer:

```bash
cd /var/www/astro

# 1) Cache kézzel — artisan nélkül (sudo, ha root tulajdonban van)
sudo rm -f bootstrap/cache/packages.php
sudo rm -f bootstrap/cache/services.php
sudo rm -f bootstrap/cache/config.php
sudo rm -f bootstrap/cache/routes-v7.php

# 2) Jogosultságok
sudo mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
sudo touch storage/logs/laravel.log
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
sudo chmod 664 storage/logs/laravel.log

# 3) Függőségek — --no-scripts, mert a post-script azonnal artisan-t hív
composer install --no-dev --optimize-autoloader --no-scripts

# 4) Artisan www-data userrel
sudo -u www-data php artisan package:discover --ansi
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize

# 5) Apache
sudo systemctl reload apache2
```

**Alternatíva:** deploy user + www-data csoport (utána `composer install` script nélkül is mehet):

```bash
sudo usermod -aG www-data "$USER"
sudo chown -R "$USER":www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
# kijelentkezés/bejelentkezés vagy: newgrp www-data
composer install --no-dev --optimize-autoloader
```

**Ellenőrzés:**

```bash
sudo -u www-data php artisan about
ls -la storage/logs/laravel.log
```
