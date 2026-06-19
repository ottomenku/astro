
194.182.83.30
otto  111111
root a111111 (nem biztos)
elfelejtett mariadb jelszó:
ALTER USER 'horoscope'@'localhost' IDENTIFIED BY 'a111111';
FLUSH PRIVILEGES;
EXIT;


1. Új felhasználó létrehozása

Például otto néven:

adduser otto

Add sudo csoporthoz:

usermod -aG sudo otto

Teszt:

su - otto
sudo whoami

Ha root-ot ír ki, működik.

2. SSH kulcs átmásolása

Ha már kulccsal lépsz be rootként:

Rootként:

rsync --archive --chown=otto:otto ~/.ssh /home/otto

Jogosultságok:

chmod 700 /home/otto/.ssh
chmod 600 /home/otto/.ssh/authorized_keys

Próbálj bejelentkezni:

ssh otto@SERVER_IP
3. Root SSH tiltása

Szerkesztés:

sudo nano /etc/ssh/sshd_config

Állítsd be:

PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes

SSH újraindítása:

sudo systemctl restart ssh

Figyelem: csak akkor tiltsd a root belépést, ha az új felhasználóval már biztosan be tudsz lépni!

4. Frissítés
sudo apt update
sudo apt upgrade -y
sudo apt autoremove -y
5. Alapcsomagok
sudo apt install -y \
curl wget unzip zip git mc htop nano ufw fail2ban \
software-properties-common ca-certificates apt-transport-https
6. Tűzfal
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

Ellenőrzés:

sudo ufw status
7. Apache
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2

Laravelhez érdemes:

sudo a2enmod rewrite headers ssl
sudo systemctl restart apache2
8. PHP 8.4

Debian 13 esetén általában elérhető:

sudo apt install -y \
php php-cli php-common php-fpm \
php-mysql php-curl php-xml php-mbstring \
php-zip php-intl php-gd php-bcmath \
php-soap php-readline php-opcache

Ellenőrzés:

php -v
9. Composer
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
10. Node.js + npm

Laravelhez ajánlom az LTS verziót:

curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs

Ellenőrzés:

node -v
npm -v
11. MySQL vagy MariaDB

Én MariaDB-t javaslok:

sudo apt install mariadb-server -y
sudo mysql_secure_installation

Laravel adatbázis:

CREATE DATABASE horoscope CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'horoscope'@'localhost'
IDENTIFIED BY 'EROS_JELSZO';

GRANT ALL PRIVILEGES ON horoscope.* TO 'horoscope'@'localhost';

FLUSH PRIVILEGES;
12. phpMyAdmin
sudo apt install phpmyadmin -y

Ha nem linkeli automatikusan:

sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin

Elérés:

https://domain.hu/phpmyadmin
13. Python környezet

LLM-es vagy crawler projektekhez:

sudo apt install -y python3 python3-pip python3-venv

Virtuális környezet:

python3 -m venv venv
source venv/bin/activate
14. Redis (nagyon ajánlott Laravelhez)
sudo apt install redis-server -y
sudo systemctl enable redis-server
-------------------------------------------------------------
ha lesz laravel:
Laravel:

composer require predis/predis

Használható:

cache
queue
session
rate limit
15. Supervisor (queue worker)
sudo apt install supervisor -y

Laravel queue futtatására kiváló.

16. Certbot (ingyen SSL)
sudo apt install certbot python3-certbot-apache -y

Tanúsítvány:

sudo certbot --apache
17. Laravel ajánlott struktúra
/var/www/
    horoscope/
        public/
        storage/
        vendor/

Jogosultságok:

sudo chown -R www-data:www-data /var/www/horoscope
sudo chmod -R 775 storage bootstrap/cache
Amit még telepítenék
Redis
Supervisor
Fail2ban
Certbot
Git
Python
Node.js
Composer