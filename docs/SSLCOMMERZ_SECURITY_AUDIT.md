# SSLCOMMERZ Integration - Security Audit & Hardening

## Security Audit Report
**Date**: February 1, 2026
**Auditor**: Claude AI Security Analysis
**Status**: ⚠️ **CRITICAL SECURITY ISSUES FOUND**

---

## Executive Summary

This security audit identified **8 security vulnerabilities** ranging from **CRITICAL to LOW** severity. Immediate action is required for production deployment.

### Severity Breakdown
- 🔴 **CRITICAL**: 3 issues
- 🟠 **HIGH**: 2 issues
- 🟡 **MEDIUM**: 2 issues
- 🟢 **LOW**: 1 issue

---

## Critical Issues (Must Fix Before Production)

### 🔴 Issue #1: Missing Authentication on Payment Initiation

**Location**: `routes/web.php:185`
**Severity**: CRITICAL
**CVSS Score**: 8.1 (High)

**Vulnerability**:
```php
Route::any('/pay', [SslCommerzPaymentController::class, 'index']);
```

The `/pay` endpoint has **NO authentication middleware**. Any user can initiate payments for ANY transaction ID by simply guessing or brute-forcing transaction IDs.

**Attack Scenario**:
1. Attacker enumerates transaction IDs (sequential or predictable)
2. Attacker calls `/pay?trx_id=TARGET_TRX_ID`
3. Attacker sees payment details for orders they don't own
4. Attacker potentially redirects to their own payment gateway

**Impact**:
- Unauthorized access to order details
- Potential payment redirection attacks
- Data exposure (customer PII, amounts)

**Solution**:
Add authentication middleware and verify ownership:

```php
// routes/web.php - Line 185
// CHANGE FROM:
Route::any('/pay', [SslCommerzPaymentController::class, 'index']);

// CHANGE TO:
Route::any('/pay', [SslCommerzPaymentController::class, 'index'])
    ->middleware(['auth', 'customer']);
```

**Controller Fix** (`SslCommerzPaymentController.php:57-63`):
```php
// Add after line 58:
// Verify order ownership
$first_order = $orders->first();
if ($first_order->user_id !== auth()->id()) {
    Log::warning('SSLCOMMERZ Payment: Unauthorized access attempt', [
        'trx_id' => $trx_id,
        'user_id' => auth()->id(),
        'order_owner' => $first_order->user_id,
    ]);
    abort(403, 'You are not authorized to access this order.');
}
```

---

### 🔴 Issue #2: Sensitive Data in Logs

**Location**: Multiple locations in `SslCommerzPaymentController.php`
**Severity**: CRITICAL
**CVSS Score**: 7.5 (High)

**Vulnerability**:
Sensitive payment information is being logged including:
- Full request data with card types
- Transaction IDs
- Customer PII (emails, phones, addresses)
- Amounts

**Affected Lines**:
- Line 40-45: Logs trx_id, code, IP, user_agent
- Line 248-257: Logs ALL payment data including card_type
- Line 448-458: Logs ALL IPN data

**Attack Scenario**:
1. Attacker gains access to server logs (file read vulnerability)
2. Attacker extracts customer PII and payment details
3. Attacker uses data for phishing or identity theft

**Impact**:
- GDPR/PCI-DSS compliance violations
- Customer data exposure
- Legal liability

**Solution**:
Create a secure logging utility:

**Step 1**: Create `app/Helpers/SecurityLogger.php`
```php
<?php

namespace App\Helpers;

class SecurityLogger
{
    /**
     * Sanitize data for logging
     */
    public static function sanitize(array $data): array
    {
        $sensitiveKeys = [
            'card_type', 'card_no', 'bank_tran_id', 'card_issuer',
            'cus_email', 'cus_phone', 'cus_add1', 'cus_add2',
            'ship_name', 'ship_phone', 'store_id', 'store_passwd'
        ];

        $sanitized = $data;

        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = self::mask($sanitized[$key]);
            }
        }

        // Mask email addresses
        if (isset($sanitized['cus_email'])) {
            $sanitized['cus_email'] = self::maskEmail($sanitized['cus_email']);
        }

        return $sanitized;
    }

    /**
     * Mask sensitive string
     */
    private static function mask(string $value): string
    {
        if (strlen($value) <= 4) {
            return '****';
        }
        return substr($value, 0, 2) . '****' . substr($value, -2);
    }

    /**
     * Mask email address
     */
    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '****@****.***';
        }
        $name = $parts[0];
        $domain = $parts[1];
        return substr($name, 0, 2) . '****@' . $domain;
    }
}
```

**Step 2**: Update Controller Imports
```php
// Add at top of SslCommerzPaymentController.php
use App\Helpers\SecurityLogger;
```

**Step 3**: Update Logging (Example for Line 248-257)
```php
// OLD CODE (Line 248-257):
Log::info('SSLCOMMERZ Success: Callback received', [
    'all_data' => $request->all(), // ❌ Logs everything
    // ...
]);

// NEW CODE:
Log::info('SSLCOMMERZ Success: Callback received', [
    'tran_id' => $request->input('tran_id'),
    'val_id' => $request->input('val_id'),
    'amount' => $request->input('amount'),
    'currency' => $request->input('currency'),
    'status' => $request->input('status'),
    'card_type' => $request->has('card_type') ? '****' : null, // Masked
    'ip' => $request->ip(),
    // ❌ Remove 'all_data' => $request->all()
]);
```

---

### 🔴 Issue #3: IPN Endpoint Open to Public Internet

**Location**: `routes/web.php:190`
**Severity**: CRITICAL
**CVSS Score**: 7.3 (High)

**Vulnerability**:
```php
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
```

The IPN endpoint has **NO source IP verification**. Any attacker can send fake IPN requests to mark orders as paid.

**Attack Scenario**:
1. Attacker creates an order
2. Attacker sends fake POST request to `/ipn` with valid transaction data
3. System validates with SSLCOMMERZ API (but attacker bypasses this)
4. If validation is weak or has timing issues, order gets marked as paid

**Current Validation** (Line 517-520):
```php
$sslc = new SslCommerzNotification();
$post_data = $request->all();
$validation = $sslc->orderValidate($post_data, $tran_id, $amount, $currency);
```

**The validation calls SSLCOMMERZ API, which is good, BUT:**
- No rate limiting (can be used for DoS)
- No source IP whitelist
- No signature verification
- Logs expose all data (see Issue #2)

**Solution**:

**Step 1**: Create IP Whitelist Middleware
Create `app/Http/Middleware/SslcommerzIpWhitelist.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SslcommerzIpWhitelist
{
    /**
     * SSLCOMMERZ IP ranges
     * Source: https://developer.sslcommerz.com/
     */
    protected $allowedIps = [
        // Sandbox IPs
        '103.163.226.132',
        '103.163.226.133',
        // Production IPs (verify with SSLCOMMERZ)
        '103.163.226.128',
        '103.163.226.129',
        '103.163.226.130',
        '103.163.226.131',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestIp = $request->ip();

        // Check if IP is in whitelist
        if (!in_array($requestIp, $this->allowedIps)) {
            Log::warning('SSLCOMMERZ IPN: Blocked unauthorized IP', [
                'ip' => $requestIp,
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
```

**Step 2**: Register Middleware
Add to `app/Http/Kernel.php`:
```php
protected $middlewareAliases = [
    // ... existing middleware
    'sslcommerz.ip' => \App\Http\Middleware\SslcommerzIpWhitelist::class,
];
```

**Step 3**: Apply to IPN Route
```php
// routes/web.php - Line 190
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn'])
    ->middleware('sslcommerz.ip');
```

**Step 4**: Add Rate Limiting
```php
// Add to routes/web.php
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn'])
    ->middleware(['sslcommerz.ip', 'throttle:60,1']); // 60 requests per minute
```

---

## High Severity Issues

### 🟠 Issue #4: Missing Signature Verification

**Location**: `SslCommerzPaymentController.php:446-592`
**Severity**: HIGH
**CVSS Score**: 6.8 (Medium)

**Vulnerability**:
The IPN handler doesn't verify SSLCOMMERZ's signature (hash) that proves the request genuinely came from SSLCOMMERZ.

**Current Code** (Line 517-520):
```php
$sslc = new SslCommerzNotification();
$post_data = $request->all();
$validation = $sslc->orderValidate($post_data, $tran_id, $amount, $currency);
```

**The `orderValidate()` method exists but doesn't check signature.**

**Attack Scenario**:
1. Attacker gets valid transaction details from success callback
2. Attacker sends fake IPN with manipulated amount
3. System validates with SSLCOMMERZ API (amount mismatch - caught)
4. BUT if API is slow/down, system might use cached/stale data

**Solution**:
Verify signature before processing:

**Add to `SslCommerzPaymentController.php`:**
```php
/**
 * Verify SSLCOMMERZ signature
 */
private function verifySignature(Request $request, $storePassword): bool
{
    if (!$request->has('verify_sign') || !$request->has('verify_key')) {
        return false;
    }

    $verifySign = $request->input('verify_sign');
    $verifyKey = $request->input('verify_key');

    // Build hash string
    $keys = explode(',', $verifyKey);
    $hashData = [];

    foreach ($keys as $key) {
        if ($request->has($key)) {
            $hashData[$key] = $request->input($key);
        }
    }

    // Add store password
    $hashData['store_passwd'] = md5($storePassword);

    // Sort by key
    ksort($hashData);

    // Build hash string
    $hashString = '';
    foreach ($hashData as $key => $value) {
        $hashString .= $key . '=' . $value . '&';
    }
    $hashString = rtrim($hashString, '&');

    // Calculate hash
    $calculatedHash = md5($hashString);

    return $calculatedHash === $verifySign;
}
```

**Update IPN Method** (Line 517):
```php
// OLD:
$sslc = new SslCommerzNotification();
$post_data = $request->all();
$validation = $sslc->orderValidate($post_data, $tran_id, $amount, $currency);

// NEW:
$store_password = settingHelper('sslcommerz_password');

// Verify signature FIRST
if (!$this->verifySignature($request, $store_password)) {
    Log::error('SSLCOMMERZ IPN: Invalid signature', [
        'tran_id' => $tran_id,
        'ip' => $request->ip(),
    ]);
    return response()->json(['error' => 'Invalid signature'], 403);
}

// Then validate
$sslc = new SslCommerzNotification();
$post_data = $request->all();
$validation = $sslc->orderValidate($post_data, $tran_id, $amount, $currency);
```

---

### 🟠 Issue #5: Missing Rate Limiting on Payment Endpoints

**Location**: `routes/web.php:185-190`
**Severity**: HIGH
**CVSS Score**: 6.5 (Medium)

**Vulnerability**:
No rate limiting on payment endpoints allows:
- Brute force attacks on transaction IDs
- DoS attacks on payment initiation
- IPN flooding

**Affected Routes**:
- `/pay` - Payment initiation
- `/success` - Success callback
- `/fail` - Fail callback
- `/cancel` - Cancel callback
- `/ipn` - IPN handler

**Attack Scenario**:
1. Attacker sends thousands of requests to `/pay` with different trx_ids
2. Server resources exhausted
3. Legitimate users cannot process payments

**Solution**:
Add rate limiting to all SSLCOMMERZ routes:

```php
// routes/web.php
Route::any('/pay', [SslCommerzPaymentController::class, 'index'])
    ->middleware('throttle:10,1'); // 10 requests per minute per user

Route::post('/success', [SslCommerzPaymentController::class, 'success'])
    ->middleware('throttle:30,1'); // 30 per minute

Route::post('/fail', [SslCommerzPaymentController::class, 'fail'])
    ->middleware('throttle:30,1');

Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel'])
    ->middleware('throttle:30,1');

Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn'])
    ->middleware(['sslcommerz.ip', 'throttle:60,1']); // IP whitelist + 60 per minute
```

---

## Medium Severity Issues

### 🟡 Issue #6: Insufficient Amount Validation

**Location**: `SslCommerzPaymentController.php:522-528`
**Severity**: MEDIUM
**CVSS Score**: 5.3 (Medium)

**Vulnerability**:
Amount validation has tolerance of ±1 BDT which might be too high for small transactions.

**Current Code** (In `SslCommerzNotification.php:107`):
```php
if (trim($merchant_trans_id) == trim($tran_id) &&
    (abs($merchant_trans_amount - $amount) < 1) && // ❌ Allows 1 BDT difference
    trim($merchant_trans_currency) == trim('BDT')) {
    return true;
}
```

**Attack Scenario**:
1. Legitimate order for 100 BDT
2. Attacker manipulates IPN to show 99 BDT
3. System accepts it (within tolerance)
4. Customer pays 99 but gets 100 worth of goods

**For small amounts (< 10 BDT), 1 BDT is 10% tolerance!**

**Solution**:
Use percentage-based tolerance with strict minimum:

**Update `SslCommerzNotification.php:107`**:
```php
// OLD:
if (trim($merchant_trans_id) == trim($tran_id) &&
    (abs($merchant_trans_amount - $amount) < 1) &&
    trim($merchant_trans_currency) == trim('BDT')) {
    return true;
}

// NEW:
$amountDiff = abs($merchant_trans_amount - $amount);
$allowableDiff = max(0.01, $merchant_trans_amount * 0.01); // 1% or 0.01 BDT minimum

if (trim($merchant_trans_id) == trim($tran_id) &&
    $amountDiff <= $allowableDiff && // Use percentage-based tolerance
    trim($merchant_trans_currency) == trim('BDT')) {
    return true;
} else {
    $this->error = "Data has been tempered";
    return false;
}
```

---

### 🟡 Issue #7: Missing CSRF Protection on Success/Fail/Cancel Routes

**Location**: `app/Http/Middleware/VerifyCsrfToken.php:18`
**Severity**: MEDIUM
**CVSS Score**: 4.3 (Medium)

**Vulnerability**:
The callback routes (`/success`, `/fail`, `/cancel`) are excluded from CSRF verification.

**Current Code**:
```php
protected $except = [
    // ...
    '/success','/cancel','/fail','/ipn'
];
```

**Why this is a problem**:
While these routes need to accept POST requests from SSLCOMMERZ (external source), completely disabling CSRF creates risk.

**Attack Scenario**:
1. Attacker creates fake HTML form pointing to your `/success` endpoint
2. Tricks user into submitting form (CSRF-like attack)
3. While API validation would catch fake payments, it wastes resources

**Solution**:
Remove from CSRF exemption and handle SSLCOMMERZ callbacks properly:

**Step 1**: Update `VerifyCsrfToken.php`
```php
// REMOVE from $except:
// '/success','/cancel','/fail','/ipn'

// Keep only IPN exempted (SSLCOMMERZ doesn't send CSRF token)
protected $except = [
    'user/complete-order','user/complete-order/*','get/ssl-response','sslcommerz/ipn','invoice/*',
    '/user/recharge-wallet','/user/recharge-wallet/*','paystack/initialize','paytm/success*',
    'user/complete-recharge','user/complete-recharge*',
    'my-wallet','payment','seller/complete-purchase','user/payment/paytmRedirect',
    'paytm/success','/pay-via-ajax','/ipn' // Keep only /ipn exempted
];
```

**Step 2**: Update `routes/web.php`
```php
// Add CSRF exemption only for IPN (external server-to-server)
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('sslcommerz.ip');

// Other routes keep CSRF protection
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
```

---

## Low Severity Issues

### 🟢 Issue #8: Verbose Error Messages

**Location**: Multiple locations
**Severity**: LOW
**CVSS Score**: 3.1 (Low)

**Vulnerability**:
Error messages might expose internal implementation details.

**Examples**:
```php
// Line 54: "Transaction ID is required" - OK
// Line 62: "Order not found" - OK
// Line 84: "BDT currency not configured. Please contact admin." - ❌ Exposes config issue
// Line 269: "Order not found. Please contact support." - OK
```

**Solution**:
Use generic error messages for users, log details internally:

```php
// OLD (Line 84):
return back()->with(['error' => 'BDT currency not configured. Please contact admin.']);

// NEW:
Log::error('SSLCOMMERZ: BDT currency not configured');
return back()->with(['error' => 'Payment system configuration error. Please contact support.']);
```

---

## Additional Security Recommendations

### 1. Add Payment Timeout Validation

Add timestamp validation to prevent replay attacks:

```php
// In ipn() method, after line 461
$paymentTimestamp = $request->input('tran_date');
if ($paymentTimestamp) {
    $paymentTime = strtotime($paymentTimestamp);
    $currentTime = time();
    $timeDiff = abs($currentTime - $paymentTime);

    // Reject payments older than 1 hour
    if ($timeDiff > 3600) {
        Log::warning('SSLCOMMERZ IPN: Old payment timestamp', [
            'tran_id' => $tran_id,
            'tran_date' => $paymentTimestamp,
            'time_diff' => $timeDiff,
        ]);
        return response()->json(['error' => 'Payment expired'], 400);
    }
}
```

### 2. Add Order Amount Locking

Prevent order amount changes after payment initiation:

```php
// In index() method, add after line 90
// Lock the order amount to prevent changes during payment
foreach ($orders as $order) {
    if ($order->is_completed == 0) {
        $order->locked_amount = $total_amount;
        $order->save();
    }
}
```

### 3. Implement Queue for IPN Processing

Move IPN processing to background queue for better performance and retry logic:

```php
// Create Job: app/Jobs/ProcessSslcommerzIpn.php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSslcommerzIpn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ipnData;

    public function __construct(array $ipnData)
    {
        $this->ipnData = $ipnData;
    }

    public function handle()
    {
        // Process IPN logic here
        // Similar to current ipn() method
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SSLCOMMERZ IPN Job Failed', [
            'error' => $exception->getMessage(),
            'ipn_data' => $this->ipnData,
        ]);
    }
}
```

### 4. Add Two-Factor Verification for High-Value Orders

```php
// In index() method, add after line 90
if ($total_amount > 100000) { // Over 100,000 BDT
    // Require additional verification
    session(['sslcommerz_high_value' => true]);
}
```

### 5. Implement Payment Fraud Detection

Add fraud scoring based on:
- Transaction velocity (same user, multiple payments)
- Amount anomalies (sudden large amounts)
- Geographic anomalies (shipping vs billing address)

---

## Production Deployment Checklist

Before going live, ensure ALL critical issues are resolved:

- [ ] **Issue #1**: Add authentication to `/pay` route
- [ ] **Issue #1**: Verify order ownership in controller
- [ ] **Issue #2**: Implement secure logging (SecurityLogger)
- [ ] **Issue #2**: Remove sensitive data from all logs
- [ ] **Issue #3**: Create and apply SslcommerzIpWhitelist middleware
- [ ] **Issue #3**: Add rate limiting to IPN endpoint
- [ ] **Issue #4**: Implement signature verification
- [ ] **Issue #5**: Add rate limiting to all payment routes
- [ ] **Issue #6**: Fix amount validation tolerance
- [ ] **Issue #7**: Remove CSRF exemption from success/fail/cancel
- [ ] **Additional**: Add payment timeout validation
- [ ] **Additional**: Implement order amount locking
- [ ] **Additional**: Consider queue-based IPN processing

---

## Security Testing Commands

```bash
# Test authentication bypass attempt
curl -X GET "http://localhost:8000/pay?trx_id=TEST123"
# Expected: 401 Unauthorized or redirect to login

# Test IPN without IP whitelist
curl -X POST http://localhost:8000/ipn \
  -d "tran_id=TEST&amount=100"
# Expected: 403 Forbidden

# Test rate limiting
for i in {1..20}; do
  curl -X GET "http://localhost:8000/pay?trx_id=TEST$i"
done
# Expected: 429 Too Many Requests after 10 requests

# Test signature verification
curl -X POST http://localhost:8000/ipn \
  -d "tran_id=VALID_TRX&verify_sign=INVALID"
# Expected: 403 Forbidden
```

---

## Compliance Notes

### PCI-DSS Compliance
- Never store full card numbers (only last 4 digits)
- Never store CVV codes
- Use SSL/TLS for all payment communications
- Implement access controls for payment data
- Regular security audits
- Maintain vulnerability management program

### GDPR Compliance
- Log only necessary data
- Implement data retention policies
- Provide data export/deletion capabilities
- Use encryption for data at rest
- Have lawful basis for processing payment data

---

## Monitoring & Alerting

Set up alerts for:
1. Failed IPN signature verification
2. IPN requests from non-whitelisted IPs
3. Rate limit violations
4. Unusual payment patterns (high amounts, high frequency)
5. API validation failures

Example alert configuration:
```php
// In ipn() method, add monitoring
if (!$validation) {
    // Send alert to admin
    Notification::route('mail', config('app.security_email'))
        ->notify(new SecurityAlert([
            'type' => 'IPN Validation Failed',
            'tran_id' => $tran_id,
            'ip' => $request->ip(),
            'details' => $sslc->error,
        ]));
}
```

---

## Summary Table

| Issue | Severity | Fixed in Code | Production Ready |
|-------|----------|----------------|------------------|
| #1: Missing Auth | CRITICAL | ❌ | NO |
| #2: Data in Logs | CRITICAL | ❌ | NO |
| #3: IPN Open | CRITICAL | ❌ | NO |
| #4: No Signature | HIGH | ❌ | NO |
| #5: No Rate Limit | HIGH | ❌ | NO |
| #6: Amount Validation | MEDIUM | ❌ | NO |
| #7: CSRF Exempt | MEDIUM | ❌ | NO |
| #8: Verbose Errors | LOW | ❌ | NO |

---

**IMPORTANT**: Do NOT deploy to production until at least all CRITICAL and HIGH issues are resolved.

---

*Audit Version: 1.0*
*Last Updated: February 1, 2026*
*Next Audit Recommended: After implementing fixes*