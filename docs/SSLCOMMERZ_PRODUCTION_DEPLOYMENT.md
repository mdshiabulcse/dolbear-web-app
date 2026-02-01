# SSLCOMMERZ Production Deployment Guide

## Complete Production Readiness & Deployment Instructions

**Version**: 1.0
**Target**: Production Environment
**Estimated Time**: 2-3 hours
**Prerequisites**: SSLCOMMERZ production account, SSL certificate, PHP 8.2+

---

## Table of Contents
1. [Pre-Production Checklist](#pre-production-checklist)
2. [Security Implementation](#security-implementation)
3. [SSLCOMMERZ Production Setup](#sslcommerz-production-setup)
4. [Environment Configuration](#environment-configuration)
5. [Code Deployment](#code-deployment)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Monitoring & Alerting](#monitoring--alerting)
8. [Rollback Procedures](#rollback-procedures)
9. [Production Best Practices](#production-best-practices)

---

## Pre-Production Checklist

### Infrastructure Requirements

- [ ] **Server Environment**
  - [ ] PHP 8.2+ installed
  - [ ] MySQL 5.7+ or MariaDB 10.3+
  - [ ] SSL certificate installed and valid
  - [ ] Server accessible via HTTPS
  - [ ] Firewall allows outbound HTTPS connections

- [ ] **Database**
  - [ ] Database backup completed
  - [ ] BDT currency exists in currencies table
  - [ ] Settings table backed up
  - [ ] Orders table backed up

- [ ] **Application**
  - [ ] All code tested in sandbox
  - [ ] Security fixes implemented
  - [ ] Logs reviewed and cleaned
  - [ ] Debug mode disabled

### SSLCOMMERZ Production Account

- [ ] **SSLCOMMERZ Account**
  - [ ] Production account created
  - [ ] KYC (Know Your Customer) completed
  - [ ] Account approved by SSLCOMMERZ
  - [ ] Production Store ID obtained
  - [ ] Production Store Password obtained

- [ ] **API Credentials**
  - [ ] Store ID saved securely
  - [ ] Store Password saved securely
  - [ ] API endpoints documented
  - [ ] IP whitelist confirmed

---

## Security Implementation

### CRITICAL: All Security Fixes MUST Be Applied

Before deploying to production, ensure ALL security fixes from the security audit are implemented:

- [ ] **Authentication Fix**
  - [ ] `/pay` route requires authentication
  - [ ] Order ownership verification added
  - [ ] Tested unauthorized access attempt blocked

- [ ] **IP Whitelist**
  - [ ] SslcommerzIpWhitelist middleware created
  - [ ] Registered in Kernel.php
  - [ ] SSLCOMMERZ production IPs added
  - [ ] Local IPs disabled

- [ ] **Signature Verification**
  - [ ] VerifySslcommerzSignature middleware created
  - [ ] Registered in Kernel.php
  - [ ] Applied to `/ipn` route
  - [ ] Tested with valid signature

- [ ] **Rate Limiting**
  - [ ] `/pay`: 10 requests/minute
  - [ ] `/success`: 30 requests/minute
  - [ ] `/fail`: 30 requests/minute
  - [ ] `/cancel`: 30 requests/minute
  - [ ] `/ipn`: 60 requests/minute

- [ ] **Secure Logging**
  - [ ] SecurityLogger.php created
  - [ ] Sensitive data masked in logs
  - [ ] No card numbers in logs
  - [ ] No emails in logs

- [ ] **CSRF Protection**
  - [ ] `/success` CSRF protected
  - [ ] `/fail` CSRF protected
  - [ ] `/cancel` CSRF protected
  - [ ] `/ipn` only exempted (external server-to-server)

---

### Security Status Check

**Run this command to verify all security fixes**:

```bash
php artisan tinker << 'EOT'
// Check 1: Middleware registered
$middleware = app('router')->getMiddleware();
$checks = [];

$checks['sslcommerz.ip'] = isset($middleware['sslcommerz.ip']);
$checks['sslcommerz.signature'] = isset($middleware['sslcommerz.signature']);

echo "=== SECURITY STATUS CHECK ===\n";
foreach ($checks as $key => $exists) {
    echo $key . ': ' . ($exists ? '✓ INSTALLED' : '✗ MISSING') . "\n";
}

// Check 2: Routes protected
$routes = \Illuminate\Support\Facades\Route::getRoutes()->getRoutesByType();
$payRoute = collect($routes)->first(fn($r) => in_array('pay', $r->uri));
$ipnRoute = collect($routes)->first(fn($r) => in_array('ipn', $r->uri));

echo "\n=== ROUTE PROTECTION ===\n";
echo "Pay route auth: " . (collect($payRoute->middleware)->contains('auth') ? '✓ YES' : '✗ NO') . "\n";
echo "Pay route throttle: " . (collect($payRoute->middleware)->contains('throttle') ? '✓ YES' : '✗ NO') . "\n";
echo "IPN route IP whitelist: " . (collect($ipnRoute->middleware)->contains('sslcommerz.ip') ? '✓ YES' : '✗ NO') . "\n";
echo "IPN route signature: " . (collect($ipnRoute->middleware)->contains('sslcommerz.signature') ? '✓ YES' : '✗ NO') . "\n";

// Check 3: BDT currency
$bdt = \App\Models\Currency::where('code', 'BDT')->first();
echo "\n=== CONFIGURATION ===\n";
echo "BDT Currency: " . ($bdt ? '✓ EXISTS' : '✗ NOT FOUND') . "\n";

// Check 4: Environment
$isTest = env('SSLCZ_TESTMODE');
echo "Sandbox Mode: " . ($isTest ? '✅ ENABLED (CHANGE TO FALSE!)' : '✅ PRODUCTION MODE') . "\n";

$localIp = env('SSLCOMMERZ_ALLOW_LOCAL_IP');
echo "Local IP Allowed: " . ($localIp ? '⚠️ YES (should be FALSE in prod!)' : '✅ NO (correct)') . "\n";

exit;
EOT
```

---

## SSLCOMMERZ Production Setup

### Step 1: Create Production SSLCOMMERZ Account

1. **Visit SSLCOMMERZ**
   - URL: https://sslcommerz.com/
   - Click "Sign Up" or "Register"

2. **Choose Account Type**
   - **Individual**: For personal/business accounts
   - **Company**: For registered businesses (recommended)

3. **Complete KYC (Know Your Customer)**
   - Upload business documents:
     - Trade License
     - Business Registration Certificate
     - National ID of owner
     - TIN Certificate
     - Bank Statement
   - Upload personal documents:
     - National ID
     - Passport (if available)
   - Fill business information
   - Submit for review

4. **Wait for Approval**
   - Usually 1-3 business days
   - You'll receive email confirmation
   - Login to check status

5. **Get Production Credentials**
   - Go to: https://merchant.sslcommerz.com/
   - Navigate to: **Developer** > **Integration**
   - **Copy Store ID** (format: `live_xxx@sslcommerz`)
   - **Copy Store Password** (NOT your panel password)
   - **Note the API Domain**: `https://securepay.sslcommerz.com`

### Step 2: Configure Production Settings

**Method 1: Via Admin Panel (Recommended)**

1. Login to your application admin panel
2. Navigate to: **Settings** > **Payment Settings**
3. Find **SSLCOMMERZ** section
4. Update settings:
   - **Store ID**: Enter your production Store ID
   - **Store Password**: Enter your production Store Password
   - **Sandbox Mode**: **DISABLE/OFF** (turn off)
5. **Save Settings**

**Method 2: Via .env File**

**File**: `.env`

```env
# SSLCOMMERZ Production Configuration
SSLCZ_TESTMODE=false
SSLCZ_STORE_ID=live_yourstore123@sslcommerz
SSLCZ_STORE_PASSWORD=your_secure_password_here
IS_LOCALHOST=false

# Security Settings
SSLCOMMERZ_IP_WHITELIST_ENABLED=true
SSLCOMMERZ_ALLOW_LOCAL_IP=false
```

**CRITICAL SECURITY NOTES**:
- `SSLCZ_TESTMODE=false` - MUST be false for production
- `IS_LOCALHOST=false` - MUST be false for production
- Store Password is from Integration page, NOT your login password
- Never commit `.env` to version control

---

## Environment Configuration

### Production .env Template

**File**: `.env`

```env
APP_NAME="Your Application Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# SSLCOMMERZ Production Settings
SSLCZ_TESTMODE=false
SSLCZ_STORE_ID=live_yourstore@sslcommerz
SSLCZ_STORE_PASSWORD=your_production_password
IS_LOCALHOST=false

# Security Settings
SSLCOMMERZ_IP_WHITELIST_ENABLED=true
SSLCOMMERZ_ALLOW_LOCAL_IP=false

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Log Configuration
LOG_CHANNEL=daily
LOG_LEVEL=error
```

---

## Code Deployment

### Pre-Deployment Code Review

### 1. Verify Security Fixes

Run security check (see above) to verify:
- [ ] All middleware registered
- [ ] Routes properly protected
- [ ] BDT currency exists
- [ ] Sandbox mode disabled

### 2. Review and Update Configuration Files

**app/Http/Kernel.php**:
```php
// Verify these lines are present (around line 104):
'sslcommerz.ip' => \App\Http\Middleware\SslcommerzIpWhitelist::class,
'sslcommerz.signature' => \App\Http\Middleware\VerifySslcommerzSignature::class,
```

**routes/web.php** (Lines 182-191):
```php
// Verify SSLCOMMERZ routes have proper middleware
Route::any('/pay', [SslCommerzPaymentController::class, 'index'])
    ->middleware('auth')
    ->middleware('throttle:10,1);
```

**app/Http/Middleware/VerifyCsrfToken.php**:
```php
// Verify /success, /fail, /cancel are NOT in $except array
protected $except = [
    // ... other routes
    '/ipn'  // Only /ipn should be exempted
];
```

### 3. Deployment Methods

#### Method A: Git Deployment (Recommended)

```bash
# 1. Create production branch
git checkout -b production

# 2. Merge all changes
git checkout main
git pull origin main

# 3. Run deployment
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# 4. Set permissions
chmod -R 755 storage bootstrap/cache storage/logs
chmod -R 644 bootstrap/cache/*.php

# 5. Clear opcache if using PHP-FPM
sudo systemctl restart php-fpm  # CentOS/Ubuntu
```

#### Method B: FTP/SFTP Deployment

1. **Upload modified files**:
   ```
   app/Http/Kernel.php
   routes/web.php
   app/Http/Controllers/SslCommerzPaymentController.php
   app/Library/SslCommerz/SslCommerzNotification.php
   ```

2. **Upload new files**:
   ```
   app/Helpers/SecurityLogger.php
   app/Http/Middleware/SslcommerzIpWhitelist.php
   app/Http/Middleware/VerifySslcommerzSignature.php
   ```

3. **Upload .env** (with production values)

4. **Run commands on server**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   php artisan view:clear
   ```

---

## Post-Deployment Verification

### 1. Verify Application is Running

```bash
# Check if site is accessible
curl -I https://yourdomain.com
# Should return 200 OK

# Check if SSL is working
curl -I https://yourdomain.com/pay
# Should not have SSL errors
```

### 2. Verify SSLCOMMERZ Configuration

```bash
php artisan tinker
$storeId = env('SSLCZ_STORE_ID');
$isTest = env('SSLCZ_TESTMODE');
echo "Store ID: " . ($storeId ? '✓ SET' : '✗ NOT SET') . "\n";
echo "Sandbox: " . ($isTest ? '❌ ENABLED' : '✅ PRODUCTION') . "\n";
exit;
```

### 3. Verify Routes

```bash
php artisan route:list | grep -E "(pay|success|fail|cancel|ipn)"
```

### 4. Test Small Transaction

**CRITICAL**: Test with real money (small amount like 10-20 BDT)

---

## Monitoring & Alerting

### Set Up Log Monitoring

```bash
# Install log monitoring
sudo apt-get install fail2ban
```

### Create Monitoring Script

```bash
#!/bin/bash
LOG_FILE="/var/www/your-app/storage/logs/laravel-$(date +%Y-%m-%d).log"
ALERT_EMAIL="your-email@example.com"
ALERT_SUBJECT="SSLCOMMERZ Security Alert"

# Check for blocked IPs
BLOCKED_IPS=$(tail -1000 "$LOG_FILE" | grep "Blocked unauthorized IP")
if [ ! -z "$BLOCKED_IPS" ]; then
    echo "$BLOCKED_IPS" | mail -s "$ALERT_SUBJECT - Blocked IPs" "$ALERT_EMAIL"
fi

# Check for invalid signatures
INVALID_SIG=$(tail -1000 "$LOG_FILE" | grep "Invalid signature")
if [ ! -z "$INVALID_SIG" ]; then
    echo "$INVALID_SIG" | mail -s "$ALERT_SUBJECT - Invalid Signatures" "$ALERT_EMAIL"
fi
```

---

## Production Best Practices

### 1. SSL Certificate
- Valid SSL certificate required
- TLS 1.2+ recommended
- From trusted CA

### 2. Database Security
- Use read-only database user
- Separate user for migrations
- Rotate credentials regularly

### 3. Secrets Management
- Never commit `.env` to version control
- Use environment variables
- Rotate credentials monthly

### 4. Log Rotation
- Set up automatic log rotation
- Keep logs for 30 days
- Compress old logs

### 5. Backup Strategy
- Daily database backups
- Weekly code backups
- Off-site backup storage

---

## Rollback Procedures

### Immediate Rollback

```bash
# 1. Revert last commit
git revert HEAD~1
git push -f

# 2. Restore database
mysql -u root -pYourDatabaseName < backup/db_backup.sql

# 3. Clear caches
php artisan config:clear
php artisan route:clear

# 4. Restart services
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

---

**Production Deployment Guide** - Complete!