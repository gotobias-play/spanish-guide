# Spanish English Learning App - Deployment Guide

## Overview

This document provides comprehensive deployment instructions, environment setup, and operational guidance for the Spanish English Learning App platform.

## System Requirements

### Minimum Requirements
- **PHP**: 8.1 or higher with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **Node.js**: 18.x or higher with NPM
- **Database**: MySQL 8.0+ or PostgreSQL 14+ or SQLite 3.8+
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Memory**: 2GB RAM minimum, 4GB recommended
- **Storage**: 10GB minimum, SSD recommended

### Recommended Production Requirements
- **PHP**: 8.2+ with OPcache enabled
- **Database**: MySQL 8.0+ with replication
- **Cache**: Redis 6.0+ for sessions and caching
- **Memory**: 8GB+ RAM for high-traffic usage
- **Storage**: 50GB+ SSD with backup strategy
- **CDN**: CloudFlare or AWS CloudFront for asset delivery

## Environment Setup

### Development Environment

#### 1. Clone Repository
```bash
git clone <repository-url> spanish-app
cd spanish-app
```

#### 2. Backend Setup
```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed
```

#### 3. Frontend Setup
```bash
# Install Node.js dependencies (use legacy-peer-deps for compatibility)
npm install --legacy-peer-deps

# Build assets for development
npm run dev
```

#### 4. Development Server
```bash
# Start Laravel development server
php artisan serve

# In separate terminal, start Vite development server
npm run dev
```

### Production Environment

#### 1. Server Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required software
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring redis-server

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 2. Application Deployment
```bash
# Clone repository to production directory
sudo mkdir -p /var/www/spanish-app
cd /var/www/spanish-app
git clone <repository-url> .

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci --legacy-peer-deps

# Build production assets
npm run build

# Set correct permissions
sudo chown -R www-data:www-data /var/www/spanish-app
sudo chmod -R 755 /var/www/spanish-app
sudo chmod -R 775 /var/www/spanish-app/storage
sudo chmod -R 775 /var/www/spanish-app/bootstrap/cache
```

#### 3. Environment Configuration
```bash
# Copy and configure environment file
cp .env.example .env
nano .env
```

#### Production Environment Variables
```env
# Application
APP_NAME="Spanish English Learning App"
APP_ENV=production
APP_KEY=base64:YOUR_32_CHARACTER_SECRET_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spanish_app
DB_USERNAME=spanish_user
DB_PASSWORD=secure_password

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Broadcasting (for real-time features)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_cluster

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls

# File Storage (if using cloud storage)
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-west-2
AWS_BUCKET=your_bucket_name
```

## Database Setup

### MySQL Production Setup

#### 1. Create Database and User
```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE spanish_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER 'spanish_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant permissions
GRANT ALL PRIVILEGES ON spanish_app.* TO 'spanish_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 2. Run Migrations and Seeders
```bash
# Run database migrations
php artisan migrate

# Seed with initial data
php artisan db:seed

# Create admin user (optional)
php artisan tinker
>>> \App\Models\User::create([
...     'name' => 'Administrator',
...     'email' => 'admin@example.com',
...     'password' => \Illuminate\Support\Facades\Hash::make('secure_password'),
...     'is_admin' => true,
...     'email_verified_at' => now(),
... ]);
```

### PostgreSQL Setup (Alternative)
```sql
-- Create database
CREATE DATABASE spanish_app WITH ENCODING 'UTF8' LC_COLLATE='en_US.UTF-8' LC_CTYPE='en_US.UTF-8';

-- Create user
CREATE USER spanish_user WITH PASSWORD 'secure_password';

-- Grant permissions
GRANT ALL PRIVILEGES ON DATABASE spanish_app TO spanish_user;
```

## Web Server Configuration

### Nginx Configuration
```nginx
# /etc/nginx/sites-available/spanish-app
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/spanish-app/public;

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

    # Asset caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
}

# Enable the site
sudo ln -s /etc/nginx/sites-available/spanish-app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### SSL Certificate Setup (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (already configured by default)
sudo systemctl status certbot.timer
```

## Multi-Tenant Configuration

### Subdomain Setup
```nginx
# Multi-tenant subdomain configuration
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name *.yourdomain.com yourdomain.com;
    root /var/www/spanish-app/public;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Standard Laravel configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Tenant Database Setup
```php
// Create tenant via Artisan command
php artisan tenant:create school-name --subdomain=school --plan=premium
```

## Performance Optimization

### PHP-FPM Configuration
```ini
# /etc/php/8.2/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

# /etc/php/8.2/fpm/php.ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### Redis Configuration
```bash
# /etc/redis/redis.conf
maxmemory 1gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### Laravel Optimization
```bash
# Production optimization commands
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Queue worker setup (systemd service)
sudo nano /etc/systemd/system/laravel-worker.service
```

#### Laravel Worker Service
```ini
[Unit]
Description=Laravel queue worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/spanish-app/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start the worker service
sudo systemctl enable laravel-worker.service
sudo systemctl start laravel-worker.service
```

## Monitoring & Maintenance

### Log Management
```bash
# Configure log rotation
sudo nano /etc/logrotate.d/laravel

# Laravel log rotation configuration
/var/www/spanish-app/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 664 www-data www-data
}
```

### Backup Strategy
```bash
#!/bin/bash
# /usr/local/bin/backup-spanish-app.sh

# Database backup
mysqldump -u spanish_user -p'secure_password' spanish_app > /backup/spanish_app_$(date +%Y%m%d_%H%M%S).sql

# Application backup
tar -czf /backup/spanish_app_files_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www spanish-app

# Remove backups older than 30 days
find /backup -name "spanish_app_*" -mtime +30 -delete

# Add to crontab
# 0 2 * * * /usr/local/bin/backup-spanish-app.sh
```

### Health Monitoring
```bash
# Create health check script
# /usr/local/bin/health-check.sh

#!/bin/bash
curl -f http://localhost/api/health || exit 1
```

### Application Updates
```bash
#!/bin/bash
# Deployment script for updates

cd /var/www/spanish-app

# Put application in maintenance mode
php artisan down

# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm ci --legacy-peer-deps

# Build assets
npm run build

# Update database
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart laravel-worker

# Bring application back online
php artisan up
```

## Security Considerations

### File Permissions
```bash
# Set secure permissions
sudo chown -R www-data:www-data /var/www/spanish-app
sudo chmod -R 755 /var/www/spanish-app
sudo chmod -R 775 /var/www/spanish-app/storage
sudo chmod -R 775 /var/www/spanish-app/bootstrap/cache
sudo chmod 600 /var/www/spanish-app/.env
```

### Firewall Configuration
```bash
# Configure UFW firewall
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw deny 3306  # Deny direct database access
```

### Regular Security Updates
```bash
# System updates
sudo apt update && sudo apt upgrade -y

# Laravel security updates
composer update --with-dependencies

# Node.js security updates
npm audit fix
```

## Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
# Fix storage and cache permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

#### 2. Queue Worker Issues
```bash
# Restart queue workers
sudo systemctl restart laravel-worker

# Check worker status
sudo systemctl status laravel-worker
```

#### 3. Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 4. Database Connection Issues
```bash
# Test database connection
php artisan tinker
>>> \Illuminate\Support\Facades\DB::connection()->getPdo();
```

### Performance Debugging
```bash
# Enable query logging temporarily
# In AppServiceProvider::boot()
\Illuminate\Support\Facades\DB::listen(function ($query) {
    \Illuminate\Support\Facades\Log::info($query->sql);
});
```

## Development Commands

### Laravel Commands
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Create symbolic link for storage
php artisan storage:link

# Run queue worker
php artisan queue:work

# Start Laravel development server
php artisan serve
```

### Frontend Commands
```bash
# Install dependencies
npm install --legacy-peer-deps

# Development build with watching
npm run dev

# Production build
npm run build

# Run frontend tests
npm test

# Run Cypress E2E tests
npm run e2e

# Open Cypress test runner
npm run cypress:open
```

### Testing Commands
```bash
# Run all PHP tests
php artisan test

# Run specific test class
php artisan test --filter=GamificationServiceTest

# Run tests with coverage
php artisan test --coverage

# Run frontend tests
npm test

# Run E2E tests
npm run e2e
```

This comprehensive deployment guide provides everything needed to successfully deploy and maintain the Spanish English Learning App platform in production environments.