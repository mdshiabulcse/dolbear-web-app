# SSLCOMMERZ Integration - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         FRONTEND (Vue.js)                                   │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                         cart_new.vue                                  │   │
│  │  ┌─────────────────────────────────────────────────────────────┐     │   │
│  │  │  Payment Method Selection:                                   │     │   │
│  │  │  ○ Cash on Delivery                                         │     │   │
│  │  │  ● Online Payment (SSLCOMMERZ)                              │     │   │
│  │  └─────────────────────────────────────────────────────────────┘     │   │
│  │                                                                       │   │
│  │  [Confirm Order Button]                                              │   │
│  └───────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ 1. POST /user/confirm-order
                                    │    {payment_method: 'online_payment'}
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         BACKEND (Laravel)                                   │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                    OrderController::confirmOrder()                   │   │
│  │  - Creates order with trx_id                                        │   │
│  │  - Returns: {"success": true}                                       │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                    │                                         │
│                                    │ 2. Response                             │
│                                    ▼                                         │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                      cart_new.vue (JavaScript)                       │   │
│  │  window.location.href = "/pay?trx_id=" + trx_id;                     │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ 3. GET /pay?trx_id=xxx
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                   SslCommerzPaymentController::index()                      │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  1. Get order by trx_id                                            │    │
│  │  2. Calculate amount in BDT                                        │    │
│  │  3. Prepare payment data:                                          │    │
│  │     - Customer info                                                │    │
│  │     - Shipping info                                                │    │
│  │     - Callback URLs:                                               │    │
│  │       * success_url = /success?trx_id=xxx&code=xxx                 │    │
│  │       * fail_url = /fail?trx_id=xxx                                │    │
│  │       * cancel_url = /cancel?trx_id=xxx                            │    │
│  │       * ipn_url = /ipn                                             │    │
│  │  4. Call SSLCOMMERZ API                                            │    │
│  │  5. Get Gateway URL                                                │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                    │                                         │
│                                    │ 4. Redirect                            │
│                                    ▼                                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ 5. User Redirect
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                        SSLCOMMERZ PAYMENT GATEWAY                            │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │                    SECURE PAYMENT PAGE                               │    │
│  │  ┌─────────────────────────────────────────────────────────────┐    │    │
│  │  │  Payment Options:                                           │    │    │
│  │  │  - Credit/Debit Card                                        │    │    │
│  │  │  - Mobile Banking                                           │    │    │
│  │  │  - Internet Banking                                         │    │    │
│  │  │  - bKash, Nagad, etc.                                       │    │    │
│  │  └─────────────────────────────────────────────────────────────┘    │    │
│  │                                                                       │    │
│  │  [Enter Payment Details] → [Process Payment]                         │    │
│  └───────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
            ┌───────▼───────┐               ┌───────▼───────┐
            │   SUCCESS     │               │   FAIL/CANCEL │
            │               │               │               │
            │ User + IPN    │               │   User only   │
            └───────┬───────┘               └───────┬───────┘
                    │                               │
                    │ 6a. POST /success            │ 6b. POST /fail
                    │      + IPN to /ipn           │      or /cancel
                    ▼                               ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                      CALLBACK PROCESSING                                     │
│                                                                              │
│  SUCCESS PATH                         FAIL/CANCEL PATH                      │
│  ┌─────────────────────────────┐      ┌─────────────────────────────┐      │
│  │ 1. Validate with SSLCOMMERZ │      │ 1. Update order status      │      │
│  │ 2. Check if already done    │      │    to failed (-1)           │      │
│  │ 3. Complete order           │      │ 2. Show error message       │      │
│  │ 4. Redirect to invoice      │      │ 3. Redirect to payment page │      │
│  └─────────────────────────────┘      └─────────────────────────────┘      │
│                                                                              │
│  IPN PATH (Server-to-Server)                                                │
│  ┌─────────────────────────────┐                                            │
│  │ 1. Receive POST from SSLC   │                                            │
│  │ 2. Validate transaction     │                                            │
│  │ 3. Check for duplicates     │                                            │
│  │ 4. Complete order           │                                            │
│  │ 5. Return JSON response     │                                            │
│  └─────────────────────────────┘                                            │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ 7. Order Completed
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          ORDER COMPLETED                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  - Order status: is_completed = 1                                  │    │
│  │  - Payment type: ssl_commerze                                      │    │
│  │  - Payment status: Paid                                            │    │
│  │  - Invoice generated                                               │    │
│  │  - User redirected to: /get-invoice/{code}                          │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
┌─────────┐      ┌─────────┐      ┌──────────────┐      ┌──────────┐
│  User   │ ───> │  Front  │ ───> │    Backend   │ ───> │ Database │
│ Browser │      │   Vue   │      │  Laravel     │      │  Orders  │
└─────────┘      └─────────┘      └──────────────┘      └──────────┘
     │                │                   │                   │
     │ 1. Select      │                   │                   │
     │    Online      │                   │                   │
     │    Payment     │                   │                   │
     │                │                   │                   │
     │<───────────────│                   │                   │
     │ 2. Order       │                   │                   │
     │    Created     │                   │                   │
     │                │                   │                   │
     │ 3. Redirect    │                   │                   │
     │    to /pay     │                   │                   │
     │                │                   │                   │
     │<───────────────│<───────────────────│                   │
     │ 4. Redirect    │                   │                   │
     │    to Gateway  │                   │                   │
     │                │                   │                   │
     │ 5. Complete    │                   │                   │
     │    Payment     │                   │                   │
     │                │                   │                   │
     │ 6. Callback    │───────────────────>│                   │
     │    to /success │                   │                   │
     │                │                   │ 7. Validate       │
     │                │                   │    with SSLC      │
     │                │                   │                   │
     │                │                   │ 8. Update Order   │
     │                │                   │                   │
     │ 9. Show        │<───────────────────│                   │
     │    Invoice     │                   │                   │
```

## Class Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Laravel Controllers                               │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                    SslCommerzPaymentController                        │  │
│  │  ┌────────────────────────────────────────────────────────────────┐  │  │
│  │  │ Properties:                                                    │  │  │
│  │  │  - $order: OrderInterface                                      │  │  │
│  │  │                                                                │  │  │
│  │  │ Public Methods:                                                │  │  │
│  │  │  + index(Request)              → Initiate payment              │  │  │
│  │  │  + success(Request)            → Success callback              │  │  │
│  │  │  + fail(Request)               → Fail callback                 │  │  │
│  │  │  + cancel(Request)             → Cancel callback               │  │  │
│  │  │  + ipn(Request)                → IPN handler                   │  │  │
│  │  │                                                                │  │  │
│  │  │ Private Methods:                                               │  │  │
│  │  │  - getCurrency()                 → Get BDT currency             │  │  │
│  │  │  - activeCurrencyCheck()         → Get active currency          │  │  │
│  │  │  - amountCalculator()            → Convert to BDT               │  │  │
│  │  │  - getCustomerInfo()             → Extract customer data        │  │  │
│  │  └────────────────────────────────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          SSLCOMMERZ Library                                  │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                    SslCommerzNotification                             │  │
│  │  ┌────────────────────────────────────────────────────────────────┐  │  │
│  │  │ Properties:                                                    │  │  │
│  │  │  - $data: array          (Payment data)                        │  │  │
│  │  │  - $config: array        (SSLCOMMERZ config)                   │  │  │
│  │  │  - $error: string        (Error message)                       │  │  │
│  │  │                                                                │  │  │
│  │  │ Public Methods:                                                │  │  │
│  │  │  + __construct()              → Load config & credentials       │  │  │
│  │  │  + makePayment(array, type)  → Initiate payment                │  │  │
│  │  │  + orderValidate(array)       → Validate transaction            │  │  │
│  │  │  + setParams(array)           → Set payment parameters          │  │  │
│  │  │  + setAuthenticationInfo()    → Set auth info                   │  │  │
│  │  │                                                                │  │  │
│  │  │ Protected Methods:                                             │  │  │
│  │  │  # setRequiredInfo(array)     → Set required params [MODIFIED] │  │  │
│  │  │  # setCustomerInfo(array)     → Set customer info               │  │  │
│  │  │  # setShipmentInfo(array)     → Set shipping info               │  │  │
│  │  │  # setProductInfo(array)      → Set product info                │  │  │
│  │  │  # setAdditionalInfo(array)   → Set custom params               │  │  │
│  │  │  # validate()                  → Validate with API               │  │  │
│  │  │  # callToApi()                → Make API request                │  │  │
│  │  │  # formatResponse()           → Format API response             │  │  │
│  │  └────────────────────────────────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                              ▲                                              │
│                              │ extends                                       │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                    AbstractSslCommerz                                 │  │
│  │  - setStoreId()              (protected)                             │  │
│  │  - setStorePassword()        (protected)                             │  │
│  │  - setApiUrl()               (protected)                             │  │
│  │  - callToApi()               (protected)                             │  │
│  │  - formatResponse()          (protected)                             │  │
│  │  - redirect()                (protected)                             │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Sequence Diagram

```
User        Frontend      OrderController    Browser      SSLCOMMERZ      SslCommerzController
 │              │                │              │              │                    │
 │ Select       │                │              │              │                    │
 │ Online       │                │              │              │                    │
 │ Payment      │                │              │              │                    │
 ├─────────────>│                │              │              │                    │
 │              │ POST confirm   │              │              │                    │
 │              ├───────────────>│              │              │                    │
 │              │                │ Create Order │              │                    │
 │              │                │ (trx_id)     │              │                    │
 │              │<───────────────┤              │              │                    │
 │              │ Success        │              │              │                    │
 │              │                │              │              │                    │
 │              │ GET /pay?trx_id              │              │                    │
 │              ├─────────────────────────────>│              │                    │
 │              │                │              │              │                    │
 │              │                │              │ Initiate     │                    │
 │              │                │              │ Payment      │                    │
 │              │                │              ├─────────────>│                    │
 │              │                │              │              │                    │
 │              │                │              │ Gateway URL  │                    │
 │              │                │              │<─────────────┤                    │
 │              │                │              │              │                    │
 │              │                │              │ Redirect     │                    │
 │              │<─────────────────────────────┤              │                    │
 │              │                │              │              │                    │
 │ Redirect     │                │              │              │                    │
 │ to Gateway   │                │              │              │                    │
 ├─────────────────────────────────────────────>│              │                    │
 │              │                │              │              │                    │
 │              │                │              │              │ Show Payment       │
 │              │                │              │              │ Page               │
 │              │                │              │              │                    │
 │              │                │              │              │<───── User ───────>│
 │              │                │              │              │    Payment         │
 │              │                │              │              │                    │
 │              │                │              │ POST /success │                    │
 │              │                │              ├───────────────────────────────────>│
 │              │                │              │              │                    │
 │              │                │              │              │   Validate         │
 │              │                │              │              │<────────────────────┤
 │              │                │              │              │                    │
 │              │                │              │   Valid      │                    │
 │              │                │              │              │                    │
 │              │                │              │ Complete     │                    │
 │              │                │              │   Order      │                    │
 │              │                │              │<──────────────────────────────────┤
 │              │                │              │              │                    │
 │              │                │              │ Redirect     │                    │
 │              │                │              │ Invoice      │                    │
 │              │                │              ├───────────────────────────────────>│
 │              │                │              │              │                    │
 │              │                │              │              │                    │
 │              │                │              │   (Simultaneous IPN)             │
 │              │                │              │ POST /ipn    │                    │
 │              │                │              ├───────────────────────────────────>│
 │              │                │              │              │                    │
 │              │                │              │              │ Validate &        │
 │              │                │              │              │ Complete          │
 │              │                │              │              │ (if not done)     │
 │              │                │              │<──────────────────────────────────┤
 │              │                │              │              │                    │
 │              │                │              │   JSON OK    │                    │
 │              │                │              ├───────────────────────────────────>│
 │              │                │              │              │                    │
 │ Show         │                │              │              │                    │
 │ Invoice      │                │              │              │                    │
 │<─────────────┤                │              │              │                    │
 │              │                │              │              │                    │
```

## Database Schema (Relevant Tables)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              orders                                        │
│  ┌──────────┬───────────────┬───────────────┬──────────────────────────┐    │
│  │   id     │   code        │  trx_id       │   is_completed           │    │
│  │  (PK)    │  (string)     │  (string)     │   (int)                  │    │
│  ├──────────┼───────────────┼───────────────┼──────────────────────────┤    │
│  │   1      │   #30001      │  abc123...    │     1 (completed)        │    │
│  │   2      │   #30002      │  def456...    │     0 (pending)          │    │
│  │   3      │   #30003      │  ghi789...    │    -1 (failed)           │    │
│  └──────────┴───────────────┴───────────────┴──────────────────────────┘    │
│                                                                              │
│  Other fields: user_id, total_payable, payment_type, billing_address,        │
│                shipping_address, created_at, updated_at                      │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                            currencies                                       │
│  ┌──────────┬───────────────┬───────────────────┬──────────────────────┐    │
│  │   id     │    code       │   exchange_rate   │       name           │    │
│  │  (PK)    │  (string)     │     (decimal)     │      (string)        │    │
│  ├──────────┼───────────────┼───────────────────┼──────────────────────┤    │
│  │    1     │    BDT        │       1.00        │  Bangladeshi Taka   │    │
│  │    2     │    USD        │     110.50        │  US Dollar           │    │
│  │    3     │    EUR        │     120.00        │  Euro                │    │
│  └──────────┴───────────────┴───────────────────┴──────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                           settings (dynamic)                                 │
│  ┌───────────────────────────┬───────────────────────────────────────────┐  │
│  │        key                │              value                         │  │
│  ├───────────────────────────┼───────────────────────────────────────────┤  │
│  │ sslcommerz_id             │ test_live_box_id                          │  │
│  │ sslcommerz_password       │ test_live_password                        │  │
│  │ is_sslcommerz_sandbox...  │ 0 or 1                                    │  │
│  │ default_currency          │ 1 (BDT ID)                                │  │
│  └───────────────────────────┴───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Configuration Flow

```
config/sslcommerz.php
        │
        ├── .env (Primary)
        │   ├── SSLCZ_TESTMODE (true/false)
        │   ├── SSLCZ_STORE_ID
        │   ├── SSLCZ_STORE_PASSWORD
        │   └── IS_LOCALHOST
        │
        └── Database Settings (Override via Admin Panel)
            ├── sslcommerz_id (settingHelper)
            ├── sslcommerz_password (settingHelper)
            └── is_sslcommerz_sandbox_mode_activated (settingHelper)

Code Priority:
1. Database settings (settingHelper) - Used in controllers
2. .env values - Fallback
```

## Error Handling Flow

```
Payment Error Occurs
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    Exception Caught in Controller                            │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  try {                                                              │    │
│  │      // Payment processing                                          │    │
│  │  } catch (\Exception $e) {                                          │    │
│  │      Log::error('SSLCOMMERZ Error:', [                              │    │
│  │          'error' => $e->getMessage(),                               │    │
│  │          'file' => $e->getFile(),                                   │    │
│  │          'line' => $e->getLine(),                                   │    │
│  │          'trace' => $e->getTraceAsString(),                         │    │
│  │      ]);                                                            │    │
│  │      return back()->with(['error' => 'Payment failed']);            │    │
│  │  }                                                                  │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                                                              │
│  Error logged to: storage/logs/laravel-YYYY-MM-DD.log                       │
│  User redirected to: /payment with error message                            │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

*Document Version: 1.0*
*Last Updated: February 1, 2026*