# SSLCOMMERZ Production Deployment - File by File Guide

## Server Path Reference

```
your-server/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Kernel.php
│   ├── Models/
│   ├── Services/
│   └── Repositories/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── sql/
├── docs/
├── public/
├── resources/
├── routes/
├── storage/
└── .env
```

---

## 1. NEW FILES - Upload to Server

### File 1: PaymentLogService.php
```
LOCAL LOCATION:
app/Services/PaymentLogService.php

↓ UPLOAD TO ↓

SERVER LOCATION:
your-server/app/Services/PaymentLogService.php
```

---

### File 2: PaymentLog.php
```
LOCAL LOCATION:
app/Models/PaymentLog.php

↓ UPLOAD TO ↓

SERVER LOCATION:
your-server/app/Models/PaymentLog.php
```

---

### File 3: Payment Logs Migration
```
LOCAL LOCATION:
database/migrations/2026_02_02_000002_create_payment_logs_table.php

↓ UPLOAD TO ↓

SERVER LOCATION:
your-server/database/migrations/2026_02_02_000002_create_payment_logs_table.php
```

---

### File 4: SQL File (Alternative to Migration)
```
LOCAL LOCATION:
database/sql/create_payment_logs_table.sql

↓ UPLOAD TO ↓

SERVER LOCATION:
your-server/database/sql/create_payment_logs_table.sql
```

---

### File 5: Payment Log Database Guide
```
LOCAL LOCATION:
docs/PAYMENT_LOG_DATABASE_GUIDE.md

↓ UPLOAD TO ↓

SERVER LOCATION:
your-server/docs/PAYMENT_LOG_DATABASE_GUIDE.md
```

---

### File 6: Production Deployment Checklist
```
LOCAL LOCATION:
docs/PRODUCTION_DEPLOYMENT_CHECKLIST.md

↓ UPLOAD TO ↓

SERVER LOCATION:
your-server/docs/PRODUCTION_DEPLOYMENT_CHECKLIST.md
```

---

## 2. EXISTING FILES - Verify or Update

### File 7: SslCommerzPaymentController.php
```
LOCAL LOCATION:
app/Http/Controllers/SslCommerzPaymentController.php

↓ CHECK/UPDATE ON ↓

SERVER LOCATION:
your-server/app/Http/Controllers/SslCommerzPaymentController.php

STATUS: ⚠️ VERIFY THIS FILE HAS PAYMENT LOGGING
         Check line 11: use App\Services\PaymentLogService;
         Check line 16: protected $paymentLog;
         Check line 18: PaymentLogService $paymentLog
```

---

### File 8: VerifySslcommerzSignature.php (MIDDLEWARE)
```
LOCAL LOCATION:
app/Http/Middleware/VerifySslcommerzSignature.php

↓ UPLOAD IF MISSING ↓

SERVER LOCATION:
your-server/app/Http/Middleware/VerifySslcommerzSignature.php

STATUS: ⚠️ ENSURE THIS FILE EXISTS
```

---

### File 9: SslcommerzIpWhitelist.php (MIDDLEWARE)
```
LOCAL LOCATION:
app/Http/Middleware/SslcommerzIpWhitelist.php

↓ UPLOAD IF MISSING ↓

SERVER LOCATION:
your-server/app/Http/Middleware/SslcommerzIpWhitelist.php

STATUS: ⚠️ ENSURE THIS FILE EXISTS
```

---

### File 10: Kernel.php (VERIFY MIDDLEWARE IS REGISTERED)
```
LOCAL LOCATION:
app/Http/Kernel.php

↓ CHECK LINES 106-108 ↓

SERVER LOCATION:
your-server/app/Http/Kernel.php

STATUS: ⚠️ VERIFY THESE LINES EXIST:

// === SSLCOMMERZ SECURITY MIDDLEWARE ===
'sslcommerz.ip' => \App\Http\Middleware\SslcommerzIpWhitelist::class,
'sslcommerz.signature' => \App\Http\Middleware\VerifySslcommerzSignature::class,
```

---

### File 11: VerifyCsrfToken.php (VERIFY ROUTES EXEMPTED)
```
LOCAL LOCATION:
app/Http/Middleware/VerifyCsrfToken.php

↓ CHECK LINE 18 ↓

SERVER LOCATION:
your-server/app/Http/Middleware/VerifyCsrfToken.php

STATUS: ⚠️ VERIFY $except ARRAY INCLUDES:

protected $except = [
    // ... other routes ...
    '/success', '/cancel', '/fail', '/ipn'
];
```

---

### File 12: web.php (VERIFY ROUTES)
```
LOCAL LOCATION:
routes/web.php

↓ CHECK LINES 259-270 ↓

SERVER LOCATION:
your-server/routes/web.php

STATUS: ⚠️ VERIFY THESE ROUTES EXIST:

Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout']);
Route::any('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::match(['get', 'post'], '/ipn', [SslCommerzPaymentController::class, 'ipn']);
Route::get('/ipn-test', [SslCommerzPaymentController::class, 'ipnTest']);
```

---

### File 13: sslcommerz.php (VERIFY CONFIG)
```
LOCAL LOCATION:
config/sslcommerz.php

↓ NO CHANGES NEEDED ↓

SERVER LOCATION:
your-server/config/sslcommerz.php

STATUS: ✓ VERIFY FILE EXISTS - No changes needed
```

---

### File 14: .env (CRITICAL CONFIGURATION)
```
LOCAL LOCATION:
.env

↓ ADD THESE SETTINGS ↓

SERVER LOCATION:
your-server/.env

STATUS: ⚠️ ADD/UPDATE THESE SETTINGS:

# ============================================
# SSLCOMMERZ PAYMENT GATEWAY CONFIG
# ============================================
SSLCZ_STORE_ID=your_live_store_id_here
SSLCZ_STORE_PASSWORD=your_live_password_here
SSLCZ_TESTMODE=false
IS_LOCALHOST=false
SSLCOMMERZ_IP_WHITELIST_ENABLED=true
SSLCOMMERZ_ALLOW_LOCAL_IP=false
```

---

## 3. DATABASE CHANGES

### Option A: Run Migration (Recommended)
```
SSH into server and run:
cd /path/to/your-server
php artisan migrate --force
```

### Option B: Manual SQL
```
Upload: database/sql/create_payment_logs_table.sql
To:     your-server/database/sql/create_payment_logs_table.sql

Then run via phpMyAdmin or SSH:
mysql -u username -p database_name < database/sql/create_payment_logs_table.sql
```

---

## 4. DEPLOYMENT CHECKLIST (Copy & Track)

```
DEPLOYMENT STATUS:

NEW FILES:
[  ] app/Services/PaymentLogService.php
[  ] app/Models/PaymentLog.php
[  ] database/migrations/2026_02_02_000002_create_payment_logs_table.php
[  ] database/sql/create_payment_logs_table.sql (if using manual SQL)
[  ] docs/PAYMENT_LOG_DATABASE_GUIDE.md
[  ] docs/PRODUCTION_DEPLOYMENT_CHECKLIST.md

VERIFY FILES:
[  ] app/Http/Controllers/SslCommerzPaymentController.php
[  ] app/Http/Middleware/VerifySslcommerzSignature.php
[  ] app/Http/Middleware/SslcommerzIpWhitelist.php
[  ] app/Http/Kernel.php (check lines 106-108)
[  ] app/Http/Middleware/VerifyCsrfToken.php (check line 18)
[  ] routes/web.php (check lines 259-270)
[  ] config/sslcommerz.php (verify exists)
[  ] .env (add SSLCOMMERZ settings)

CONFIGURATION:
[  ] Run migrations: php artisan migrate --force
[  ] Clear cache: php artisan config:clear
[  ] Clear cache: php artisan cache:clear
[  ] Set SSLCZ_TESTMODE=false in .env
[  ] Set production store_id in .env
[  ] Set production store_password in .env
[  ] Set SSLCOMMERZ_IP_WHITELIST_ENABLED=true
[  ] Set SSLCOMMERZ_ALLOW_LOCAL_IP=false

TESTING:
[  ] Test payment initiation
[  ] Test success callback
[  ] Test fail callback
[  ] Test cancel callback
[  ] Test IPN endpoint
[  ] Check payment_logs table for entries
[  ] Check Laravel logs for errors
```

---

## 5. SSH COMMANDS FOR DEPLOYMENT

```bash
# Connect to your server
ssh user@your-server.com

# Navigate to your Laravel application
cd /var/www/html/your-laravel-app

# Upload files (from your local machine)
# Using SCP:
scp app/Services/PaymentLogService.php user@server:/var/www/html/your-laravel-app/app/Services/
scp app/Models/PaymentLog.php user@server:/var/www/html/your-laravel-app/app/Models/
scp database/migrations/2026_02_02_000002_create_payment_logs_table.php user@server:/var/www/html/your-laravel-app/database/migrations/

# Or using FTP/SFTP client, upload to same paths

# On server - Run migrations
php artisan migrate --force

# On server - Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# On server - Verify routes
php artisan route:list | grep -E "(pay|success|fail|cancel|ipn)"

# On server - Check permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## 6. QUICK REFERENCE - ALL FILES

| # | File Path | Action |
|---|-----------|--------|
| 1 | `app/Services/PaymentLogService.php` | UPLOAD NEW |
| 2 | `app/Models/PaymentLog.php` | UPLOAD NEW |
| 3 | `database/migrations/2026_02_02_000002_create_payment_logs_table.php` | UPLOAD NEW |
| 4 | `database/sql/create_payment_logs_table.sql` | UPLOAD NEW |
| 5 | `docs/PAYMENT_LOG_DATABASE_GUIDE.md` | UPLOAD NEW |
| 6 | `docs/PRODUCTION_DEPLOYMENT_CHECKLIST.md` | UPLOAD NEW |
| 7 | `app/Http/Controllers/SslCommerzPaymentController.php` | VERIFY/UPDATE |
| 8 | `app/Http/Middleware/VerifySslcommerzSignature.php` | VERIFY EXISTS |
| 9 | `app/Http/Middleware/SslcommerzIpWhitelist.php` | VERIFY EXISTS |
| 10 | `app/Http/Kernel.php` | VERIFY LINES 106-108 |
| 11 | `app/Http/Middleware/VerifyCsrfToken.php` | VERIFY LINE 18 |
| 12 | `routes/web.php` | VERIFY LINES 259-270 |
| 13 | `.env` | ADD SSLCOMMERZ SETTINGS |

---

## 7. POST-DEPLOYMENT VERIFICATION

```bash
# SSH into server and run these commands:

# 1. Check migration status
php artisan migrate:status | grep payment_logs

# 2. Verify SSLCOMMERZ routes exist
php artisan route:list | grep -E "(pay|success|fail|cancel|ipn)"

# Expected output:
# | GET|HEAD| /example1 | .....
# | GET|HEAD| /example2 | .....
# | ANY      | /pay      | .....
# | POST     | /pay-via-ajax | .....
# | POST     | /success  | .....
# | POST     | /fail     | .....
# | POST     | /cancel   | .....
# | GET|POST | /ipn      | .....
# | GET|HEAD | /ipn-test | .....

# 3. Check payment_logs table exists
mysql -u root -p -e "DESCRIBE payment_logs" your_database_name

# 4. Verify .env settings
grep SSLCZ .env
grep SSLCOMMERZ .env

# 5. Check Laravel logs (last 50 lines)
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## 8. TROUBLESHOOTING

| Problem | Command to Run |
|---------|----------------|
| Migration not running | `php artisan migrate:status` |
| Routes not working | `php artisan route:clear` |
| Config not updating | `php artisan config:clear` |
| Permission errors | `chmod -R 775 storage bootstrap/cache` |
| Check for errors | `tail -100 storage/logs/laravel-*.log` |

---

## 9. PRODUCTION SETTINGS SUMMARY

```bash
# These MUST be set correctly in .env on production server:
SSLCZ_STORE_ID=LIVE_STORE_ID_FROM_SSLCOMMERZ
SSLCZ_STORE_PASSWORD=LIVE_PASSWORD_FROM_SSLCOMMERZ
SSLCZ_TESTMODE=false
IS_LOCALHOST=false
SSLCOMMERZ_IP_WHITELIST_ENABLED=true
SSLCOMMERZ_ALLOW_LOCAL_IP=false

# SSLCOMMERZ Dashboard URLs to configure:
Success URL:  https://yourdomain.com/success
Fail URL:     https://yourdomain.com/fail
Cancel URL:   https://yourdomain.com/cancel
IPN URL:      https://yourdomain.com/ipn
```

---

## 10. SUPPORT CONTACTS

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel-*.log`
2. Check payment_logs table in database
3. Verify SSLCOMMERZ dashboard settings
4. SSLCOMMERZ Support: https://developer.sslcommerz.com/

---

## QUICK COPY-PASTE DEPLOYMENT SCRIPT

```bash
#!/bin/bash
# Save as deploy_sslcommerz.sh and run on server

echo "=== SSLCOMMERZ DEPLOYMENT STARTED ==="

# Navigate to project
cd /var/www/html/your-laravel-app

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear cache
echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Set permissions
echo "Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Verify routes
echo "Verifying SSLCOMMERZ routes..."
php artisan route:list | grep -E "(pay|success|fail|cancel|ipn)"

echo "=== DEPLOYMENT COMPLETE ==="
echo "Please verify .env has correct SSLCOMMERZ settings"
```

---

**DEPLOYMENT COMPLETE! 🚀**

Next steps:
1. Upload all [NEW FILES]
2. Verify all [VERIFY FILES]
3. Update .env with production credentials
4. Run migration commands
5. Test payment flow
6. Monitor logs