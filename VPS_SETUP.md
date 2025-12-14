# VPS Setup Guide for OnShelf GTDL

This guide will help you set up the OnShelf GTDL Laravel project on your VPS after cloning from GitHub.

## Prerequisites

Make sure your VPS has the following installed:
- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL/MariaDB
- Nginx or Apache
- Git

## Step-by-Step Setup Commands

### 1. Clone the Repository

```bash
cd /var/www  # or your preferred directory
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git onshelf-web
cd onshelf-web
```

### 2. Install PHP Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

**Note:** Use `--no-dev` for production. For development, use `composer install` without flags.

### 3. Install Node Dependencies and Build Assets

```bash
npm install
npm run build
```

### 4. Set Up Environment File

```bash
cp .env.example .env
# Or if .env.example doesn't exist, create .env manually
```

### 5. Configure Environment Variables

Edit the `.env` file with your VPS settings:

```bash
nano .env
```

**Important variables to configure:**
```env
APP_NAME="OnShelf GTDL"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=onshelf_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Create Database

```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE onshelf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'your_db_user'@'localhost' IDENTIFIED BY 'your_db_password';
GRANT ALL PRIVILEGES ON onshelf_db.* TO 'your_db_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 8. Run Database Migrations

```bash
php artisan migrate
```

If you have seeders:
```bash
php artisan db:seed
```

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Set Proper Permissions

```bash
# Set ownership (replace 'www-data' with your web server user if different)
sudo chown -R www-data:www-data /var/www/onshelf-web

# Set directory permissions
sudo find /var/www/onshelf-web -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/onshelf-web -type f -exec chmod 644 {} \;

# Set special permissions for storage and cache
sudo chmod -R 775 /var/www/onshelf-web/storage
sudo chmod -R 775 /var/www/onshelf-web/bootstrap/cache
```

### 11. Optimize Application (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 12. Set Up Scheduled Tasks (Cron Job)

Add Laravel's scheduler to your crontab:

```bash
crontab -e
```

Add this line (replace the path with your actual project path):
```bash
* * * * * cd /var/www/onshelf-web && php artisan schedule:run >> /dev/null 2>&1
```

### 13. Configure Web Server

#### For Nginx:

Create a configuration file:
```bash
sudo nano /etc/nginx/sites-available/onshelf
```

Add this configuration (adjust paths and domain):
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/onshelf-web/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/onshelf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### For Apache:

Enable required modules:
```bash
sudo a2enmod rewrite
sudo a2enmod headers
```

Create a virtual host configuration:
```bash
sudo nano /etc/apache2/sites-available/onshelf.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/onshelf-web/public

    <Directory /var/www/onshelf-web/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/onshelf_error.log
    CustomLog ${APACHE_LOG_DIR}/onshelf_access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite onshelf.conf
sudo systemctl reload apache2
```

### 14. Set Up SSL Certificate (Optional but Recommended)

Using Let's Encrypt with Certbot:
```bash
sudo apt install certbot python3-certbot-nginx  # For Nginx
# OR
sudo apt install certbot python3-certbot-apache  # For Apache

sudo certbot --nginx -d your-domain.com -d www.your-domain.com
# OR
sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

### 15. Test the Application

Visit your domain in a browser:
```
http://your-domain.com
```

## Quick Setup Script

You can also create a setup script to automate most of these steps:

```bash
#!/bin/bash
# save as setup.sh

echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

echo "Installing NPM dependencies..."
npm install

echo "Building assets..."
npm run build

echo "Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Please edit .env file with your configuration"
    exit 1
fi

echo "Generating application key..."
php artisan key:generate

echo "Running migrations..."
php artisan migrate

echo "Creating storage link..."
php artisan storage:link

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setting permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache

echo "Setup complete!"
```

Make it executable and run:
```bash
chmod +x setup.sh
./setup.sh
```

## Troubleshooting

### Permission Issues
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

## Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Configure proper file permissions
- [ ] Set up SSL certificate
- [ ] Configure firewall rules
- [ ] Keep dependencies updated
- [ ] Set up regular backups

## Maintenance Commands

### Update Dependencies
```bash
composer update --no-dev
npm update
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Backup Database
```bash
mysqldump -u your_db_user -p onshelf_db > backup_$(date +%Y%m%d).sql
```

---

**Note:** Replace all placeholder values (your-domain.com, database credentials, etc.) with your actual VPS configuration.

