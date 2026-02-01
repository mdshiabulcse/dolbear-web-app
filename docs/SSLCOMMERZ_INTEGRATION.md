# SSLCOMMERZ Payment Gateway Integration

## Overview
This document provides complete documentation for the SSLCOMMERZ payment gateway integration in the Dolbear E-commerce Application.

## Table of Contents
1. [Features](#features)
2. [Architecture](#architecture)
3. [Configuration](#configuration)
4. [Payment Flow](#payment-flow)
5. [API Endpoints](#api-endpoints)
6. [Files Modified](#files-modified)
7. [Setup Instructions](#setup-instructions)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

---

## Features

- **Hosted Payment Solution**: Redirects customers to SSLCOMMERZ secure payment page
- **Sandbox & Production Mode**: Easy switching between test and live environments
- **Dynamic Callback URLs**: Supports query parameters in return URLs
- **IPN Support**: Server-to-server payment notifications
- **Transaction Validation**: Validates payments with SSLCOMMERZ API
- **Order Completion**: Automatic order status updates upon successful payment
- **Multi-Currency Support**: Automatic conversion to BDT
- **Duplicate Prevention**: Prevents double processing of payments

---

## Architecture

### Component Diagram

```
┌─────────────────┐
│  cart_new.vue   │ (Frontend - Checkout Page)
└────────┬────────┘
         │ 1. User clicks "Confirm Order"
         │    with "Online Payment" selected
         ▼
┌─────────────────────────────────┐
│  OrderController::confirmOrder  │ (Creates Order)
└────────┬────────────────────────┘
         │ 2. Returns success with trx_id
         ▼
┌─────────────────────────────────┐
│   cart_new.vue (redirects)      │
└────────┬────────────────────────┘
         │ 3. Redirects to /pay?trx_id=xxx
         ▼
┌─────────────────────────────────┐
│ SslCommerzPaymentController::   │
│ index()                          │ (Initiates Payment)
└────────┬────────────────────────┘
         │ 4. Calls SSLCOMMERZ API
         ▼
┌─────────────────────────────────┐
│   SSLCOMMERZ Gateway            │ (Payment Page)
└────────┬────────────────────────┘
         │ 5. User completes payment
         ▼
┌─────────────────────────────────┐
│   Success/Fail/Cancel Callbacks │
│   IPN (Server-to-Server)        │
└─────────────────────────────────┘
```

### Data Flow

```
Order Creation → Payment Initiation → Gateway Redirect → Payment → Callback → Order Completion
```

---

## Configuration

### 1. Environment Variables (.env)

Add these variables to your `.env` file:

```env
# SSLCOMMERZ Configuration
SSLCZ_TESTMODE=true                    # true=sandbox, false=production
SSLCZ_STORE_ID=your_test_store_id      # SSLCOMMERZ Store ID
SSLCZ_STORE_PASSWORD=your_test_store_pass  # SSLCOMMERZ Store Password
IS_LOCALHOST=true                      # true for development
```

### 2. Database Settings

Add SSLCOMMERZ credentials in the **Settings Management** section of your admin panel:

- **Store ID**: Your SSLCOMMERZ store ID
- **Store Password**: Your SSLCOMMERZ store password (NOT panel password)
- **Sandbox Mode**: Enable/Disable sandbox mode

### 3. Configuration File

The configuration is stored in `config/sslcommerz.php`:

```php
<?php

$apiDomain = env('SSLCZ_TESTMODE')
    ? "https://sandbox.sslcommerz.com"
    : "https://securepay.sslcommerz.com";

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

---

## Payment Flow

### Step-by-Step Process

#### 1. Order Confirmation
**Endpoint**: `POST /user/confirm-order`

**Request**:
```javascript
{
  payment_method: 'online_payment',
  // ... other checkout data
}
```

**Response**:
```json
{
  "success": true
}
```

#### 2. Payment Initiation
**Endpoint**: `GET /pay?trx_id={transaction_id}`

**Process**:
1. Retrieve order by transaction ID
2. Calculate amount in BDT
3. Prepare payment data
4. Call SSLCOMMERZ API
5. Redirect to payment gateway

#### 3. Payment Gateway (SSLCOMMERZ)
- User enters payment details
- SSLCOMMERZ processes payment
- User is redirected back to callback URL

#### 4. Callback Processing
**Success**: `/success?tran_id={xxx}&val_id={xxx}&amount={xxx}&currency=BDT`
**Fail**: `/fail?tran_id={xxx}`
**Cancel**: `/cancel?tran_id={xxx}`
**IPN**: `/ipn` (POST with all payment data)

---

## API Endpoints

### Payment Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | `/example1` | `exampleEasyCheckout` | Demo: Easy checkout |
| GET | `/example2` | `exampleHostedCheckout` | Demo: Hosted checkout |
| ANY | `/pay` | `index` | **Initiate payment** |
| POST | `/pay-via-ajax` | `payViaAjax` | Alternative payment method |
| POST | `/success` | `success` | **Success callback** |
| POST | `/fail` | `fail` | Fail callback |
| POST | `/cancel` | `cancel` | Cancel callback |
| POST | `/ipn` | `ipn` | **IPN handler** |

### Important: Route Placement
These routes must be placed **BEFORE** catch-all routes in `routes/web.php`:

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

## Files Modified

### 1. Controllers

**File**: `app/Http/Controllers/SslCommerzPaymentController.php`

**Key Methods**:
- `index(Request $request)` - Payment initiation (lines 38-229)
- `success(Request $request)` - Success callback (lines 246-372)
- `fail(Request $request)` - Fail callback (lines 380-419)
- `cancel(Request $request)` - Cancel callback (lines 427-436)
- `ipn(Request $request)` - IPN handler (lines 446-592)

**Helper Methods**:
- `getCurrency()` - Get BDT currency
- `activeCurrencyCheck()` - Get active currency
- `amountCalculator()` - Convert amount to BDT
- `getCustomerInfo()` - Extract customer details

### 2. SSLCOMMERZ Library

**File**: `app/Library/SslCommerz/SslCommerzNotification.php`

**Modification**: Updated `setRequiredInfo()` method (lines 291-334)
- Now accepts dynamic callback URLs from `$info` array
- Falls back to config values if not provided
- Allows URLs with query parameters

**Before**:
```php
$this->setSuccessUrl();
$this->data['success_url'] = $this->getSuccessUrl();
```

**After**:
```php
if (!empty($info['success_url'])) {
    $this->data['success_url'] = $info['success_url'];
} else {
    $this->setSuccessUrl();
    $this->data['success_url'] = $this->getSuccessUrl();
}
```

### 3. Routes

**File**: `routes/web.php`

**Location**: Lines 182-191 (before catch-all routes)

### 4. Frontend

**File**: `resources/js/components/frontend/pages/cart_new.vue`

**Location**: Lines 1058-1087

**Code**:
```javascript
if (this.payment_form.payment_method === 'online_payment') {
  // For online payment: redirect to SSLCOMMERZ payment initiation
  window.location.href = this.getUrl(
      "pay?trx_id=" + this.trx_id
  );
  return;
}
```

---

## Setup Instructions

### Step 1: Get SSLCOMMERZ Account

1. Visit [SSLCOMMERZ](https://sslcommerz.com/)
2. Register for a merchant account
3. Complete KYC verification
4. Get your Store ID and Store Password from the dashboard

### Step 2: Configure Environment

Edit `.env` file:

```env
# For Testing (Sandbox)
SSLCZ_TESTMODE=true
SSLCZ_STORE_ID=test_box_id
SSLCZ_STORE_PASSWORD=test_password
IS_LOCALHOST=true

# For Production
SSLCZ_TESTMODE=false
SSLCZ_STORE_ID=live_box_id
SSLCZ_STORE_PASSWORD=live_password
IS_LOCALHOST=false
```

### Step 3: Configure Database Settings

1. Login to Admin Panel
2. Go to **Settings** > **Payment Settings**
3. Find **SSLCOMMERZ** section
4. Enter:
   - Store ID
   - Store Password (API password, NOT panel password)
   - Enable/Disable Sandbox Mode

### Step 4: Verify Configuration

Check the configuration file at `config/sslcommerz.php` matches your requirements.

### Step 5: Test Payment Flow

1. Add products to cart
2. Proceed to checkout
3. Select **"Online Payment"**
4. Click **"Confirm Order"**
5. You should be redirected to SSLCOMMERZ gateway

---

## Testing

### Sandbox Test Cards

SSLCOMMERZ provides test cards for sandbox testing:

| Card Type | Card Number | Expiry | CVV | Result |
|-----------|-------------|--------|-----|--------|
| Visa | 4111111111111111 | 12/25 | 123 | Success |
| Visa | 4000056655665556 | 12/25 | 123 | Fail |
| MasterCard | 5555555555554444 | 12/25 | 123 | Success |

### Test Checklist

- [ ] Order creation with online payment
- [ ] Redirect to SSLCOMMERZ gateway
- [ ] Successful payment
- [ ] Order completion
- [ ] Invoice generation
- [ ] Failed payment handling
- [ ] Cancelled payment handling
- [ ] IPN processing
- [ ] Currency conversion (BDT)
- [ ] Duplicate payment prevention

---

## Troubleshooting

### Common Issues

#### 1. "500 Server Error - Something went wrong"

**Cause**: PHP opcache serving old file version

**Solution**:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart web server
```

#### 2. "404 Page Not Found" on /pay

**Cause**: SSLCOMMERZ routes placed after catch-all routes

**Solution**: Move SSLCOMMERZ routes BEFORE catch-all routes in `routes/web.php`

#### 3. "Column not found: is_default"

**Cause**: Old currency query method

**Solution**: Already fixed in `activeCurrencyCheck()` method

#### 4. "Store Credential Error"

**Cause**: Using panel password instead of API password

**Solution**:
1. Login to SSLCOMMERZ dashboard
2. Go to **Developer** > **Integration**
3. Copy **Store ID** and **Store Password** from there
4. DO NOT use your login panel password

#### 5. Payment not redirecting

**Checklist**:
- [ ] Verify `SSLCZ_STORE_ID` is set
- [ ] Verify `SSLCZ_STORE_PASSWORD` is set
- [ ] Check Laravel logs: `storage/logs/laravel-*.log`
- [ ] Verify route: `php artisan route:list | grep pay`
- [ ] Check currency: BDT must exist in currencies table

### Debug Mode

Enable detailed logging:

```php
// In SslCommerzPaymentController.php
Log::info('SSLCOMMERZ Debug: ', [
    'trx_id' => $trx_id,
    'amount' => $total_amount,
    // ... more data
]);
```

Check logs:
```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## Security Considerations

1. **Validation**: Always validate payment with SSLCOMMERZ API
2. **IPN**: Use IPN for reliable payment updates
3. **Duplicates**: Check `is_completed` status before processing
4. **Amount Verification**: Verify amount matches before completing order
5. **Webhooks**: Validate IPN requests from SSLCOMMERZ servers
6. **Credentials**: Never commit credentials to version control

---

## Currency Handling

### Supported Currencies

SSLCOMMERZ accepts multiple currencies but processes in BDT:
- BDT (Bangladeshi Taka) - Direct processing
- USD, EUR, SGD, etc. - Converted to BDT

### Conversion Logic

```php
// Amount conversion in controller
$active_currency = $this->activeCurrencyCheck();  // User's selected currency
$bdt_currency = $this->getCurrency();             // BDT currency

$amount_result = $this->amountCalculator(
    $orders,
    $data,
    $active_currency,  // From currency
    $bdt_currency      // To currency
);
```

---

## IPN (Instant Payment Notification)

### Purpose
IPN provides reliable server-to-server payment notifications, ensuring orders are completed even if:
- User closes browser after payment
- Network interruption occurs
- User session is lost

### IPN Flow

```
SSLCOMMERZ Server → POST /ipn → Validate Transaction → Complete Order → Return JSON
```

### IPN Data Received

```php
[
    'tran_id' => 'transaction_id',
    'val_id' => 'validation_id',
    'amount' => '100.00',
    'currency' => 'BDT',
    'status' => 'VALID',
    'card_type' => 'VISA',
    'value_a' => 'order_code',  // Our custom data
    // ... more fields
]
```

---

## Order Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 0 | Pending | Order created, payment pending |
| 1 | Completed | Payment successful, order complete |
| -1 | Failed | Payment failed |

---

## Reference: SSLCOMMERZ API Documentation

- **Official Docs**: https://developer.sslcommerz.com/
- **Sandbox**: https://sandbox.sslcommerz.com/
- **Production**: https://securepay.sslcommerz.com/
- **Dashboard**: https://merchant.sslcommerz.com/

---

## Support & Maintenance

### Logs Location
```
storage/logs/laravel-YYYY-MM-DD.log
```

### Key Log Messages
- `SSLCOMMERZ Payment: Initiated` - Payment started
- `SSLCOMMERZ Payment: Redirecting to gateway` - Redirect success
- `SSLCOMMERZ Success: Order completed` - Payment successful
- `SSLCOMMERZ IPN: Order completed` - IPN processed

### Performance Optimization
- Enable Laravel config cache: `php artisan config:cache`
- Enable route cache: `php artisan route:cache`
- Use queue for IPN processing (optional enhancement)

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-02-01 | Initial integration with dynamic callback URLs |

---

## Appendix: Complete Payment Data Structure

### Request to SSLCOMMERZ

```php
$post_data = [
    // Required
    'total_amount' => '100.00',
    'currency' => 'BDT',
    'tran_id' => 'unique_transaction_id',
    'product_category' => 'E-commerce',

    // Callback URLs (Dynamic)
    'success_url' => 'http://yoursite.com/success?trx_id=xxx&code=xxx',
    'fail_url' => 'http://yoursite.com/fail?trx_id=xxx',
    'cancel_url' => 'http://yoursite.com/cancel?trx_id=xxx',
    'ipn_url' => 'http://yoursite.com/ipn',

    // Customer Info
    'cus_name' => 'Customer Name',
    'cus_email' => 'customer@email.com',
    'cus_phone' => '01700000000',
    'cus_add1' => 'Address Line 1',
    'cus_city' => 'City',
    'cus_country' => 'Bangladesh',

    // Shipping Info
    'ship_name' => 'Customer Name',
    'ship_add1' => 'Address Line 1',
    'ship_city' => 'City',
    'ship_country' => 'Bangladesh',
    'shipping_method' => 'YES',
    'num_of_item' => 2,

    // Product Info
    'product_name' => 'Order Payment',
    'product_category' => 'E-commerce',
    'product_profile' => 'physical-goods',

    // Custom Parameters (Returned in callbacks)
    'value_a' => 'order_code',  // Order code
    'value_b' => '',            // Reserved
    'value_c' => '',            // Reserved
    'value_d' => '',            // Reserved
];
```

### Response from SSLCOMMERZ

```json
{
    "GatewayPageURL": "https://securepay.sslcommerz.com/...",
    "status": "SUCCESS",
    "failedreason": "",
    "storeLogo": "https://...logo.png"
}
```

---

*Document Version: 1.0*
*Last Updated: February 1, 2026*
*Maintained By: Development Team*