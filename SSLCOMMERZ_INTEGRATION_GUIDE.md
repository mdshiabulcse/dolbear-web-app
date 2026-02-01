# SSLCOMMERZ Payment Gateway Integration Guide

## Table of Contents
1. [Overview](#overview)
2. [Getting Started with SSLCOMMERZ](#getting-started-with-sslcommerz)
3. [Configuration](#configuration)
4. [API Endpoints](#api-endpoints)
5. [Integration Components](#integration-components)
6. [Payment Flow](#payment-flow)
7. [Required Parameters](#required-parameters)
8. [Optional Parameters](#optional-parameters)
9. [EMI Configuration](#emi-configuration)
10. [Security & Validation](#security--validation)
11. [Testing](#testing)
12. [Going Live](#going-live)
13. [Troubleshooting](#troubleshooting)

---

## Overview

**SSLCOMMERZ** is Bangladesh's leading payment gateway aggregator, enabling merchants to accept payments through:
- Credit/Debit Cards (Visa, MasterCard, AMEX)
- Mobile Banking (bKash, Nagad, Rocket, etc.)
- Internet Banking
- Other digital payment methods

### Project Integration Status
This project has a complete SSLCOMMERZ integration using a custom library located at:
- **Library Path**: `app/Library/SslCommerz/`
- **Config**: `config/sslcommerz.php`
- **Controller**: `app/Http/Controllers/Site/PaymentController.php`

---

## Getting Started with SSLCOMMERZ

### 1. Create SSLCOMMERZ Account

1. Visit [SSLCOMMERZ](https://sslcommerz.com/)
2. Register as a merchant
3. Complete KYC verification
4. Obtain **Store ID** and **Store Password** from dashboard

### 2. Environment Setup

SSLCOMMERZ provides two environments:

| Environment | API Domain | Purpose |
|------------|-----------|---------|
| **Sandbox** | `https://sandbox.sslcommerz.com` | Testing & Development |
| **Live** | `https://securepay.sslcommerz.com` | Production Transactions |

---

## Configuration

### 1. Config File (`config/sslcommerz.php`)

```php
<?php

return [
    'projectPath' => env('PROJECT_PATH'),

    // API Domain Configuration
    // Sandbox: "https://sandbox.sslcommerz.com"
    // Live: "https://securepay.sslcommerz.com"
    'apiDomain' => "https://sandbox.sslcommerz.com",

    // API Credentials
    'apiCredentials' => [
        'store_id' => '',           // Your SSLCOMMERZ Store ID
        'store_password' => '',     // Your SSLCOMMERZ Store Password
    ],

    // API Endpoints
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],

    // Localhost Development
    'connect_from_localhost' => env("IS_LOCALHOST", true),

    // Callback URLs
    'success_url' => '/',
    'failed_url' => config('services.ssl_commerze.FAIL_URL'),
    'cancel_url' => config('services.ssl_commerze.CANCEL_URL'),
    'ipn_url' => '/ipn',
];
```

### 2. Database Settings (`database/seeders/Admin/SettingsSeeder.php`)

```php
// SSLCOMMERZ Settings
Setting::create(['title' => 'sslcommerz_id',                           'value'  => 'ecomm621c6cee01086',           'lang' => 'en']);
Setting::create(['title' => 'sslcommerz_password',                     'value'  => 'ecomm621c6cee01086@ssl',       'lang' => 'en']);
Setting::create(['title' => 'is_sslcommerz_activated',                 'value'  => 1,                              'lang' => 'en']);
Setting::create(['title' => 'is_sslcommerz_sandbox_mode_activated',    'value'  => 0,                              'lang' => 'en']);
```

### 3. Environment Variables

Choose the appropriate environment setup below:

---

## Development Mode (Sandbox)

### Overview

Development mode uses SSLCOMMERZ's sandbox environment for testing payment integration without processing real transactions. All payments are simulated.

### Sandbox Environment Setup

#### 1. `.env` File - Development Configuration

```env
# ============================================
# DEVELOPMENT / SANDBOX MODE
# ============================================

# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# SSLCOMMERZ Sandbox Configuration
PROJECT_PATH=http://localhost:8000
IS_LOCALHOST=true

# Sandbox Credentials (Provided by SSLCOMMERZ)
SSLCOMMERZ_STORE_ID=ecomm621c6cee01086
SSLCOMMERZ_STORE_PASSWORD=ecomm621c6cee01086@ssl
SSLCOMMERZ_ACTIVATED=1
SSLCOMMERZ_SANDBOX_MODE=1
```

#### 2. Config File - `config/sslcommerz.php` (Development)

```php
<?php

return [
    'projectPath' => env('PROJECT_PATH', 'http://localhost:8000'),

    // SANDBOX API Domain
    'apiDomain' => "https://sandbox.sslcommerz.com",

    // Sandbox Credentials
    'apiCredentials' => [
        'store_id' => env('SSLCOMMERZ_STORE_ID', 'ecomm621c6cee01086'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', 'ecomm621c6cee01086@ssl'),
    ],

    // API Endpoints
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],

    // Development Mode - Allow localhost
    'connect_from_localhost' => env("IS_LOCALHOST", true),

    // Callback URLs
    'success_url' => '/',
    'failed_url' => config('services.ssl_commerze.FAIL_URL'),
    'cancel_url' => config('services.ssl_commerze.CANCEL_URL'),
    'ipn_url' => '/ipn',
];
```

#### 3. Database Settings - Development

Run in Tinker or update via Admin Panel:

```php
// php artisan tinker
use App\Models\Setting;

// Development/Sandbox Settings
Setting::updateOrCreate(
    ['title' => 'sslcommerz_id'],
    ['value' => 'ecomm621c6cee01086', 'lang' => 'en']
);

Setting::updateOrCreate(
    ['title' => 'sslcommerz_password'],
    ['value' => 'ecomm621c6cee01086@ssl', 'lang' => 'en']
);

Setting::updateOrCreate(
    ['title' => 'is_sslcommerz_activated'],
    ['value' => 1, 'lang' => 'en']
);

Setting::updateOrCreate(
    ['title' => 'is_sslcommerz_sandbox_mode_activated'],
    ['value' => 1, 'lang' => 'en']  // 1 = Sandbox Mode ON
);

echo "Development mode configured!";
```

#### 4. Admin Panel Configuration (Development)

1. Login to Admin Dashboard
2. Go to **Settings** → **Payment Gateway**
3. Find **SSLCOMMERZ** section and set:
   - **Store ID**: `ecomm621c6cee01086`
   - **Store Password**: `ecomm621c6cee01086@ssl`
   - **Sandbox Mode**: **ON** (1)
   - **Active**: **ON** (1)

### Sandbox Test Credentials

Use these credentials for testing in sandbox mode:

| Payment Method | Test Credential |
|----------------|-----------------|
| **bKash** | `01770618583` |
| **DBBL Nexus** | `01610811517` |
| **Visa Card** | `4111111111111111` (any future expiry, CVV: 123) |
| **MasterCard** | `5555555555554444` (any future expiry, CVV: 123) |
| **AMEX** | `374545195704001` (any future expiry, CVV: 1234) |

### Development Verification

```bash
# Verify sandbox configuration
php artisan tinker

>>> echo "API Domain: " . config('sslcommerz.apiDomain');
// Expected: https://sandbox.sslcommerz.com

>>> echo "Sandbox Mode: " . (settingHelper('is_sslcommerz_sandbox_mode_activated') ? 'ON' : 'OFF');
// Expected: ON

>>> echo "Localhost: " . (config('sslcommerz.connect_from_localhost') ? 'YES' : 'NO');
// Expected: YES
```

### Local Development with ngrok

For testing callbacks in local development:

```bash
# Install ngrok: https://ngrok.com/download

# Start ngrok tunnel
ngrok http 8000

# Update .env with ngrok URL
PROJECT_PATH=https://abc123.ngrok.io
IS_LOCALHOST=false

# Update SSLCOMMERZ settings
Setting::where('title', 'is_sslcommerz_sandbox_mode_activated')->update(['value' => 1]);
```

---

## Live Production Mode

### Overview

Production mode uses SSLCOMMERZ's live gateway for processing real payments. **Ensure all testing is complete before switching to production.**

### Production Environment Setup

#### 1. `.env` File - Production Configuration

```env
# ============================================
# LIVE PRODUCTION MODE
# ============================================

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# SSLCOMMERZ Production Configuration
PROJECT_PATH=https://your-domain.com
IS_LOCALHOST=false

# ⚠️ PRODUCTION CREDENTIALS - Get from SSLCOMMERZ Dashboard
# IMPORTANT: Never share these credentials or commit to version control!
SSLCOMMERZ_STORE_ID=your_live_store_id_here
SSLCOMMERZ_STORE_PASSWORD=your_live_store_password_here
SSLCOMMERZ_ACTIVATED=1
SSLCOMMERZ_SANDBOX_MODE=0
```

#### 2. Config File - `config/sslcommerz.php` (Production)

```php
<?php

return [
    'projectPath' => env('PROJECT_PATH', 'https://your-domain.com'),

    // PRODUCTION API Domain
    'apiDomain' => "https://securepay.sslcommerz.com",

    // Production Credentials (from .env)
    'apiCredentials' => [
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    ],

    // API Endpoints
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],

    // Production Mode - Disable localhost
    'connect_from_localhost' => env("IS_LOCALHOST", false),

    // Callback URLs (must use HTTPS)
    'success_url' => '/',
    'failed_url' => config('services.ssl_commerze.FAIL_URL'),
    'cancel_url' => config('services.ssl_commerze.CANCEL_URL'),
    'ipn_url' => '/ipn',
];
```

#### 3. Database Settings - Production

```php
// php artisan tinker
use App\Models\Setting;

// ⚠️ PRODUCTION - Replace with your live credentials
Setting::updateOrCreate(
    ['title' => 'sslcommerz_id'],
    ['value' => env('SSLCOMMERZ_STORE_ID'), 'lang' => 'en']
);

Setting::updateOrCreate(
    ['title' => 'sslcommerz_password'],
    ['value' => env('SSLCOMMERZ_STORE_PASSWORD'), 'lang' => 'en']
);

Setting::updateOrCreate(
    ['title' => 'is_sslcommerz_activated'],
    ['value' => 1, 'lang' => 'en']
);

Setting::updateOrCreate(
    ['title' => 'is_sslcommerz_sandbox_mode_activated'],
    ['value' => 0, 'lang' => 'en']  // 0 = Sandbox Mode OFF (Live)
);

echo "Production mode configured!";
```

#### 4. Admin Panel Configuration (Production)

1. Login to Admin Dashboard
2. Go to **Settings** → **Payment Gateway**
3. Find **SSLCOMMERZ** section and set:
   - **Store ID**: Your live store ID from SSLCOMMERZ
   - **Store Password**: Your live store password from SSLCOMMERZ
   - **Sandbox Mode**: **OFF** (0)
   - **Active**: **ON** (1)

### Pre-Production Checklist

Before switching to live mode, ensure:

- [ ] SSL certificate is installed and valid
- [ ] All callback URLs are accessible via HTTPS
- [ ] Server IP is whitelisted with SSLCOMMERZ (if required)
- [ ] Live Store ID and Password obtained from SSLCOMMERZ
- [ ] All payment methods tested in sandbox
- [ ] Success/fail/cancel callbacks working
- [ ] IPN endpoint receiving notifications
- [ ] Database backups configured
- [ ] Error logging enabled
- [ ] `APP_DEBUG=false` in production

### Production Verification

```bash
# Verify production configuration
php artisan tinker

>>> echo "API Domain: " . config('sslcommerz.apiDomain');
// Expected: https://securepay.sslcommerz.com

>>> echo "Store ID: " . config('sslcommerz.apiCredentials.store_id');
// Expected: your_live_store_id

>>> echo "Sandbox Mode: " . (settingHelper('is_sslcommerz_sandbox_mode_activated') ? 'ON' : 'OFF');
// Expected: OFF

>>> echo "Localhost: " . (config('sslcommerz.connect_from_localhost') ? 'YES' : 'NO');
// Expected: NO

>>> echo "App Debug: " . (config('app.debug') ? 'ON' : 'OFF');
// Expected: OFF
```

### First Live Transaction

1. Start with a small amount (BDT 10-50)
2. Complete payment through SSLCOMMERZ gateway
3. Verify in SSLCOMMERZ merchant dashboard
4. Check database for payment status
5. Verify IPN was received
6. Confirm order completion

---

## Environment Comparison

| Setting | Development (Sandbox) | Production (Live) |
|---------|----------------------|-------------------|
| **API Domain** | `https://sandbox.sslcommerz.com` | `https://securepay.sslcommerz.com` |
| **Store ID** | `ecomm621c6cee01086` | Your live store ID |
| **Store Password** | `ecomm621c6cee01086@ssl` | Your live password |
| **Sandbox Mode** | `1` (enabled) | `0` (disabled) |
| **Localhost** | `true` | `false` |
| **APP_ENV** | `local` | `production` |
| **APP_DEBUG** | `true` | `false` |
| **URL Protocol** | HTTP (localhost) | HTTPS required |
| **SSL Verification** | Disabled | Enabled |
| **Real Money** | No (test mode) | Yes (real transactions) |

---

## Switching Between Environments

### From Development to Production

```bash
# 1. Update .env file
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
PROJECT_PATH=https://your-domain.com
IS_LOCALHOST=false
SSLCOMMERZ_STORE_ID=your_live_id
SSLCOMMERZ_STORE_PASSWORD=your_live_password
SSLCOMMERZ_SANDBOX_MODE=0

# 2. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 3. Update database via tinker
php artisan tinker
>>> Setting::where('title', 'is_sslcommerz_sandbox_mode_activated')->update(['value' => 0]);
>>> Setting::where('title', 'sslcommerz_id')->update(['value' => env('SSLCOMMERZ_STORE_ID')]);
>>> Setting::where('title', 'sslcommerz_password')->update(['value' => env('SSLCOMMERZ_STORE_PASSWORD')]);
```

### From Production to Development (Rollback)

```bash
# 1. Update .env file
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
PROJECT_PATH=http://localhost:8000
IS_LOCALHOST=true
SSLCOMMERZ_STORE_ID=ecomm621c6cee01086
SSLCOMMERZ_STORE_PASSWORD=ecomm621c6cee01086@ssl
SSLCOMMERZ_SANDBOX_MODE=1

# 2. Clear cache
php artisan cache:clear
php artisan config:clear

# 3. Update database via tinker
php artisan tinker
>>> Setting::where('title', 'is_sslcommerz_sandbox_mode_activated')->update(['value' => 1]);
>>> Setting::where('title', 'sslcommerz_id')->update(['value' => 'ecomm621c6cee01086']);
>>> Setting::where('title', 'sslcommerz_password')->update(['value' => 'ecomm621c6cee01086@ssl']);
```

---

## API Endpoints

### Available Endpoints

| Endpoint | Purpose |
|----------|---------|
| `/gwprocess/v4/api.php` | Initiate payment session |
| `/validator/api/merchantTransIDvalidationAPI.php` | Check transaction status |
| `/validator/api/validationserverAPI.php` | Validate order/IPN |

### Route Definition

```php
// routes/web.php
Route::match(['post', 'get'], 'get/ssl-response', [PaymentController::class, 'sslResponse'])->name('ssl.response');
```

---

## Integration Components

### Library Structure

```
app/Library/SslCommerz/
├── SslCommerzInterface.php          # Interface definition
├── AbstractSslCommerz.php           # Base abstract class
└── SslCommerzNotification.php       # Main implementation
```

### 1. SslCommerzInterface.php

Defines the contract for SSLCOMMERZ operations:

```php
<?php
namespace App\Library\SslCommerz;

interface SslCommerzInterface
{
    public function makePayment(array $data);
    public function orderValidate($requestData, $trxID, $amount, $currency);
    public function setParams($data);
    public function setRequiredInfo(array $data);
    public function setCustomerInfo(array $data);
    public function setShipmentInfo(array $data);
    public function setProductInfo(array $data);
    public function setAdditionalInfo(array $data);
    public function callToApi($data, $header = [], $setLocalhost = false);
}
```

### 2. AbstractSslCommerz.php

Provides core functionality:

- **API Communication**: cURL-based API calls with SSL verification control
- **Response Formatting**: JSON response processing
- **Redirection**: HTTP redirect helper

Key Methods:
```php
protected function callToApi($data, $header = [], $setLocalhost = false)
public function formatResponse($response, $type = 'checkout', $pattern = 'json')
public function redirect($url, $permanent = false)
```

### 3. SslCommerzNotification.php

Main implementation class with complete payment handling:

**Constructor**: Loads configuration and credentials
```php
public function __construct()
{
    $this->config = config('sslcommerz');
    $this->setStoreId($this->config['apiCredentials']['store_id']);
    $this->setStorePassword($this->config['apiCredentials']['store_password']);
}
```

---

## Payment Flow

### Step 1: Initiate Payment

**Controller Method**: `PaymentController@sslResponse`

```php
public function sslResponse(Request $request)
{
    // 1. Set API domain based on sandbox mode
    if (settingHelper('is_sslcommerz_sandbox_mode_activated') == 1) {
        config(['sslcommerz.apiDomain' => 'https://sandbox.sslcommerz.com']);
    } else {
        config(['sslcommerz.apiDomain' => 'https://securepay.sslcommerz.com']);
    }

    // 2. Prepare payment data
    $post_data = [
        'total_amount' => round($amount),
        'currency' => 'BDT',
        'tran_id' => date('YmdHis'),
        // ... customer info
        // ... shipping info
        // ... product info
    ];

    // 3. Set dynamic callback URLs
    config(['sslcommerz.success_url' => $this->successUrl($request, $user, $amount)]);
    config(['sslcommerz.cancel_url' => $this->cancelUrl($request)]);

    // 4. Set credentials
    config(['sslcommerz.apiCredentials.store_id' => settingHelper('sslcommerz_id')]);
    config(['sslcommerz.apiCredentials.store_password' => settingHelper('sslcommerz_password')]);

    // 5. Create payment and redirect
    $sslc = new SslCommerzNotification();
    $response = $sslc->makePayment($post_data);

    $data = json_decode($response);
    return redirect($data->data);
}
```

### Step 2: Customer Payment

1. Customer is redirected to SSLCOMMERZ gateway
2. Customer selects payment method
3. Customer completes payment

### Step 3: Payment Callback

After payment, SSLCOMMERZ redirects to:
- **Success URL**: When payment is successful
- **Failed URL**: When payment fails
- **Cancel URL**: When customer cancels

### Step 4: IPN (Instant Payment Notification)

SSLCOMMERZ sends server-to-server notification to `ipn_url` for reliable payment confirmation.

---

## Required Parameters

### Essential Payment Parameters

| Parameter | Type | Max Length | Description | Example |
|-----------|------|------------|-------------|---------|
| `total_amount` | decimal(10,2) | - | **Mandatory** - Transaction amount (BDT 10.00 - 500000.00) | `55.40` |
| `currency` | string(3) | 3 | **Mandatory** - Currency code | `BDT`, `USD`, `EUR` |
| `tran_id` | string(30) | 30 | **Mandatory** - Unique transaction ID | `20231215143022` |
| `product_category` | string(50) | 50 | **Mandatory** - Product category | `electronics`, `clothing` |
| `success_url` | string(255) | 255 | **Mandatory** - Success callback URL | `https://site.com/success` |
| `fail_url` | string(255) | 255 | **Mandatory** - Failure callback URL | `https://site.com/fail` |
| `cancel_url` | string(255) | 255 | **Mandatory** - Cancel callback URL | `https://site.com/cancel` |

### Customer Information (Required)

```php
$post_data['cus_name'] = 'Customer Name';
$post_data['cus_email'] = 'customer@email.com';
$post_data['cus_add1'] = 'Customer Address';
$post_data['cus_city'] = 'Dhaka';
$post_data['cus_postcode'] = '1000';
$post_data['cus_country'] = 'Bangladesh';
$post_data['cus_phone'] = '01700000000';
```

### Shipping Information (Required if shipping_method = YES)

```php
$post_data['shipping_method'] = 'NO'; // or 'YES' / 'Courier'
$post_data['num_of_item'] = 1;
$post_data['ship_name'] = 'Recipient Name';
$post_data['ship_add1'] = 'Shipping Address';
$post_data['ship_city'] = 'Dhaka';
$post_data['ship_country'] = 'Bangladesh';
```

### Product Information (Required)

```php
$post_data['product_name'] = 'Computer,Speaker';
$post_data['product_category'] = 'electronic';
$post_data['product_profile'] = 'physical-goods';
```

**Valid Product Profiles:**
- `general` - General purpose
- `physical-goods` - Tangible products
- `non-physical-goods` - Digital products/services
- `airline-tickets` - Flight bookings
- `travel-vertical` - Hotel bookings
- `telecom-vertical` - Mobile/utility recharge

---

## Optional Parameters

### IPN URL (Recommended)

```php
$post_data['ipn_url'] = 'https://yoursite.com/ipn';
```

**Why use IPN?**
- Reliable server-to-server notification
- Works even if customer loses connection
- Updates backend office automatically

### Gateway Control

```php
// Show specific gateways only
$post_data['multi_card_name'] = 'brac_visa,dbbl_visa,bkash';

// Group gateways
$post_data['multi_card_name'] = 'internetbank'; // All internet banking
$post_data['multi_card_name'] = 'mobilebank';   // All mobile banking
$post_data['multi_card_name'] = 'visacard';     // All Visa cards

// Allow specific card BINs
$post_data['allowed_bin'] = '371598,371599,376947';
```

### Additional Meta Data

```php
$post_data['value_a'] = 'Custom data 1'; // Pass order ID, user ID, etc.
$post_data['value_b'] = 'Custom data 2';
$post_data['value_c'] = 'Custom data 3';
$post_data['value_d'] = 'Custom data 4';
```

### Cart Details (JSON Format)

```php
$post_data['cart'] = json_encode([
    ["product" => "Product A", "quantity" => "1", "amount" => "200.00"],
    ["product" => "Product B", "quantity" => "2", "amount" => "150.00"]
]);
```

### Financial Breakdown

```php
$post_data['product_amount'] = '100.00';    // Base product price
$post_data['vat'] = '15.00';                 // VAT amount
$post_data['discount_amount'] = '10.00';    // Discount given
$post_data['convenience_fee'] = '5.00';     // Convenience fee
```

---

## EMI Configuration

Enable EMI (Equated Monthly Installment) for eligible transactions:

```php
// Enable EMI option
$post_data['emi_option'] = 1;

// Max installment options
$post_data['emi_max_inst_option'] = 3;  // Customer sees 3,6,9 month options

// Pre-select installment (no gateway selection)
$post_data['emi_selected_inst'] = 6;  // 6 months

// Allow EMI only (force EMI payment)
$post_data['emi_allow_only'] = 1;
```

---

## Security & Validation

### Order Validation Method

Validate transactions server-side:

```php
$sslc = new SslCommerzNotification();

// Validate payment
$isValid = $sslc->orderValidate(
    $_POST,           // SSLCOMMERZ POST data
    $tran_id,         // Your transaction ID
    $amount,          // Expected amount
    'BDT'             // Currency
);

if ($isValid) {
    // Payment is valid - process order
} else {
    // Payment validation failed
    echo $sslc->error;
}
```

### Hash Verification

The library automatically verifies SSLCOMMERZ hash signature:

```php
protected function SSLCOMMERZ_hash_verify($post_data, $store_passwd = "")
{
    // 1. Extract verify_key fields
    $pre_define_key = explode(',', $post_data['verify_key']);

    // 2. Build verification array
    $new_data = [];
    foreach ($pre_define_key as $value) {
        $new_data[$value] = $post_data[$value];
    }

    // 3. Add store password (MD5)
    $new_data['store_passwd'] = md5($store_passwd);

    // 4. Sort and create hash string
    ksort($new_data);
    $hash_string = "";
    foreach ($new_data as $key => $value) {
        $hash_string .= $key . '=' . ($value) . '&';
    }
    $hash_string = rtrim($hash_string, '&');

    // 5. Verify signature
    if (md5($hash_string) == $post_data['verify_sign']) {
        return true;
    } else {
        return false;
    }
}
```

---

## Testing

### Sandbox Test Cards

| Card Type | Card Number | Expiry | CVV |
|-----------|-------------|--------|-----|
| bKash | `01770618583` | - | - |
| DBBL Nexus | `01610811517` | - | - |
| Visa | `4111111111111111` | Any future date | `123` |
| MasterCard | `5555555555554444` | Any future date | `123` |
| AMEX | `374545195704001` | Any future date | `1234` |

### Testing Checklist

- [ ] Test with sandbox credentials
- [ ] Test success scenario
- [ ] Test failure scenario
- [ ] Test cancel scenario
- [ ] Verify IPN is received
- [ ] Test hash verification
- [ ] Validate amount calculation
- [ ] Test currency conversion
- [ ] Verify webhook handling

### Sandbox Credentials (Example)

```php
Store ID:    ecomm621c6cee01086
Store Password: ecomm621c6cee01086@ssl
API Domain:  https://sandbox.sslcommerz.com
```

---

## Going Live - Production Mode Setup

### Pre-Live Checklist

1. **Account Setup**
   - [ ] Complete merchant KYC verification
   - [ ] Get **LIVE** Store ID and Password from SSLCOMMERZ
   - [ ] Verify callback URLs are publicly accessible
   - [ ] Complete SSLCOMMERZ technical integration review

2. **Configuration**
   - [ ] Update `apiDomain` to production URL
   - [ ] Set `connect_from_localhost` to `false`
   - [ ] Update credentials in database/settings
   - [ ] Enable SSLCOMMERZ in admin panel
   - [ ] Set sandbox mode to OFF

3. **Security**
   - [ ] Enable HTTPS on production domain
   - [ ] Verify valid SSL certificate
   - [ ] Set proper file permissions (755 for folders, 644 for files)
   - [ ] Enable CSRF protection
   - [ ] Disable debug mode (`APP_DEBUG=false`)

4. **Testing**
   - [ ] Test with small amounts (BDT 10-50)
   - [ ] Verify all payment methods work
   - [ ] Check refund process
   - [ ] Test IPN reliability
   - [ ] Test success/fail/cancel callbacks

---

### Production Configuration

#### 1. Update `.env` File for Production

```env
# .env - Production Settings

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# SSLCOMMERZ Production Settings
PROJECT_PATH=https://your-domain.com
IS_LOCALHOST=false

# Production Credentials (Get from SSLCOMMERZ Dashboard)
SSLCOMMERZ_STORE_ID=your_live_store_id_here
SSLCOMMERZ_STORE_PASSWORD=your_live_store_password_here
SSLCOMMERZ_ACTIVATED=1
SSLCOMMERZ_SANDBOX_MODE=0

# Important: NEVER commit live credentials to version control!
# Add .env to .gitignore
```

#### 2. Update Config File (`config/sslcommerz.php`)

```php
<?php

return [
    'projectPath' => env('PROJECT_PATH', 'https://your-domain.com'),

    // Production API Domain
    'apiDomain' => "https://securepay.sslcommerz.com",

    // Production Credentials (loaded from .env)
    'apiCredentials' => [
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    ],

    // API Endpoints
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],

    // Production Mode - Disable localhost
    'connect_from_localhost' => env("IS_LOCALHOST", false),

    // Callback URLs (ensure these use HTTPS)
    'success_url' => '/',
    'failed_url' => config('services.ssl_commerze.FAIL_URL'),
    'cancel_url' => config('services.ssl_commerze.CANCEL_URL'),
    'ipn_url' => '/ipn',
];
```

#### 3. Update Database Settings

Run this SQL query or use tinker/seeders:

```sql
-- Update SSLCOMMERZ settings for production
UPDATE settings SET `value` = 'your_live_store_id' WHERE title = 'sslcommerz_id';
UPDATE settings SET `value` = 'your_live_store_password' WHERE title = 'sslcommerz_password';
UPDATE settings SET `value` = '1' WHERE title = 'is_sslcommerz_activated';
UPDATE settings SET `value` = '0' WHERE title = 'is_sslcommerz_sandbox_mode_activated';
```

Or using Laravel Tinker:

```php
// php artisan tinker
use App\Models\Setting;

Setting::where('title', 'sslcommerz_id')->update(['value' => 'your_live_store_id']);
Setting::where('title', 'sslcommerz_password')->update(['value' => 'your_live_store_password']);
Setting::where('title', 'is_sslcommerz_sandbox_mode_activated')->update(['value' => 0]);

echo "SSLCOMMERZ production settings updated!";
```

Or update in Admin Panel:
1. Login to Admin Dashboard
2. Go to **Settings** → **Payment Gateway**
3. Find **SSLCOMMERZ** section
4. Update:
   - **Store ID**: Your live store ID
   - **Store Password**: Your live store password
   - **Sandbox Mode**: Set to **OFF** (0)
   - **Active**: Set to **ON** (1)

#### 4. Update Settings Seeder for Future Deployments

```php
// database/seeders/Admin/SettingsSeeder.php

//SSLCOMMERZ Setting - Production ready with environment fallback
Setting::create([
    'title' => 'sslcommerz_id',
    'value' => env('SSLCOMMERZ_STORE_ID', ''),
    'lang' => 'en'
]);

Setting::create([
    'title' => 'sslcommerz_password',
    'value' => env('SSLCOMMERZ_STORE_PASSWORD', ''),
    'lang' => 'en'
]);

Setting::create([
    'title' => 'is_sslcommerz_activated',
    'value' => env('SSLCOMMERZ_ACTIVATED', 1),
    'lang' => 'en'
]);

Setting::create([
    'title' => 'is_sslcommerz_sandbox_mode_activated',
    'value' => env('SSLCOMMERZ_SANDBOX_MODE', 0),
    'lang' => 'en'
]);
```

---

### Environment Comparison

| Setting | Sandbox Mode | Production Mode |
|---------|--------------|-----------------|
| **API Domain** | `https://sandbox.sslcommerz.com` | `https://securepay.sslcommerz.com` |
| **Store ID** | Test credentials (provided by SSLCOMMERZ) | Live credentials (from your dashboard) |
| **Store Password** | Test password | Live password |
| **Sandbox Mode** | `1` (enabled) | `0` (disabled) |
| **Localhost** | `true` (SSL verification off) | `false` (SSL verification on) |
| **URLs** | Can use HTTP | **Must use HTTPS** |

---

### Production Verification

#### 1. Verify Configuration

After updating settings, verify the configuration:

```php
// php artisan tinker

// Check configuration
echo "API Domain: " . config('sslcommerz.apiDomain') . "\n";
echo "Store ID: " . config('sslcommerz.apiCredentials.store_id') . "\n";
echo "Sandbox Mode: " . (settingHelper('is_sslcommerz_sandbox_mode_activated') ? 'ON' : 'OFF') . "\n";
echo "Localhost: " . (config('sslcommerz.connect_from_localhost') ? 'YES' : 'NO') . "\n";
```

**Expected Output for Production:**
```
API Domain: https://securepay.sslcommerz.com
Store ID: your_live_store_id
Sandbox Mode: OFF
Localhost: NO
```

#### 2. Test Connection

```php
// Test API connection
$sslc = new \App\Library\SslCommerz\SslCommerzNotification();
echo "SSLCOMMERZ Library Loaded Successfully";
```

#### 3. Run Test Transaction

1. Create a small test order (BDT 10-50)
2. Complete payment through SSLCOMMERZ gateway
3. Verify success callback is received
4. Check database for payment status
5. Verify IPN is processed

---

### Production Callback URLs

Ensure all callback URLs use HTTPS and are publicly accessible:

```php
// In PaymentController@sslResponse
$this->successUrl($request, $user, $amount);  // Must return HTTPS URL
$this->cancelUrl($request);                    // Must return HTTPS URL

// IPN URL
Route::post('/ipn', [PaymentController::class, 'ipnHandler'])->name('ipn.sslcommerz');
```

**Example Production URLs:**
- Success URL: `https://yourdomain.com/user/complete-order?payment_type=ssl_commerze`
- Fail URL: `https://yourdomain.com/payment?error=payment_failed`
- Cancel URL: `https://yourdomain.com/payment?error=payment_cancelled`
- IPN URL: `https://yourdomain.com/ipn`

---

### Security Best Practices for Production

#### 1. Never Hardcode Credentials

```php
// ❌ BAD - Hardcoded in code
Setting::create(['title' => 'sslcommerz_id', 'value' => 'ecomm_live_12345']);

// ✅ GOOD - From environment
Setting::create(['title' => 'sslcommerz_id', 'value' => env('SSLCOMMERZ_STORE_ID')]);
```

#### 2. Use Environment Variables

```env
# .env - Add to .gitignore!
SSLCOMMERZ_STORE_ID=live_xxxxx
SSLCOMMERZ_STORE_PASSWORD=live_password_xxxxx
SSLCOMMERZ_SANDBOX_MODE=0
```

#### 3. Protect .env File

```bash
# .gitignore
/.env
/.env.production
/.env.local
```

#### 4. Enable HTTPS Only

```php
// Force HTTPS in production
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

#### 5. Monitor Transactions

- Enable logging for all payment transactions
- Set up alerts for failed payments
- Monitor SSLCOMMERZ dashboard regularly
- Keep records of all transaction IDs

---

### Production Troubleshooting

#### Issue 1: Payments not reaching SSLCOMMERZ

**Check:**
```bash
# Verify API domain is correct
php artisan tinker
>>> config('sslcommerz.apiDomain');
// Should return: https://securepay.sslcommerz.com
```

#### Issue 2: Callback URLs not working

**Check:**
- Callback URLs must be publicly accessible (not localhost)
- Must use HTTPS (SSL certificate required)
- Firewall should allow incoming requests
- Test URL accessibility: `curl -I https://yourdomain.com/ipn`

#### Issue 3: Hash validation failing

**Check:**
- Store password matches exactly (case-sensitive)
- All parameters included in hash calculation
- Transaction ID matches what was sent

---

### Live Rollback Procedure

If you need to rollback to sandbox mode:

```sql
-- Switch back to sandbox mode
UPDATE settings SET `value` = '1' WHERE title = 'is_sslcommerz_sandbox_mode_activated';

-- Or using tinker
Setting::where('title', 'is_sslcommerz_sandbox_mode_activated')->update(['value' => 1]);
```

```env
# .env - Switch to sandbox
SSLCOMMERZ_SANDBOX_MODE=1
IS_LOCALHOST=true
```

---

### Post-Live Monitoring

After going live, monitor these metrics:

1. **Transaction Success Rate**
   - Target: >95% success rate
   - Alert if: <90% for 1 hour

2. **API Response Time**
   - Target: <3 seconds
   - Alert if: >5 seconds consistently

3. **IPN Delivery**
   - All transactions should trigger IPN
   - Monitor for missing notifications

4. **Error Logs**
   - Check `storage/logs/laravel.log` for errors
   - Set up log monitoring

---

### Contact SSLCOMMERZ Support

**For Production Issues:**
- **Email**: support@sslcommerz.com
- **Phone**: +880 1722 722 722
- **Merchant Panel**: https://merchant.sslcommerz.com
- **Technical Docs**: https://developer.sslcommerz.com/

**When to Contact:**
- Live credentials not working
- High transaction failure rate
- IPN not being delivered
- Need urgent technical assistance

---

## Troubleshooting

### Common Issues

#### 1. "FAILED TO CONNECT WITH SSLCOMMERZ API"

**Cause**: cURL error or network issue

**Solution**:
```php
// Check if localhost development
'connect_from_localhost' => true,  // Disable SSL verification for localhost

// Or check firewall/cURL settings
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
```

#### 2. "Hash validation failed"

**Cause**: Signature mismatch

**Solution**:
- Verify store password is correct
- Check all parameters are included in hash
- Ensure parameter order matches

#### 3. Invalid Transaction ID

**Cause**: Duplicate or invalid tran_id

**Solution**:
```php
// Always use unique transaction ID
'tran_id' => date('YmdHis') . '_' . rand(1000, 9999);
// or use order ID
'tran_id' => 'ORD_' . $order->id;
```

#### 4. "Data has been tempered"

**Cause**: Amount or transaction ID mismatch

**Solution**:
- Verify amount calculation
- Check currency conversion
- Ensure transaction ID matches exactly

#### 5. Callback Not Working

**Cause**: URL issues or server blocking

**Solution**:
- Verify callback URLs are accessible from internet
- Check server firewall allows SSLCOMMERZ IP
- Use HTTPS URLs
- Test with ngrok for local development

### Debug Mode

Enable detailed logging:

```php
// In controller
\Log::info('SSLCOMMERZ Request:', $post_data);
\Log::info('SSLCOMMERZ Response:', $response);

// Check Laravel logs
// storage/logs/laravel.log
```

### Getting Help

- **SSLCOMMERZ Support**: support@sslcommerz.com
- **Merchant Panel**: https://merchant.sslcommerz.com
- **Documentation**: https://developer.sslcommerz.com/

---

## Payment Method Reference

### Supported Banks & Wallets

**Cards:**
- BRAC VISA, Dutch-Bangla VISA, City VISA, EBL VISA, Southeast VISA
- BRAC MasterCard, DBBL MasterCard, City MasterCard, EBL MasterCard
- City AMEX

**Mobile Banking:**
- bKash, Nagad, Rocket, DBBL Nexus, Tap N Pay

**Internet Banking:**
- Bank Asia, AB Bank, MTB, City Touch, IBBL, Upay

---

## File Reference

### Project Files

| File | Purpose |
|------|---------|
| `config/sslcommerz.php` | Main configuration file |
| `app/Library/SslCommerz/AbstractSslCommerz.php` | Base class with API methods |
| `app/Library/SslCommerz/SslCommerzInterface.php` | Interface definition |
| `app/Library/SslCommerz/SslCommerzNotification.php` | Payment implementation |
| `app/Http/Controllers/Site/PaymentController.php` | Payment controller (sslResponse method) |
| `routes/web.php` | Route definition (line 298) |
| `database/seeders/Admin/SettingsSeeder.php` | Default settings (lines 249-253) |

### Assets

| Asset | Location |
|-------|----------|
| SSLCOMMERZ Logo | `images/payment-method/sslcommerze.svg` |
| `public/images/payment-method/sslcommerze.svg` | Public asset |

---

## Example: Complete Payment Request

```php
$post_data = array();
$post_data['total_amount'] = "500.00";
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "ORD_20231215153022";

# CUSTOMER INFO
$post_data['cus_name'] = "John Doe";
$post_data['cus_email'] = "john@example.com";
$post_data['cus_add1'] = "House 123, Road 10";
$post_data['cus_add2'] = "Dhanmondi";
$post_data['cus_city'] = "Dhaka";
$post_data['cus_state'] = "Dhaka";
$post_data['cus_postcode'] = "1205";
$post_data['cus_country'] = "Bangladesh";
$post_data['cus_phone'] = "01700000000";
$post_data['cus_fax'] = "";

# SHIPPING INFO
$post_data['ship_name'] = "John Doe";
$post_data['ship_add1'] = "House 123, Road 10";
$post_data['ship_city'] = "Dhaka";
$post_data['ship_state'] = "Dhaka";
$post_data['ship_postcode'] = "1205";
$post_data['ship_country'] = "Bangladesh";
$post_data['shipping_method'] = "YES";
$post_data['num_of_item'] = "2";

# PRODUCT INFO
$post_data['product_name'] = "Laptop,Mouse";
$post_data['product_category'] = "electronics";
$post_data['product_profile'] = "physical-goods";

# URLs (auto-set in controller)
$post_data['success_url'] = route('payment.success');
$post_data['fail_url'] = route('payment.fail');
$post_data['cancel_url'] = route('payment.cancel');
$post_data['ipn_url'] = route('ipn.sslcommerz');

# EMI (optional)
$post_data['emi_option'] = 1;
$post_data['emi_max_inst_option'] = 3;

# Create payment
$sslc = new SslCommerzNotification();
$response = $sslc->makePayment($post_data);
$data = json_decode($response);

if ($data->status == 'success') {
    return redirect($data->data);
}
```

---

## License & Disclaimer

This integration guide is for the Dolbear E-commerce Project. SSLCOMMERZ is a registered trademark of SSL Wireless.

**Last Updated**: January 2026
**Version**: 1.0