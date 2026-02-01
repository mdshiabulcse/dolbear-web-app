# SSLCOMMERZ Integration - File Location Reference

## Complete File-by-File Changes Reference

This document provides the exact location of every change made for the SSLCOMMERZ integration.

---

## Table of Contents
1. [Controllers](#controllers)
2. [Library Files](#library-files)
3. [Routes](#routes)
4. [Frontend Components](#frontend-components)
5. [Configuration Files](#configuration-files)
6. [Documentation Files](#documentation-files)

---

## Controllers

### File: `app/Http/Controllers/SslCommerzPaymentController.php`

**Full Path**: `C:\shiab\dolbear-web-app\app\Http\Controllers\SslCommerzPaymentController.php`

**Status**: NEW FILE CREATED (Complete implementation)

**Lines Breakdown**:

| Lines | Method/Section | Description |
|-------|----------------|-------------|
| 1-11 | Namespace & Imports | `use DB`, `use Log`, `use SslCommerzNotification`, etc. |
| 14-19 | Constructor | `__construct(OrderInterface $order)` |
| 21-29 | Example Methods | `exampleEasyCheckout()`, `exampleHostedCheckout()` |
| **38-229** | **index() Method** | **MAIN: Payment initiation logic** |
| 40-55 | - Request logging & validation | Get trx_id, validate input |
| 57-78 | - Order retrieval | Find orders by trx_id, get code |
| 80-97 | - Currency calculation | Get BDT currency, convert amount |
| 99-124 | - Customer info & URLs | Get customer data, prepare callback URLs |
| 126-136 | - Credential validation | Check store_id and password |
| 138-183 | - Payment data preparation | Build post_data array with all fields |
| 185-201 | - SSLCOMMERZ API call | Set config, add URLs, call makePayment() |
| 203-227 | - Response handling | Redirect or error handling |
| **246-372** | **success() Method** | **SUCCESS CALLBACK HANDLER** |
| 248-262 | - Log & extract data | Get tran_id, amount, currency, code |
| 264-286 | - Duplicate check | Check if order already completed |
| 288-345 | - Validation & completion | Validate with SSLCOMMERZ, complete order |
| 347-352 | - Invoice redirect | Redirect to invoice page |
| **380-419** | **fail() Method** | **FAIL CALLBACK HANDLER** |
| 382-408 | - Update order status | Mark order as failed (-1) |
| 410-417 | - Error redirect | Redirect to payment page with error |
| **427-436** | **cancel() Method** | **CANCEL CALLBACK HANDLER** |
| 429-435 | - User cancellation | Redirect with info message |
| **446-592** | **ipn() Method** | **IPN HANDLER (Server-to-Server)** |
| 448-466 | - Log & validate | Receive IPN, validate input |
| 468-492 | - Duplicate prevention | Check if already processed |
| 494-528 | - Transaction validation | Validate with SSLCOMMERZ API |
| 530-569 | - Order completion | Complete order, handle errors |
| 571-575 | - Success response | Return JSON success |
| 577-591 | - Error response | Return JSON error |
| **601-696** | **Helper Methods** | **PRIVATE HELPER FUNCTIONS** |
| 601-604 | - getCurrency() | Get BDT currency from database |
| 609-626 | - activeCurrencyCheck() | Get user's active currency |
| 631-647 | - amountCalculator() | Convert amount to BDT |
| 652-696 | - getCustomerInfo() | Extract customer info from order |

**Key Code Locations**:

```php
// LINE 185-201: Payment initiation with dynamic URLs
config(['sslcommerz.apiDomain' => $api_domain]);
config(['sslcommerz.apiCredentials.store_id' => $store_id]);
config(['sslcommerz.apiCredentials.store_password' => $store_password]);
config(['sslcommerz.connect_from_localhost' => env('IS_LOCALHOST', false)]);

// Add callback URLs to post_data for dynamic URLs with query parameters
$post_data['success_url'] = $success_url;
$post_data['fail_url'] = $fail_url;
$post_data['cancel_url'] = $cancel_url;
$post_data['ipn_url'] = $ipn_url;

// Initiate SSLCOMMERZ payment
$sslc = new SslCommerzNotification();
$payment_options = $sslc->makePayment($post_data, 'hosted');
```

---

## Library Files

### File: `app/Library/SslCommerz/SslCommerzNotification.php`

**Full Path**: `C:\shiab\dolbear-web-app\app\Library\SslCommerz\SslCommerzNotification.php`

**Status**: MODIFIED (Lines 291-334)

**Changes Made**:

| Lines | Before | After |
|-------|--------|-------|
| 291-334 | Fixed URLs from config | Dynamic URLs from $info array |

**Exact Code Change**:

**BEFORE (Lines 298-316)**:
```php
// Set the SUCCESS, FAIL, CANCEL Redirect URL before setting the other parameters
$this->setSuccessUrl();
$this->setFailedUrl();
$this->setCancelUrl();
$this->setIPNUrl();

$this->data['success_url'] = $this->getSuccessUrl();
$this->data['fail_url'] = $this->getFailedUrl();
$this->data['cancel_url'] = $this->getCancelUrl();
```

**AFTER (Lines 298-319)**:
```php
// Set the SUCCESS, FAIL, CANCEL Redirect URL before setting the other parameters
// Allow URLs to be passed in $info array for dynamic callback URLs with query parameters
if (!empty($info['success_url'])) {
    $this->data['success_url'] = $info['success_url'];
} else {
    $this->setSuccessUrl();
    $this->data['success_url'] = $this->getSuccessUrl();
}

if (!empty($info['fail_url'])) {
    $this->data['fail_url'] = $info['fail_url'];
} else {
    $this->setFailedUrl();
    $this->data['fail_url'] = $this->getFailedUrl();
}

if (!empty($info['cancel_url'])) {
    $this->data['cancel_url'] = $info['cancel_url'];
} else {
    $this->setCancelUrl();
    $this->data['cancel_url'] = $this->getCancelUrl();
}

if (!empty($info['ipn_url'])) {
    $this->data['ipn_url'] = $info['ipn_url'];
} else {
    $this->setIPNUrl();
    $this->data['ipn_url'] = $this->getIPNUrl();
}
```

**Other Methods in File** (Reference Only):
| Lines | Method | Purpose |
|-------|--------|---------|
| 19-25 | __construct() | Load config and credentials |
| 27-35 | orderValidate() | Validate transaction with SSLCOMMERZ |
| 39-139 | validate() | Protected validation method |
| 142-179 | SSLCOMMERZ_hash_verify() | Verify hash signature |
| 187-223 | makePayment() | Initiate payment to SSLCOMMERZ |
| 225-263 | URL getters/setters | Protected URL methods |
| 388-402 | setCustomerInfo() | Set customer information |
| 404-418 | setShipmentInfo() | Set shipment information |
| 420-465 | setProductInfo() | Set product information |
| 467-475 | setAdditionalInfo() | Set custom parameters (value_a-d) |

---

## Routes

### File: `routes/web.php`

**Full Path**: `C:\shiab\dolbear-web-app\routes\web.php`

**Status**: MODIFIED (Lines 182-191)

**Exact Location**:

Find this section in `routes/web.php` (around line 182-191, BEFORE catch-all routes):

**ADDED CODE**:
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

**IMPORTANT**: These routes MUST be placed BEFORE any catch-all routes like:
```php
Route::get('/{anypath}', ...);
```

**Context** (Lines 175-195):
```php
// ... previous routes ...

// 182-191: SSLCOMMERZ routes added here
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

// Catch-all routes (must come AFTER SSLCOMMERZ routes)
Route::get('/{anypath}', ...);
```

---

## Frontend Components

### File: `resources/js/components/frontend/pages/cart_new.vue`

**Full Path**: `C:\shiab\dolbear-web-app\resources\js\components\frontend\pages\cart_new.vue`

**Status**: MODIFIED (Lines 1058-1087)

**Exact Location**:

Find the `confirmOrder()` method in cart_new.vue (around line 1058-1087):

**ADDED/MODIFIED CODE**:
```javascript
// API call
const url = this.getUrl('user/confirm-order');
const response = await axios.post(url, requestData);

if (response.data.error) {
  this.$Progress.fail();
  toastr.error(response.data.error, this.lang.Error + ' !!');
  this.loading = false;
} else {
  this.$Progress.finish();
  toastr.success('Order confirmed successfully!', 'Success');

  await this.takeOrders();

  // NEW CODE ADDED HERE:
  if (this.payment_form.payment_method === 'online_payment') {
    // For online payment: redirect to SSLCOMMERZ payment initiation
    // The takeOrders() method will have fetched the order with the code
    // Redirect using trx_id only (code will be fetched from order)
    window.location.href = this.getUrl(
        "pay?trx_id=" + this.trx_id
    );
    return;
  }

  // EXISTING CODE: For Cash on Delivery
  this.completeOrders();
}
```

**Context**:
```javascript
// Around line 1058: Inside confirmOrder() method
async confirmOrder() {
  // ... validation code ...

  // API call to confirm order
  const url = this.getUrl('user/confirm-order');
  const response = await axios.post(url, requestData);

  if (response.data.error) {
    // ... error handling ...
  } else {
    this.$Progress.finish();
    toastr.success('Order confirmed successfully!', 'Success');

    await this.takeOrders();

    // === NEW CODE STARTS HERE (Line ~1068) ===
    if (this.payment_form.payment_method === 'online_payment') {
      window.location.href = this.getUrl("pay?trx_id=" + this.trx_id);
      return;
    }
    // === NEW CODE ENDS HERE ===

    // Existing Cash on Delivery flow
    this.completeOrders();
  }
}
```

---

## Configuration Files

### File: `config/sslcommerz.php`

**Full Path**: `C:\shiab\dolbear-web-app\config\sslcommerz.php`

**Status**: EXISTING FILE (Reference Only)

**Purpose**: SSLCOMMERZ configuration file

**Content**:
```php
<?php
// SSLCOMMERZ configuration

$apiDomain = env('SSLCZ_TESTMODE') ? "https://sandbox.sslcommerz.com" : "https://securepay.sslcommerz.com";

return [
    'apiCredentials' => [
        'store_id' => env("SSLCZ_STORE_ID"),
        'store_password' => env("SSLCZ_STORE_PASSWORD"),
    ],
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],
    'apiDomain' => $apiDomain,
    'connect_from_localhost' => env("IS_LOCALHOST", false),
    'success_url' => '/success',
    'failed_url' => '/fail',
    'cancel_url' => '/cancel',
    'ipn_url' => '/ipn',
];
```

### File: `.env`

**Full Path**: `C:\shiab\dolbear-web-app\.env`

**Status**: MODIFIED (Add these variables)

**ADD TO .env**:
```env
# SSLCOMMERZ Configuration
SSLCZ_TESTMODE=true
SSLCZ_STORE_ID=your_test_store_id
SSLCZ_STORE_PASSWORD=your_test_store_password
IS_LOCALHOST=true
```

**Production Values**:
```env
SSLCZ_TESTMODE=false
SSLCZ_STORE_ID=your_live_store_id
SSLCZ_STORE_PASSWORD=your_live_store_password
IS_LOCALHOST=false
```

---

## Documentation Files Created

### 1. `docs/SSLCOMMERZ_INTEGRATION.md`
**Full Path**: `C:\shiab\dolbear-web-app\docs\SSLCOMMERZ_INTEGRATION.md`
- Complete integration documentation (500+ lines)

### 2. `docs/SSLCOMMERZ_QUICK_REFERENCE.md`
**Full Path**: `C:\shiab\dolbear-web-app\docs\SSLCOMMERZ_QUICK_REFERENCE.md`
- Quick reference guide (150+ lines)

### 3. `docs/SSLCOMMERZ_ARCHITECTURE.md`
**Full Path**: `C:\shiab\dolbear-web-app\docs\SSLCOMMERZ_ARCHITECTURE.md`
- Architecture diagrams and flows (400+ lines)

### 4. `docs/SSLCOMMERZ_CHANGELOG.md`
**Full Path**: `C:\shiab\dolbear-web-app\docs\SSLCOMMERZ_CHANGELOG.md`
- Implementation changelog (400+ lines)

### 5. `docs/SSLCOMMERZ_FILE_LOCATIONS.md` (This File)
**Full Path**: `C:\shiab\dolbear-web-app\docs\SSLCOMMERZ_FILE_LOCATIONS.md`
- This file location reference

---

## Summary Table of All Changes

| File Path | Status | Lines Changed | Type |
|-----------|--------|---------------|------|
| `app/Http/Controllers/SslCommerzPaymentController.php` | NEW | 1-697 | Controller |
| `app/Library/SslCommerz/SslCommerzNotification.php` | MODIFIED | 291-334 | Library |
| `routes/web.php` | MODIFIED | 182-191 | Routes |
| `resources/js/components/frontend/pages/cart_new.vue` | MODIFIED | 1058-1087 | Frontend |
| `config/sslcommerz.php` | EXISTING | - | Config |
| `.env` | MODIFIED | ADD | Environment |

---

## Line Number Quick Reference

### Payment Initiation Flow
```
cart_new.vue:1068-1074
    ↓ (redirect)
web.php:187 (Route::any('/pay'))
    ↓
SslCommerzPaymentController.php:38 (index method)
    ↓
SslCommerzPaymentController.php:185-201 (prepare & call API)
    ↓
SslCommerzNotification.php:187 (makePayment)
    ↓
SslCommerzNotification.php:291-334 (setRequiredInfo with URLs)
    ↓
SslCommerzNotification.php:198 (setParams)
    ↓
SslCommerzNotification.php:268 (callToApi)
    ↓
SslCommerzNotification.php:206 (formatResponse)
```

### Success Callback Flow
```
SSLCOMMERZ Gateway
    ↓ (POST /success)
web.php:188 (Route::post('/success'))
    ↓
SslCommerzPaymentController.php:246 (success method)
    ↓
SslCommerzPaymentController.php:303 (orderValidate)
    ↓
SslCommerzNotification.php:39 (validate method)
    ↓
SslCommerzPaymentController.php:330 (completeOrder)
    ↓
SslCommerzPaymentController.php:348 (redirect to invoice)
```

### IPN Flow
```
SSLCOMMERZ Server
    ↓ (POST /ipn)
web.php:191 (Route::post('/ipn'))
    ↓
SslCommerzPaymentController.php:446 (ipn method)
    ↓
SslCommerzPaymentController.php:518 (orderValidate)
    ↓
SslCommerzPaymentController.php:551 (completeOrder)
    ↓
SslCommerzPaymentController.php:571 (JSON response)
```

---

## Visual File Tree

```
dolbear-web-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── SslCommerzPaymentController.php    [NEW FILE - 697 lines]
│   └── Library/
│       └── SslCommerz/
│           ├── AbstractSslCommerz.php            [EXISTING - Reference]
│           ├── SslCommerzInterface.php           [EXISTING - Reference]
│           └── SslCommerzNotification.php        [MODIFIED - Lines 291-334]
├── config/
│   └── sslcommerz.php                             [EXISTING - Reference]
├── docs/
│   ├── SSLCOMMERZ_INTEGRATION.md                  [NEW - Documentation]
│   ├── SSLCOMMERZ_QUICK_REFERENCE.md              [NEW - Reference]
│   ├── SSLCOMMERZ_ARCHITECTURE.md                 [NEW - Diagrams]
│   ├── SSLCOMMERZ_CHANGELOG.md                    [NEW - Changelog]
│   └── SSLCOMMERZ_FILE_LOCATIONS.md               [NEW - This file]
├── resources/
│   └── js/
│       └── components/
│           └── frontend/
│               └── pages/
│                   └── cart_new.vue               [MODIFIED - Lines 1058-1087]
├── routes/
│   └── web.php                                     [MODIFIED - Lines 182-191]
└── .env                                            [MODIFIED - ADD variables]
```

---

## Verification Commands

### Check Controller Exists
```bash
# Check if controller file exists
ls -la app/Http/Controllers/SslCommerzPaymentController.php

# Check file size (should be ~25KB)
wc -l app/Http/Controllers/SslCommerzPaymentController.php
```

### Check Routes
```bash
# List SSLCOMMERZ routes
php artisan route:list | grep -E "(pay|success|fail|ipn)"

# Expected output:
# GET|HEAD|POST|PUT|PATCH|DELETE  pay             ...  App\Http\Controllers\SslCommerzPaymentController@index
# POST                          success         ...  App\Http\Controllers\SslCommerzPaymentController@success
# POST                          fail            ...  App\Http\Controllers\SslCommerzPaymentController@fail
# POST                          cancel          ...  App\Http\Controllers\SslCommerzPaymentController@cancel
# POST                          ipn             ...  App\Http\Controllers\SslCommerzPaymentController@ipn
```

### Check Library Modification
```bash
# Check modified lines in SslCommerzNotification.php
sed -n '291,334p' app/Library/SslCommerz/SslCommerzNotification.php
```

### Check Frontend Modification
```bash
# Check modified lines in cart_new.vue
sed -n '1058,1087p' resources/js/components/frontend/pages/cart_new.vue
```

---

## Related Files (Reference Only)

These files are part of the SSLCOMMERZ library but were NOT modified:

| File | Purpose |
|------|---------|
| `app/Library/SslCommerz/AbstractSslCommerz.php` | Base class with protected methods |
| `app/Library/SslCommerz/SslCommerzInterface.php` | Interface definition |

---

*Document Version: 1.0*
*Last Updated: February 1, 2026*
*Purpose: Exact file location reference for SSLCOMMERZ integration*