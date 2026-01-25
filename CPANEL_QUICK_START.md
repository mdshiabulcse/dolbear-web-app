# 🚀 Quick Start: cPanel Deployment Guide

## ⚡ Fast Track Deployment (30 Minutes)

### Step 1: Prepare Files Locally (5 min)

#### 1.1 Build Vue.js Frontend
```bash
# Open terminal/CMD in your project folder
cd C:\shiab\dolbear-web-app

# Install dependencies (first time only)
npm install

# Build for production
npm run production
```

**What this does:**
- Compiles Vue.js components
- Minifies CSS/JS files
- Creates optimized bundles in `public/frontend/` and `public/admin/`

#### 1.2 Create Production .env File
```bash
# Copy production template
cp .env.production .env
```

Edit `.env` and update these values:
```env
APP_URL=https://yourdomain.com
MIX_ASSET_URL=https://yourdomain.com
ASSET_URL=https://yourdomain.com

DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

APP_DEBUG=false
APP_INSTALLED=true
```

---

### Step 2: Upload to Server (5 min)

#### 2.1 Create Project ZIP
```bash
# On Windows: Right-click → Send to → Compressed folder
# Exclude these folders:
- node_modules/
- vendor/
- .git/
- storage/logs/*
```

#### 2.2 Upload via cPanel
1. **Login to cPanel** → File Manager
2. **Go to** `public_html`
3. **Upload** your ZIP file
4. **Right-click ZIP** → Extract
5. **Move contents** from extracted folder to `public_html`

---

### Step 3: Server Configuration (10 min)

#### 3.1 Install Composer Dependencies

**Via cPanel Terminal:**
```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

**If no terminal access:**
1. Use "Setup Node.js App" in cPanel (if available)
2. Or request host to run composer commands

#### 3.2 Set Permissions

**Via cPanel File Manager:**
1. Select `storage/` folder → Right-click → Change Permissions → **775**
2. Select `bootstrap/cache/` folder → Right-click → Change Permissions → **775**

**Via File Manager Code:**
- Select all files → Permissions: **644**
- Select all folders → Permissions: **755**
- `storage/` and `bootstrap/cache/` → **775**

#### 3.3 Create Database

1. **cPanel** → MySQL Databases
2. **Create Database**: `yourname_store`
3. **Create User**: `yourname_user`
4. **Generate Password** (save it!)
5. **Add User to Database** → Check "ALL PRIVILEGES"

#### 3.4 Import Database

**Via cPanel → phpMyAdmin:**
1. Click database name
2. Import tab → Choose your SQL file
3. Click "Go"

**If no SQL backup:**
```bash
# Via Terminal
cd ~/public_html
php artisan migrate --force
php artisan db:seed --force
```

#### 3.5 Generate Application Key

```bash
# Via Terminal
cd ~/public_html
php artisan key:generate
```

---

### Step 4: Finalize Deployment (10 min)

#### 4.1 Create Storage Link
```bash
php artisan storage:link
```

#### 4.2 Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

#### 4.3 Cache for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 4.4 Set Up Cron Job

1. **cPanel** → Cron Jobs
2. **Add New Cron Job** (Every minute)
3. **Command**:
```bash
/usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

*Note: Replace `username` with your cPanel username*

---

### Step 5: Verify & Test (5 min)

#### 5.1 Test Frontend
```
https://yourdomain.com
```
✅ Should load homepage
✅ No 500 errors
✅ CSS/JS loads correctly

#### 5.2 Test Admin
```
https://yourdomain.com/admin/login
```
✅ Login page loads
✅ Default credentials work (if seeded)

#### 5.3 Check Error Log
If issues occur:
```
cPanel → Errors → Select domain
OR
File Manager → storage/logs/laravel.log
```

---

## 🔧 Troubleshooting Quick Fixes

### Problem: 500 Internal Server Error

**Solution 1: Check .env file**
```bash
# .env must exist in project root
ls -la .env

# If missing, create it
cp .env.production .env
```

**Solution 2: Fix Permissions**
```bash
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

**Solution 3: Clear Cache**
```bash
php artisan cache:clear
php artisan config:clear
```

### Problem: Vue.js Not Working

**Solution 1: Verify Build**
```bash
# Check these files exist:
public/frontend/js/app.js
public/frontend/css/app.css
mix-manifest.json
```

**Solution 2: Rebuild Locally**
```bash
npm run production
# Upload public/frontend/ folder again
```

**Solution 3: Check Asset URLs**
```env
# In .env file:
MIX_ASSET_URL=https://yourdomain.com
ASSET_URL=https://yourdomain.com
```

### Problem: Database Connection Failed

**Solution 1: Verify Credentials**
```bash
# Check .env
cat .env | grep DB_
```

**Solution 2: Test Connection**
```bash
php artisan tinker
DB::connection()->getPdo();
```

**Solution 3: Check Database Exists**
```
cPanel → MySQL Databases → Verify database listed
```

### Problem: Images/Media Not Loading

**Solution:**
```bash
# Create storage link
php artisan storage:link

# Verify link exists
ls -la public/storage
```

---

## 📋 Pre-Deployment Checklist

### Local Preparation
- [ ] Run `npm run production` locally
- [ ] Test site with `APP_DEBUG=false`
- [ ] Backup database
- [ ] Update .env for production
- [ ] Create project ZIP (exclude node_modules, vendor)

### Server Upload
- [ ] Upload files to `public_html`
- [ ] Extract and organize files
- [ ] Create MySQL database
- [ ] Create database user
- [ ] Import database or run migrations

### Configuration
- [ ] Install composer dependencies
- [ ] Set file permissions (775 for storage)
- [ ] Create/edit .env file
- [ ] Generate application key
- [ ] Create storage link
- [ ] Clear all caches
- [ ] Set up cron job
- [ ] Configure SSL certificate

### Testing
- [ ] Homepage loads
- [ ] Admin panel loads
- [ ] Can login to admin
- [ ] Images load correctly
- [ ] Vue.js components work
- [ ] No console errors
- [ ] Check error logs

---

## 🎯 Common cPanel Paths

```bash
# Project root
/home/username/public_html

# PHP binary
/usr/local/bin/php
# OR
/opt/cpanel/ea-php82/bin/php

# Composer
/usr/local/bin/composer

# Error logs
/home/username/public_html/storage/logs/laravel.log

# Access logs
/home/username/logs/access_log
```

---

## 📞 Quick Commands Reference

```bash
# Navigate to project
cd ~/public_html

# Install composer
composer install --no-dev

# Clear cache
php artisan cache:clear

# Storage link
php artisan storage:link

# Optimize
php artisan optimize

# Migrate database
php artisan migrate --force

# Check version
php -v
php artisan --version
```

---

## 🚨 Emergency Rollback

If deployment fails:

```bash
# 1. Restore .env file
cp .env.backup .env

# 2. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 3. Restore database (via phpMyAdmin)

# 4. Check logs
tail -f storage/logs/laravel.log
```

---

## ✅ Success Indicators

You'll know deployment succeeded when:

1. ✅ Homepage loads without errors
2. ✅ Admin panel accessible at `/admin/login`
3. ✅ No "Installation" page appears
4. ✅ Images and media display correctly
5. ✅ Vue.js components render properly
6. ✅ No console errors (F12)
7. ✅ Storage link works
8. ✅ Database queries execute
9. ✅ Cron jobs scheduled
10. ✅ SSL certificate active

---

## 📱 Mobile Testing

After deployment, test on:
- [ ] Chrome (Android)
- [ ] Safari (iOS)
- [ ] Different screen sizes
- [ ] PWA functionality (if applicable)

---

## 🔐 Security Checklist

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false`
- [ ] Enable HTTPS/SSL
- [ ] Protect .env file (644 permissions)
- [ ] Set up regular backups
- [ ] Monitor error logs
- [ ] Keep PHP version updated
- [ ] Use strong database password

---

## 📞 Need Help?

### Check These First:
1. `storage/logs/laravel.log`
2. cPanel Error logs
3. Browser console (F12)
4. Network tab in DevTools

### Useful cPanel Features:
- **phpMyAdmin**: Database management
- **File Manager**: File operations
- **Terminal**: Command line access
- **Cron Jobs**: Scheduled tasks
- **SSL/TLS**: Security certificates
- **Errors**: View error logs

---

## ⏱️ Time Estimates

| Task | Time |
|------|------|
| Local build (npm) | 5 min |
| Upload files | 5 min |
| Database setup | 3 min |
| Composer install | 5 min |
| Configuration | 10 min |
| Testing | 5 min |
| **Total** | **~30 min** |

---

**Tip**: Save this file for future reference!
