# SSLCOMMERZ Integration - Secure Routes Configuration

## Instructions

This document shows the EXACT routes configuration with all security fixes applied.

## Location: routes/web.php

### Current (INSECURE) Configuration (Lines 182-191)

```php
// SSLCOMMERZ Start - MUST be before catch-all routes
Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout']);
Route::any('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
// SSLCOMMERZ END
```

---

### SECURE Configuration (Replace Lines 182-191 with this)

```php
// SSLCOMMERZ Start - MUST be before catch-all routes
// Demo routes (can be removed in production)
Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout'])
    ->middleware('auth');
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout'])
    ->middleware('auth');

// Payment initiation - REQUIRES AUTHENTICATION
Route::any('/pay', [SslCommerzPaymentController::class, 'index'])
    ->middleware(['auth', 'customer', 'throttle:10,1']);

// Alternative payment method
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax'])
    ->middleware(['auth', 'customer', 'throttle:10,1']);

// Success callback - With rate limiting
Route::post('/success', [SslCommerzPaymentController::class, 'success'])
    ->middleware('throttle:30,1');

// Fail callback - With rate limiting
Route::post('/fail', [SslCommerzPaymentController::class, 'fail'])
    ->middleware('throttle:30,1');

// Cancel callback - With rate limiting
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel'])
    ->middleware('throttle:30,1');

// IPN endpoint - MAXIMUM SECURITY
// - IP whitelist (only SSLCOMMERZ servers)
// - Signature verification
// - Rate limiting
// - No CSRF (external server-to-server)
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn'])
    ->middleware([
        'sslcommerz.ip',
        'sslcommerz.signature',
        'throttle:60,1'
    ])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// SSLCOMMERZ END
```

---

## Step-by-Step Implementation

### Step 1: Register Middleware Aliases

**File**: `app/Http/Kernel.php`

Add to the `$middlewareAliases` array:

```php
protected $middlewareAliases = [
    // ... existing middleware aliases

    // SSLCOMMERZ Security Middleware
    'sslcommerz.ip' => \App\Http\Middleware\SslcommerzIpWhitelist::class,
    'sslcommerz.signature' => \App\Http\Middleware\VerifySslcommerzSignature::class,
];
```

### Step 2: Update CSRF Exceptions

**File**: `app/Http/Middleware/VerifyCsrfToken.php`

**BEFORE** (Line 16-19):
```php
protected $except = [
    'user/complete-order','user/complete-order/*','get/ssl-response','sslcommerz/ipn','invoice/*','/user/recharge-wallet','/user/recharge-wallet/*','paystack/initialize','paytm/success*','user/complete-recharge','user/complete-recharge*',
    'my-wallet','payment','seller/complete-purchase','user/payment/paytmRedirect','paytm/success','/pay-via-ajax', '/success','/cancel','/fail','/ipn'
];
```

**AFTER**:
```php
protected $except = [
    'user/complete-order','user/complete-order/*','get/ssl-response','sslcommerz/ipn','invoice/*','/user/recharge-wallet','/user/recharge-wallet/*','paystack/initialize','paytm/success*','user/complete-recharge','user/complete-recharge*',
    'my-wallet','payment','seller/complete-purchase','user/payment/paytmRedirect','paytm/success','/pay-via-ajax','/ipn'
];
```

**REMOVED**: `/success`,`/cancel`,`/fail` - These now require CSRF validation

### Step 3: Add Environment Variables

**File**: `.env`

```env
# SSLCOMMERZ Security Settings
SSLCOMMERZ_IP_WHITELIST_ENABLED=true
SSLCOMMERZ_ALLOW_LOCAL_IP=false  # Set to true ONLY for local testing
```

**For Production**:
```env
SSLCOMMERZ_IP_WHITELIST_ENABLED=true
SSLCOMMERZ_ALLOW_LOCAL_IP=false
```

**For Local Development**:
```env
SSLCOMMERZ_IP_WHITELIST_ENABLED=false  # Disable IP check locally
SSLCOMMERZ_ALLOW_LOCAL_IP=true  # Or keep enabled and allow local IPs
```

---

## Middleware Order

The middleware is applied in this order for the IPN endpoint:

1. `sslcommerz.ip` - **FIRST**: Verify source IP is from SSLCOMMERZ
2. `sslcommerz.signature` - **SECOND**: Verify request signature
3. `throttle:60,1` - **THIRD**: Apply rate limiting (60 requests per minute)
4. CSRF token check is **SKIPPED** (via `withoutMiddleware`)

---

## Route Security Levels

| Route | Auth Required | IP Whitelist | Signature Verify | Rate Limit | CSRF |
|-------|--------------|--------------|------------------|------------|------|
| `/pay` | ✅ Yes | ❌ No | ❌ No | 10/min | ✅ Yes |
| `/pay-via-ajax` | ✅ Yes | ❌ No | ❌ No | 10/min | ✅ Yes |
| `/success` | ❌ No* | ❌ No | ❌ No | 30/min | ✅ Yes |
| `/fail` | ❌ No | ❌ No | ❌ No | 30/min | ✅ Yes |
| `/cancel` | ❌ No | ❌ No | ❌ No | 30/min | ✅ Yes |
| `/ipn` | ❌ No† | ✅ Yes | ✅ Yes | 60/min | ❌ No |

*Callback routes don't require auth because user might not be logged in when returning from SSLCOMMERZ
†IPN is server-to-server, so no user session involved

---

## Testing Routes After Changes

### Test 1: Authentication on /pay

```bash
# Test without authentication (should fail)
curl -X GET "http://localhost:8000/pay?trx_id=TEST"
# Expected: 401 Unauthorized or redirect to login

# Test with authentication (should succeed if valid trx_id)
curl -X GET "http://localhost:8000/pay?trx_id=VALID_TRX" \
  -H "Cookie: laravel_session=YOUR_SESSION"
# Expected: 200 OK or redirect to payment gateway
```

### Test 2: IP Whitelist on /ipn

```bash
# Test from unauthorized IP (should fail)
curl -X POST http://localhost:8000/ipn \
  -d "tran_id=TEST&amount=100"
# Expected: 403 Forbidden

# Check logs for:
# "SSLCOMMERZ IPN: Blocked unauthorized IP"
```

### Test 3: Signature Verification

```bash
# Test with invalid signature (should fail)
curl -X POST http://localhost:8000/ipn \
  -d "tran_id=TEST&verify_sign=INVALID&verify_key=tran_id"
# Expected: 403 Forbidden

# Check logs for:
# "SSLCOMMERZ Signature: Invalid signature detected"
```

### Test 4: Rate Limiting

```bash
# Test rate limiting on /pay
for i in {1..15}; do
  curl -X GET "http://localhost:8000/pay?trx_id=TEST$i"
done
# Expected: 429 Too Many Requests after 10 requests
```

---

## Production Deployment Checklist

Before deploying to production:

- [ ] Registered middleware in `Kernel.php`
- [ ] Updated CSRF exceptions in `VerifyCsrfToken.php`
- [ ] Replaced routes in `web.php` with secure versions
- [ ] Set `SSLCOMMERZ_IP_WHITELIST_ENABLED=true` in `.env`
- [ ] Set `SSLCOMMERZ_ALLOW_LOCAL_IP=false` in `.env`
- [ ] Tested IP whitelist with real SSLCOMMERZ IP
- [ ] Tested signature verification
- [ ] Tested rate limiting
- [ ] Verified CSRF protection on success/fail/cancel
- [ ] Monitored logs for blocked requests
- [ ] Configured alerts for security events

---

## Troubleshooting

### Issue: "403 Forbidden" on IPN in Production

**Cause**: IP whitelist blocking legitimate SSLCOMMERZ requests

**Solution**:
1. Check logs for blocked IP: `tail -f storage/logs/laravel-*.log | grep "Blocked unauthorized IP"`
2. Verify SSLCOMMERZ's current IP range from: https://developer.sslcommerz.com/
3. Update `$allowedIps` in `SslcommerzIpWhitelist.php`

### Issue: "403 Forbidden" on /pay

**Cause**: User not authenticated

**Solution**:
1. User must be logged in before initiating payment
2. Frontend should check auth status before redirecting to `/pay`

### Issue: Rate Limit Blocking Legitimate Traffic

**Cause**: Too many requests from same user

**Solution**:
1. Adjust throttle values in routes (e.g., `throttle:20,1` for 20 per minute)
2. Or use `throttle:100,1` for higher limit

---

*Document Version: 1.0*
*Last Updated: February 1, 2026*