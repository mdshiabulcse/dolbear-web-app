# SSLCOMMERZ Sandbox Testing Guide

## Complete Guide for Testing Payment Integration in Sandbox Mode

**Estimated Time**: 45 minutes
**Difficulty**: Beginner
**Prerequisites**: SSLCOMMERZ sandbox account, local development environment

---

## Table of Contents
1. [Sandbox Account Setup](#sandbox-account-setup)
2. [Configure for Sandbox Mode](#configure-for-sandbox-mode)
3. [Sandbox Test Cards](#sandbox-test-cards)
4. [Step-by-Step Testing Process](#step-by-step-testing-process)
5. [Verifying Payment Callbacks](#verifying-payment-callbacks)
6. [Common Sandbox Issues](#common-sandbox-issues)
7. [Testing Checklist](#testing-checklist)
8. [Moving from Sandbox to Production](#moving-from-sandbox-to-production)

---

## Sandbox Account Setup

### 1. Create SSLCOMMERZ Sandbox Account

1. **Visit SSLCOMMERZ Website**
   - Go to: https://sslcommerz.com/
   - Click "Sign Up" or "Register"

2. **Choose Sandbox Option**
   - Select "Individual" or "Company" account
   - Use email: `your-email@example.com`
   - Use password: Create strong password

3. **Complete Registration**
   - Fill in required information
   - Verify email address
   - Login to dashboard

### 2. Access Sandbox Dashboard

1. **Login to Dashboard**
   - URL: https://sandbox.sslcommerz.com/
   - OR: https://merchant.sslcommerz.com/ (switch to sandbox mode)

2. **Navigate to Developer Section**
   - Go to **Developer** > **Integration**
   - This will show your sandbox credentials

### 3. Get Sandbox Credentials

You will find:
- **Store ID**: Something like `testbox123456789@sslcommerz`
- **Store Password**: Something like `test123` (NOT your login password)
- **API Domain**: `https://sandbox.sslcommerz.com`

**Example Sandbox Credentials**:
```
Store ID: test_live ABC123@gmail.com
Store Password: ABC123@4321
API Domain: https://sandbox.sslcommerz.com
```

---

## Configure for Sandbox Mode

### Option 1: Using Database Settings (Recommended for Admin Panel)

1. **Login to Your Application**
   - Go to: `http://localhost:8000/admin`
   - Login with admin credentials

2. **Navigate to Settings**
   - Go to **Settings** > **Payment Settings**
   - Find **SSLCOMMERZ** section

3. **Configure Sandbox Mode**
   - **Store ID**: Enter your sandbox Store ID
   - **Store Password**: Enter your sandbox Store Password
   - **Sandbox Mode**: Enable/Turn ON

4. **Save Settings**

### Option 2: Using .env File (For Developers)

**File**: `.env`

**Add or Update these lines**:
```env
# SSLCOMMERZ Sandbox Configuration
SSLCZ_TESTMODE=true
SSLCZ_STORE_ID=test_live ABC123@gmail.com
SSLCZ_STORE_PASSWORD=ABC123@4321
IS_LOCALHOST=true

# Security Settings for Development
SSLCOMMERZ_IP_WHITELIST_ENABLED=false
SSLCOMMERZ_ALLOW_LOCAL_IP=true
```

**Important Notes**:
- `SSLCZ_TESTMODE=true` enables sandbox mode
- `IS_LOCALHOST=true` allows local testing without SSL certificate
- Store ID and Password should be from sandbox dashboard (NOT production)

---

## Sandbox Test Cards

SSLCOMMERZ provides specific test cards for sandbox testing:

### ✅ Test Cards for SUCCESS Transactions

| Card Type | Card Number | Expiry | CVV | Card Holder Name | Result |
|-----------|-------------|--------|-----|------------------|--------|
| Visa | 4111111111111111 | Any future date | Any 3 digits | Any name | SUCCESS |
| MasterCard | 5555555555554444 | 12/25 or later | 123 | Test User | SUCCESS |
| American Express | 374245455400126 | 12/25 or later | 1234 | Test User | SUCCESS |

### ❌ Test Cards for FAILED Transactions

| Card Type | Card Number | Expiry | CVC | Card Holder Name | Result |
|-----------|-------------|--------|-----|------------------|--------|
| Visa | 4000056655665556 | Any future date | Any 3 digits | Any name | FAILED |
| Visa | 4005006655665556 | Any future date | Any 3 digits | Any name | FAILED |
| MasterCard | 5105105105105100 | Any future date | Any 3 digits | Any name | FAILED |

---

## Step-by-Step Testing Process

### Step 1: Create Test User Account

1. **Register New User**
   - Go to: `http://localhost:8000/register`
   - Email: `testuser@example.com`
   - Password: `Test@123456`

2. **Login to Account**
   - Go to: `http://localhost:8000/login`
   - Enter credentials
   - Click "Login"

---

### Step 2: Add Products to Cart

1. **Browse Products**
   - Go to: `http://localhost:8000/shop`
   - Select a product
   - Click "Add to Cart"

2. **View Cart**
   - Go to: `http://localhost:8000/cart`
   - Verify product is in cart

---

### Step 3: Proceed to Checkout

1. **Click Checkout**
   - From cart page, click "Checkout"

2. **Fill Shipping Information**
   - Name: `Test User`
   - Email: `testuser@example.com`
   - Phone: `01700000000`
   - Address: `123 Test Street, Dhaka`
   - City: `Dhaka`
   - Country: `Bangladesh`

---

### Step 4: Select Payment Method

1. **Choose Payment Method**
   - Select "Online Payment (SSLCOMMERZ)"

2. **Click "Confirm Order"**
   - This creates order and redirects to `/pay`

---

### Step 5: SSLCOMMERZ Payment Page

You should now be on SSLCOMMERZ's hosted payment page.

**Complete Payment**:
1. Select "Card Payment"
2. Use test card: `4111111111111111`
3. Expiry: `12/25`
4. CVV: `123`
5. Click "Pay Now"

---

### Step 6: Verify Success

1. **Wait for success message**
2. **Auto-redirect back to your site**
3. **Order should be completed**
4. **Invoice accessible**

---

## Common Sandbox Issues

### Issue 1: "Store Credential Error"

**Cause**: Using login password instead of API password

**Solution**: Use credentials from **Developer > Integration** page

### Issue 2: "Failed to Connect with SSLCOMMERZ API"

**Cause**: SSL certificate issues

**Solution**: Set `IS_LOCALHOST=true` in `.env`

### Issue 3: "Currency Not Supported"

**Cause**: BDT currency not configured

**Solution**: Create BDT currency in database

---

## Testing Checklist

- [ ] Sandbox account created
- [ ] Credentials configured
- [ ] BDT currency exists
- [ ] Can add products to cart
- [ ] Can proceed to checkout
- [ ] Can select online payment
- [ ] Redirected to SSLCOMMERZ
- [ ] Payment successful with test card
- [ ] Redirected back to site
- [ ] Order completed
- [ ] Invoice accessible

---

## Quick Test Commands

```bash
# Check configuration
php artisan tinker --execute="
\$storeId = env('SSLCZ_STORE_ID');
\$isTest = env('SSLCZ_TESTMODE');
echo 'Store ID: ' . \$storeId . PHP_EOL;
echo 'Sandbox Mode: ' . (\$isTest ? 'YES' : 'NO') . PHP_EOL;
"

# Check BDT currency
php artisan tinker --execute="
\$bdt = \App\Models\Currency::where('code', 'BDT')->first();
echo 'BDT: ' . (\$bdt ? 'FOUND' : 'NOT FOUND') . PHP_EOL;
"

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

**Testing Guide Version**: 1.0
**Last Updated**: February 1, 2026
