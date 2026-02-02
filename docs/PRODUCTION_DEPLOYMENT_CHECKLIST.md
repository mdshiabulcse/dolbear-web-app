# SSLCOMMERZ Production Deployment Checklist

## Overview
This checklist covers all files and configurations needed to deploy the SSLCOMMERZ payment system to production.

---

## 1. Database Changes (CRITICAL)

### Run Migrations
```bash
php artisan migrate
```

### Tables Created/Modified:
| Migration File | Description |
|---------------|-------------|
| `2026_02_02_000001_add_gateway_tran_id_to_orders_table.php` | Adds `gateway_tran_id` column to orders table |
| `2026_02_02_000002_create_payment_logs_table.php` | Creates payment_logs table for tracking all payment events |

### Manual SQL (Alternative)
If you can't run migrations, execute:
```bash
mysql -u root -p your_database_name < database/sql/create_payment_logs_table.sql
```

---

## 2. New Files to Deploy

### Core Files
| File Path | Purpose |
|-----------|---------|
| `app/Services/PaymentLogService.php` | Payment logging service |
| `app/Models/PaymentLog.php` | Payment log Eloquent model |

### Updated Files
| File Path | Changes |
|-----------|---------|
| `app/Http/Controllers/SslCommerzPaymentController.php` | Added PaymentLogService integration |
| `app/Http/Middleware/VerifySslcommerzSignature.php` | Signature verification middleware |
| `app/Http/Middleware/SslcommerzIpWhitelist.php` | IP whitelist middleware |

### Documentation
| File Path | Purpose |
|-----------|---------|
| `docs/PAYMENT_LOG_DATABASE_GUIDE.md` | SQL queries and usage guide |
| `database/sql/create_payment_logs_table.sql` | Raw SQL for manual table creation |

---

## 3. Environment Configuration (.env)

### Add to your `.env` file:

```bash
# ============================================
# SSLCOMMERZ PAYMENT GATEWAY CONFIG
# ============================================

# Store Credentials (get from SSLCOMMERZ dashboard)
SSLCZ_STORE_ID=your_live_store_id
SSLCZ_STORE_PASSWORD=your_live_store_password

# Environment Mode
# true = sandbox (testing)
# false = production (live)
SSLCZ_TESTMODE=false

# Localhost SSL Verification
# true = disable SSL verify on localhost
# false = enable SSL verify (recommended for production)
IS_LOCALHOST=false

# IP Whitelist Configuration
# true = enable IP whitelist checking (recommended for production)
# false = disable (only for testing)
SSLCOMMERZ_IP_WHITELIST_ENABLED=true

# Allow Local IP for Testing
# true = allow localhost/127.0.0.1 (testing only)
# false = block localhost (production)
SSLCOMMERZ_ALLOW_LOCAL_IP=false
```

---

## 4. Verify Middleware Registration

### Check: `app/Http/Kernel.php`

Ensure these lines exist in the `$routeMiddleware` array (lines 106-108):

```php
// === SSLCOMMERZ SECURITY MIDDLEWARE ===
'sslcommerz.ip' => \App\Http\Middleware\SslcommerzIpWhitelist::class,
'sslcommerz.signature' => \App\Http\Middleware\VerifySslcommerzSignature::class,
```

---

## 5. Verify CSRF Exemption

### Check: `app/Http/Middleware/VerifyCsrfToken.php`

Ensure SSLCOMMERZ routes are in the `$except` array (line 18):

```php
protected $except = [
    // ... other routes ...
    '/success', '/cancel', '/fail', '/ipn'
];
```

---

## 6. Verify Routes

### Check: `routes/web.php`

Ensure SSLCOMMERZ routes exist (around lines 259-270):

```php
// SSLCOMMERZ Start
Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout']);
Route::any('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::match(['get', 'post'], '/ipn', [SslCommerzPaymentController::class, 'ipn']);
Route::get('/ipn-test', [SslCommerzPaymentController::class, 'ipnTest']);
// SSLCOMMERZ END
```

---

## 7. SSLCOMMERZ Dashboard Configuration

### Log into SSLCOMMERZ Dashboard and Configure:

1. **IPN URL**: Set to `https://yourdomain.com/ipn`
2. **Success URL**: Set to `https://yourdomain.com/success`
3. **Fail URL**: Set to `https://yourdomain.com/fail`
4. **Cancel URL**: Set to `https://yourdomain.com/cancel`

### Important Notes:
- Use `https://` for production (not `http://`)
- Replace `yourdomain.com` with your actual domain

---

## 8. Database Settings Configuration

### Configure in Admin Panel or Database:

1. **SSLCOMMERZ Store ID**: Your live store ID from SSLCOMMERZ
2. **SSLCOMMERZ Store Password**: Your live store password from SSLCOMMERZ
3. **Sandbox Mode**: Set to `0` (disabled) for production

### Database Tables to Check:
- `settings` table (or wherever payment gateway settings are stored)
- Look for keys like: `sslcommerz_id`, `sslcommerz_password`, `is_sslcommerz_sandbox_mode_activated`

---

## 9. Production Server Requirements

### PHP Extensions:
- `php-curl` (for API calls)
- `php-json` (for JSON handling)
- `php-mbstring` (for string handling)

### Firewall/Server:
- Allow outbound HTTPS to:
  - `securepay.sslcommerz.com` (production)
  - Port: 443

### SSL Certificate:
- **REQUIRED** for production
- SSLCOMMERZ requires HTTPS for live transactions

---

## 10. Post-Deployment Testing

### Test Checklist:
- [ ] Payment initiation works
- [ ] Redirect to SSLCOMMERZ gateway successful
- [ ] Success callback processes correctly
- [ ] Fail callback handles errors properly
- [ ] Cancel callback works when user cancels
- [ ] IPN receives and processes payment
- [ ] Order status updates to "completed" after payment
- [ ] Payment logs are being created in `payment_logs` table
- [ ] Check `storage/logs/laravel-*.log` for any errors

---

## 11. Security Verification (IMPORTANT!)

### After Deployment, Verify:

1. **Test IP Whitelist**:
   ```bash
   # Should be blocked
   curl -X POST https://yourdomain.com/ipn

   # Check logs for blocked IP
   tail -f storage/logs/laravel-*.log | grep "IPN: Blocked"
   ```

2. **Test Signature Verification**:
   ```bash
   # Invalid signature should be rejected
   curl -X POST https://yourdomain.com/success \
     -d "tran_id=test&verify_sign=invalid"
   ```

3. **Verify HTTPS Only**:
   ```bash
   # HTTP should redirect to HTTPS
   curl -I http://yourdomain.com/pay
   ```

---

## 12. Monitor These Logs

### Laravel Logs:
```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Search for SSLCOMMERZ events:
```bash
grep "SSLCOMMERZ" storage/logs/laravel-*.log
```

### Key Log Messages to Watch:
- `SSLCOMMERZ Payment: Initiated`
- `SSLCOMMERZ Success: Transaction validated`
- `SSLCOMMERZ IPN: Order completed`
- `SSLCOMMERZ IPN: Blocked unauthorized IP` (security alert!)
- `SSLCOMMERZ Signature: Invalid signature` (security alert!)

---

## 13. Quick File Checklist

### Copy these files to production:

```
✓ app/Services/PaymentLogService.php
✓ app/Models/PaymentLog.php
✓ app/Http/Controllers/SslCommerzPaymentController.php
✓ app/Http/Middleware/VerifySslcommerzSignature.php
✓ app/Http/Middleware/SslcommerzIpWhitelist.php
✓ app/Http/Middleware/VerifyCsrfToken.php (verify $except array)
✓ app/Http/Kernel.php (verify middleware registration)
✓ routes/web.php (verify SSLCOMMERZ routes)
✓ database/migrations/2026_02_02_000001_*.php
✓ database/migrations/2026_02_02_000002_*.php
```

---

## 14. Rollback Plan (If Issues Occur)

### If payment fails after deployment:

1. **Check Laravel logs**:
   ```bash
   tail -100 storage/logs/laravel-*.log
   ```

2. **Check SSLCOMMERZ credentials**:
   - Verify store_id and store_password in `.env`
   - Verify database settings table

3. **Test IPN accessibility**:
   ```bash
   curl -X POST https://yourdomain.com/ipn
   ```

4. **Verify database migrations ran**:
   ```bash
   php artisan migrate:status
   ```

5. **Clear config cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

## 15. Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Configuration error" | Check SSLCOMMERZ credentials in `.env` and database |
| "Access denied" on IPN | Check IP whitelist middleware; ensure SSLCOMMERZ IPs are correct |
| "Invalid signature" error | Verify store password matches SSLCOMMERZ dashboard |
| Payment succeeds but order not completed | Check IPN is receiving callback; check Laravel logs |
| SSL certificate error | Ensure server has valid SSL certificate |
| Timeout during payment | Check firewall allows outbound HTTPS to SSLCOMMERZ |

---

## 16. SSLCOMMERZ IP Addresses (for Firewall)

### Add these to your whitelist if needed:

**Production IPs:**
- 103.163.226.128
- 103.163.226.129
- 103.163.226.130
- 103.163.226.131

**Sandbox IPs (testing only):**
- 103.163.226.132
- 103.163.226.133

---

## 17. Final Verification Command

Run this after deployment to verify everything:

```bash
# Run migrations
php artisan migrate --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Verify SSLCOMMERZ routes exist
php artisan route:list | grep -E "(pay|success|fail|cancel|ipn)"

# Check middleware is registered
grep -r "sslcommerz" app/Http/Kernel.php
```

---

## Summary

**Must Do Before Going Live:**
1. ✅ Run database migrations
2. ✅ Configure `.env` with production credentials
3. ✅ Set `SSLCOMMERZ_IP_WHITELIST_ENABLED=true`
4. ✅ Set `SSLCOMMERZ_ALLOW_LOCAL_IP=false`
5. ✅ Set `SSLCOMMERZ_TESTMODE=false`
6. ✅ Verify HTTPS is enabled on server
7. ✅ Test payment flow end-to-end
8. ✅ Monitor logs for errors

**After Going Live:**
- Monitor payment_logs table regularly
- Watch for "Blocked unauthorized IP" messages
- Watch for "Invalid signature" messages
- Check payment success rate

---

## Support

If issues occur:
1. Check `storage/logs/laravel-*.log`
2. Check `payment_logs` table
3. Verify SSLCOMMERZ dashboard for transaction status
4. Contact SSLCOMMERZ support if needed