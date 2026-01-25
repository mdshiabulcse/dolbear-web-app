# 🚀 Dolbear eCommerce - Production Deployment Guide

## 📋 Table of Contents
1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [cPanel Deployment Steps](#cpanel-deployment-steps)
4. [Vue.js Frontend Build](#vuejs-frontend-build)
5. [Database Setup](#database-setup)
6. [Post-Deployment Configuration](#post-deployment-configuration)
7. [Common Issues & Solutions](#common-issues--solutions)

---

## 🔧 Server Requirements

### Minimum Requirements
- **PHP Version**: 8.2 or higher ⚠️ **CRITICAL**
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Node.js**: 16.x or 18.x
- **NPM**: 7.x or higher
- **Composer**: 2.x

### PHP Extensions Required
```ini
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML
- Zip
- GD Library
- Imagick (optional)
```

### PHP Configuration (.htaccess or php.ini)
```ini
memory_limit = 256M
post_max_size = 100M
upload_max_filesize = 100M
max_execution_time = 300
max_input_time = 300
```

---

## ✅ Pre-Deployment Checklist

### 1. Local Development Setup
- [ ] Remove installation system (already done ✅)
- [ ] Test all features locally
- [ ] Set `APP_DEBUG=false` in production environment
- [ ] Clear all caches
- [ ] Update .env file for production

### 2. File Preparation
```bash
# Create a clean copy excluding development files
compress project excluding:
- node_modules/
- vendor/
- .git/
- storage/logs/*
- storage/framework/cache/*
- storage/framework/sessions/*
- storage/framework/views/*
```

---

## 🖥️ cPanel Deployment Steps

### Step 1: Upload Files to Server

#### Option A: Using cPanel File Manager
1. **Login to cPanel** → File Manager
2. **Navigate to public_html** (or your subdirectory)
3. **Upload your project** as a ZIP file
4. **Extract the ZIP** file
5. **Move all files** from the extracted folder to `public_html`

#### Option B: Using Git (Recommended)
```bash
# SSH into your server
ssh username@yourdomain.com

# Navigate to your directory
cd public_html

# Clone repository
git clone https://your-repo-url.git .

# Or pull if already cloned
git pull origin main
```

### Step 2: Set Directory Permissions

Via cPanel File Manager or SSH:
```bash
# Navigate to project root
cd public_html

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage and cache directories need write permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Some hosts require 777 (use only if necessary)
# chmod -R 777 storage bootstrap/cache
```

### Step 3: Install Composer Dependencies

#### Via SSH (Recommended)
```bash
cd public_html
composer install --optimize-autoloader --no-dev
```

#### Via cPanel Terminal
1. Open cPanel → Terminal
2. Run the above commands

#### If Composer Not Available
1. Download `composer.phar` from https://getcomposer.org/download/
2. Upload to server
3. Run: `php composer.phar install --optimize-autoloader --no-dev`

### Step 4: Create Environment File

```bash
# Copy production environment file
cp .env.production .env

# Or create from scratch
touch .env
```

Edit `.env` file with your production settings:

```env
APP_NAME="Your Store Name"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_INSTALLED=true

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Asset Configuration
MIX_ASSET_URL=https://yourdomain.com
ASSET_URL=https://yourdomain.com

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=465
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security (Generate new keys)
JWT_SECRET=generate-new-jwt-secret-here

# Payment Gateways (Configure as needed)
PAYSTACK_PUBLIC=your-paystack-key
PAYSTACK_TEST=your-paystack-test-key

# Social Login (Optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_CALLBACK_URL=https://yourdomain.com/callback/google

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_CALLBACK_URL=https://yourdomain.com/callback/facebook
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

---

## 🎨 Vue.js Frontend Build

### Step 1: Install Node.js Dependencies

#### Via SSH
```bash
cd public_html
npm install
```

#### If Node.js Not Available on Server
**Build Locally and Upload:**

1. **On your local machine:**
```bash
# Navigate to your project
cd C:\shiab\dolbear-web-app

# Install dependencies
npm install

# Build for production
npm run production
```

2. **Upload compiled files:**
Upload these folders to your server:
- `public/frontend/` (contains compiled CSS/JS)
- `public/admin/` (contains admin compiled JS)
- `mix-manifest.json` (asset mapping file)

### Step 2: Verify Build Configuration

Your `webpack.mix.js` is configured for:
```javascript
// Frontend build
mix.js('resources/js/app.js', 'public/frontend/js')
    .vue()
    .combine([...], 'public/frontend/css/app.css')

// Admin build
mix.js('resources/js/admin.js', 'public/admin/js/app.js')
    .vue()
```

### Step 3: Build Commands

```bash
# Development build (not for production)
npm run dev

# Watch mode (for development)
npm run watch

# Production build (minified & optimized)
npm run production

# Production with polling (if watch doesn't work)
npm run watch-poll
```

### Step 4: Verify Compiled Assets

After build, ensure these files exist:
```
public/
├── frontend/
│   ├── js/
│   │   ├── app.js
│   │   ├── chunks-180/ (code-split chunks)
│   │   └── plugin.js
│   └── css/
│       └── app.css
├── admin/
│   └── js/
│       └── app.js
└── mix-manifest.json
```

---

## 🗄️ Database Setup

### Step 1: Create Database via cPanel

1. **Login to cPanel** → MySQL Databases
2. **Create New Database**: `your_database_name`
3. **Create New User**: `your_database_user`
4. **Set Password**: Use strong password generator
5. **Add User to Database**: Check "ALL PRIVILEGES"
6. **Submit**

### Step 2: Import Database

#### Option A: Via cPanel phpMyAdmin
1. Open phpMyAdmin
2. Select your database
3. Click "Import" tab
4. Choose your SQL backup file
5. Click "Go"

#### Option B: Via SSH
```bash
# Import database
mysql -u username -p database_name < backup.sql

# Or with gunzip for compressed files
gunzip < backup.sql.gz | mysql -u username -p database_name
```

#### Option C: If No Backup Exists
```bash
# Run migrations to create tables
php artisan migrate --force

# Seed database if available
php artisan db:seed --force
```

### Step 3: Verify Database Connection

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
=> Should return PDO instance info
>>> exit
```

---

## 🔒 Post-Deployment Configuration

### Step 1: Clear and Cache Config

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 2: Set Correct Permissions

```bash
# Storage link
php artisan storage:link

# Optimize for production
php artisan optimize

# Clear compiled files
php artisan clear-compiled
```

### Step 3: Verify Installation

Open your browser:
```
https://yourdomain.com
```

You should see your homepage, NOT installation page.

### Step 4: Admin Access

```
https://yourdomain.com/admin/login
```

Default credentials (if seeded):
- Email: admin@example.com
- Password: password (CHANGE IMMEDIATELY!)

### Step 5: Configure Cron Jobs

Via cPanel → Cron Jobs:

```bash
# Run Laravel scheduler every minute
* * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1

# Example:
* * * * * /usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

### Step 6: Queue Worker (Optional)

If using queues:
```bash
# Via Supervisor (recommended)
# Create: /etc/supervisor/conf.d/laravel-worker.conf

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/public_html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=username
numprocs=2
redirect_stderr=true
stdout_logfile=/home/username/public_html/worker.log
```

---

## 🛠️ Common Issues & Solutions

### Issue 1: 500 Internal Server Error

**Causes & Solutions:**

```bash
# Check .env file exists
ls -la .env

# Check permissions
chmod -R 755 .
chmod -R 775 storage bootstrap/cache

# Check PHP version
php -v  # Should be 8.2+

# Check error log
tail -f storage/logs/laravel.log
```

### Issue 2: Vue Components Not Loading

**Solutions:**

```bash
# Rebuild frontend
npm run production

# Clear mix cache
rm -rf public/frontend/js/chunks-180
npm run production

# Check mix-manifest.json
cat mix-manifest.json

# Verify ASSET_URL in .env
ASSET_URL=https://yourdomain.com
MIX_ASSET_URL=https://yourdomain.com
```

### Issue 3: Database Connection Failed

```bash
# Check database credentials
cat .env | grep DB_

# Test connection manually
mysql -h localhost -u user -p database

# Check MySQL is running
systemctl status mysql
```

### Issue 4: Permission Denied

```bash
# Fix ownership
chown -R user:group /path/to/project

# Fix permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

### Issue 5: Mix Assets Not Found

```bash
# Ensure build was run
ls -la public/frontend/js/

# Check mix-manifest.json
cat mix-manifest.json

# Rebuild
npm run production

# Clear browser cache
# Ctrl+F5 or Cmd+Shift+R
```

### Issue 6: Routes Not Working

```bash
# Check .htaccess file exists
ls -la public/.htaccess

# Verify mod_rewrite is enabled
# In cPanel: Software → Select PHP Version → Extensions

# Clear route cache
php artisan route:clear
php artisan route:cache
```

### Issue 7: Storage Link Not Working

```bash
# Remove existing link
rm -rf public/storage

# Create new link
php artisan storage:link

# Verify link exists
ls -la public/storage
```

---

## 📊 Performance Optimization

### Enable OPcache
Add to `php.ini` or via cPanel:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### Enable Gzip Compression
Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>
```

### Enable Browser Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## 🔐 Security Checklist

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong database password
- [ ] Enable SSL/HTTPS
- [ ] Set up firewall rules
- [ ] Disable directory browsing
- [ ] Protect sensitive files (.env, .git)
- [ ] Regular backups
- [ ] Monitor logs
- [ ] Keep dependencies updated

---

## 📝 Quick Reference Commands

```bash
# Cache operations
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache

# Database operations
php artisan migrate:fresh --seed
php artisan db:seed

# Storage
php artisan storage:link

# Queue
php artisan queue:work
php artisan queue:retry all

# Optimization
php artisan optimize
composer dump-autoload --optimize

# Build
npm run production
npm run dev
npm run watch
```

---

## 📞 Support & Resources

### Useful Links
- Laravel Documentation: https://laravel.com/docs
- Vue.js Documentation: https://vuejs.org/guide/
- cPanel Documentation: https://docs.cpanel.net/

### Log Files Location
```
storage/logs/laravel.log
storage/logs/laravel-YYYY-MM-DD.log
```

### Error Reporting
Check these files when issues occur:
1. `storage/logs/laravel.log`
2. `storage/logs/laravel-[date].log`
3. Server error log (via cPanel → Errors)

---

## ✨ Deployment Checklist Summary

### Before Upload
- [ ] Test locally with `APP_ENV=production`
- [ ] Run `npm run production`
- [ ] Optimize composer dependencies
- [ ] Backup database
- [ ] Backup .env file

### After Upload
- [ ] Upload files to server
- [ ] Set correct permissions
- [ ] Create and configure .env
- [ ] Install composer dependencies
- [ ] Generate app key
- [ ] Create/import database
- [ ] Run migrations
- [ ] Create storage link
- [ ] Clear all caches
- [ ] Cache config/routes/views
- [ ] Set up cron jobs
- [ ] Configure SSL
- [ ] Test all functionality
- [ ] Monitor error logs

---

**Last Updated**: 2026-01-14
**Project**: Dolbear eCommerce
**Laravel Version**: 8.x
**Vue.js Version**: 2.6.14
