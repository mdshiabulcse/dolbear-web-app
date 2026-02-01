# Pathao Courier Integration Guide

## Table of Contents
1. [Overview](#overview)
2. [Getting Started with Pathao](#getting-started-with-pathao)
3. [Configuration](#configuration)
4. [API Integration](#api-integration)
5. [Integration Components](#integration-components)
6. [Order Delivery Flow](#order-delivery-flow)
7. [API Endpoints](#api-endpoints)
8. [Webhook Integration](#webhook-integration)
9. [Database Schema](#database-schema)
10. [Frontend Integration](#frontend-integration)
11. [Testing](#testing)
12. [Troubleshooting](#troubleshooting)

---

## Overview

**Pathao** is a leading logistics and courier service provider in Bangladesh, offering on-demand delivery solutions for e-commerce businesses. This project has a complete Pathao Courier integration.

### Features
- Create delivery orders directly from admin panel
- Automatic city and zone selection based on customer address
- Real-time delivery fee calculation
- SMS notification to customer with tracking ID
- Webhook support for delivery status updates
- Access token management with caching

### Project Integration Status

| Component | Location |
|-----------|----------|
| **Config** | `config/pathao.php` |
| **Service** | `app/Services/PathaoService.php` |
| **Controller** | `app/Http/Controllers/Admin/PathaoCourier/PathaoCourierController.php` |
| **Repository** | `app/Repositories/Admin/PathaoCourier/PathaoCourierRepository.php` |
| **Views** | `resources/views/admin/PathaoCourier/index.blade.php` |
| **Middleware** | `app/Http/Middleware/ValidatePathaoSignature.php` |

---

## Getting Started with Pathao

### 1. Create Pathao Merchant Account

1. Visit [Pathao Logistics](https://pathao.com/)
2. Register as a merchant/business
3. Complete KYC verification
4. Obtain API credentials from Pathao dashboard

### 2. Get API Credentials

From your Pathao merchant dashboard, you'll need:
- **Client ID**
- **Client Secret**
- **Username**
- **Password**
- **Store ID**
- **API Host URL**
- **Webhook Signature** (for status updates)

---

## Configuration

Choose the appropriate environment setup below:

---

## Development Mode (Sandbox)

### Overview

Development mode uses Pathao's sandbox environment for testing delivery integrations without processing real orders. All deliveries are simulated.

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

# Pathao Sandbox Configuration
PATHAO_COURIER_HOST=https://hermes-api-sandbox.pathao.com/alcaldia
PATHAO_STORE_ID=test_store_id
PATHAO_COURIER_CLIENT_ID=test_client_id
PATHAO_COURIER_CLIENT_SECRET=test_client_secret
PATHAO_COURIER_USERNAME=test_username
PATHAO_COURIER_PASSWORD=test_password
PATHAO_COURIER_GRANT_TYPE=password
PATHAO_WEBHOOK_SIGNATURE=test_signature_secret
```

#### 2. Config File - `config/pathao.php` (Development)

```php
<?php

return [
    // SANDBOX API Host
    'host' => env('PATHAO_COURIER_HOST', 'https://hermes-api-sandbox.pathao.com/alcaldia'),

    // Sandbox Store ID
    'store_id' => env('PATHAO_STORE_ID', 'test_store_id'),

    // Sandbox API Credentials
    'client_id' => env('PATHAO_COURIER_CLIENT_ID', 'test_client_id'),
    'client_secret' => env('PATHAO_COURIER_CLIENT_SECRET', 'test_client_secret'),
    'username' => env('PATHAO_COURIER_USERNAME', 'test_username'),
    'password' => env('PATHAO_COURIER_PASSWORD', 'test_password'),
    'grant_type' => env('PATHAO_COURIER_GRANT_TYPE', 'password'),
    'webhook_signature' => env('PATHAO_WEBHOOK_SIGNATURE', 'test_signature_secret'),
];
```

#### 3. Development Verification

```bash
# Verify sandbox configuration
php artisan tinker

>>> echo "API Host: " . config('pathao.host');
// Expected: https://hermes-api-sandbox.pathao.com/alcaldia

>>> echo "Store ID: " . config('pathao.store_id');
// Expected: test_store_id

>>> echo "Client ID: " . config('pathao.client_id');
// Expected: test_client_id
```

#### 4. Test API Connection (Development)

```bash
# Test access token generation
curl -X POST \
http://localhost:8000/admin/pathao/test-token \
-H "Content-Type: application/json" \
-H "Accept: application/json"

# Test city list API
curl -X GET \
http://localhost:8000/admin/pathao/city \
-H "Accept: application/json"

# Test zone list API
curl -X GET \
"http://localhost:8000/admin/pathao/zone?city_id=1" \
-H "Accept: application/json"
```

### Sandbox Test Data

Use these values for testing in sandbox mode:

| Field | Test Value |
|-------|------------|
| **API Host** | `https://hermes-api-sandbox.pathao.com/alcaldia` |
| **Store ID** | Provided by Pathao (test) |
| **City ID** | 1 (Dhaka for testing) |
| **Zone ID** | Check via zone list API |
| **Delivery Type** | 48 (Normal) or 12 (On Demand) |
| **Item Type** | 1 (Document) or 2 (Parcel) |
| **Test Phone** | 01700000000 (11 digits, no +88) |

### Local Development with ngrok

For testing webhooks in local development:

```bash
# Install ngrok: https://ngrok.com/download

# Start ngrok tunnel
ngrok http 8000

# Update Pathao sandbox dashboard with webhook URL
# Webhook URL: https://abc123.ngrok.io/admin/pathao/status-update
# Header: X-PATHAO-Signature: test_signature_secret
```

---

## Live Production Mode

### Overview

Production mode uses Pathao's live gateway for processing real deliveries. **Ensure all testing is complete before switching to production.**

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

# ⚠️ PRODUCTION CREDENTIALS - Get from Pathao Dashboard
# IMPORTANT: Never share these credentials or commit to version control!

# Pathao Production API Host
PATHAO_COURIER_HOST=https://hermes-api.pathao.com/alcaldia

# Production Store ID (from Pathao Dashboard)
PATHAO_STORE_ID=your_live_store_id_here

# Production API Credentials
PATHAO_COURIER_CLIENT_ID=your_live_client_id_here
PATHAO_COURIER_CLIENT_SECRET=your_live_client_secret_here
PATHAO_COURIER_USERNAME=your_live_username_here
PATHAO_COURIER_PASSWORD=your_live_password_here
PATHAO_COURIER_GRANT_TYPE=password

# ⚠️ Webhook Signature - Keep this secret!
PATHAO_WEBHOOK_SIGNATURE=your_production_webhook_signature_here
```

#### 2. Config File - `config/pathao.php` (Production)

```php
<?php

return [
    // PRODUCTION API Host
    'host' => env('PATHAO_COURIER_HOST', 'https://hermes-api.pathao.com/alcaldia'),

    // Production Store ID (from .env)
    'store_id' => env('PATHAO_STORE_ID'),

    // Production Credentials (from .env)
    'client_id' => env('PATHAO_COURIER_CLIENT_ID'),
    'client_secret' => env('PATHAO_COURIER_CLIENT_SECRET'),
    'username' => env('PATHAO_COURIER_USERNAME'),
    'password' => env('PATHAO_COURIER_PASSWORD'),
    'grant_type' => env('PATHAO_COURIER_GRANT_TYPE', 'password'),
    'webhook_signature' => env('PATHAO_WEBHOOK_SIGNATURE'),
];
```

#### 3. Production Verification

```bash
# Verify production configuration
php artisan tinker

>>> echo "API Host: " . config('pathao.host');
// Expected: https://hermes-api.pathao.com/alcaldia

>>> echo "Store ID: " . config('pathao.store_id');
// Expected: your_live_store_id

>>> echo "Client ID: " . config('pathao.client_id');
// Expected: your_live_client_id

>>> echo "Webhook Signature: " . config('pathao.webhook_signature');
// Expected: your_production_webhook_signature
```

#### 4. Test API Connection (Production)

```bash
# Test access token generation
curl -X POST \
https://your-domain.com/admin/pathao/test-token \
-H "Content-Type: application/json" \
-H "Accept: application/json"

# Test city list API
curl -X GET \
https://your-domain.com/admin/pathao/city \
-H "Accept: application/json"

# Test zone list API
curl -X GET \
"https://your-domain.com/admin/pathao/zone?city_id=1" \
-H "Accept: application/json"
```

### Pre-Production Checklist

Before switching to live mode, ensure:

- [ ] SSL certificate is installed and valid
- [ ] Webhook URL is publicly accessible via HTTPS
- [ ] Production Store ID obtained from Pathao
- [ ] Production API credentials configured
- [ ] Webhook signature set up correctly
- [ ] All city/zone data cached properly
- [ ] SMS service configured for notifications
- [ ] Database backup configured
- [ ] Error logging enabled
- [ ] `APP_DEBUG=false` in production

### Webhook Setup (Production)

1. **Configure Webhook URL in Pathao Dashboard**:
   ```
   URL: https://your-domain.com/admin/pathao/status-update
   Method: POST
   Content-Type: application/json
   Headers:
   X-PATHAO-Signature: your_production_webhook_signature_here
   ```

2. **Verify Webhook Signature**:
   ```php
   // Middleware: ValidatePathaoSignature.php
   // Checks X-PATHAO-Signature header matches config
   if ($header !== $expectedSignature) {
       return response('Unauthorized', 401);
   }
   ```

3. **Test Webhook**:
   ```bash
   # Test webhook endpoint
   curl -X POST \
   https://your-domain.com/admin/pathao/status-update \
   -H "Content-Type: application/json" \
   -H "X-PATHAO-Signature: your_production_webhook_signature_here" \
   -d '{
       "consignment_id": "PTH-123456789",
       "status": "delivered",
       "updated_at": "2024-01-15 14:30:00"
   }'
   ```

### First Live Delivery

1. Navigate to Admin → Orders
2. Select an order for delivery
3. Click "Create Delivery" → Pathao
4. Verify pre-filled customer information
5. Select appropriate city and zone
6. Choose delivery type (Normal/On Demand)
7. Enter item details (quantity, weight, COD amount)
8. Submit to create delivery
9. Verify consignment ID is generated
10. Confirm SMS is sent to customer
11. Check order details for delivery fee

---

## Environment Comparison

| Setting | Development (Sandbox) | Production (Live) |
|---------|----------------------|-------------------|
| **API Host** | `https://hermes-api-sandbox.pathao.com/alcaldia` | `https://hermes-api.pathao.com/alcaldia` |
| **Store ID** | Test credentials | Live store ID |
| **Client ID** | Test client ID | Live client ID |
| **Client Secret** | Test secret | Live secret |
| **Username** | Test username | Live username |
| **Password** | Test password | Live password |
| **Webhook Signature** | Test signature | Production signature |
| **APP_ENV** | `local` | `production` |
| **APP_DEBUG** | `true` | `false` |
| **URL Protocol** | HTTP (localhost) | HTTPS required |
| **Real Deliveries** | No (test mode) | Yes (real deliveries) |

---

## Switching Between Environments

### From Development to Production

```bash
# 1. Update .env file
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Update Pathao credentials
PATHAO_COURIER_HOST=https://hermes-api.pathao.com/alcaldia
PATHAO_STORE_ID=your_live_store_id
PATHAO_COURIER_CLIENT_ID=your_live_client_id
PATHAO_COURIER_CLIENT_SECRET=your_live_client_secret
PATHAO_COURIER_USERNAME=your_live_username
PATHAO_COURIER_PASSWORD=your_live_password
PATHAO_WEBHOOK_SIGNATURE=your_live_webhook_signature

# 2. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 3. Clear specific Pathao caches
php artisan tinker
>>> Cache::forget('pathao_access_token');
>>> Cache::forget('city-list');
>>> Cache::forget('zone-list-1');  # Replace with actual city_id

# 4. Test API connection
php artisan tinker
>>> $pathao = new \App\Services\PathaoService();
>>> $cities = $pathao->getCity();
>>> print_r($cities);
```

### From Production to Development (Rollback)

```bash
# 1. Update .env file
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Update Pathao credentials to sandbox
PATHAO_COURIER_HOST=https://hermes-api-sandbox.pathao.com/alcaldia
PATHAO_STORE_ID=test_store_id
PATHAO_COURIER_CLIENT_ID=test_client_id
PATHAO_COURIER_CLIENT_SECRET=test_client_secret
PATHAO_COURIER_USERNAME=test_username
PATHAO_COURIER_PASSWORD=test_password
PATHAO_WEBHOOK_SIGNATURE=test_signature_secret

# 2. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 3. Clear specific Pathao caches
php artisan tinker
>>> Cache::forget('pathao_access_token');
>>> Cache::forget('city-list');
```

---

## Webhook Configuration by Environment

### Development Webhook (Testing with ngrok)

```bash
# 1. Start ngrok
ngrok http 8000

# 2. Update Pathao Sandbox Dashboard
Webhook URL: https://abc123.ngrok.io/admin/pathao/status-update
Signature: test_signature_secret
Header: X-PATHAO-Signature: test_signature_secret

# 3. Test webhook
curl -X POST \
https://abc123.ngrok.io/admin/pathao/status-update \
-H "X-PATHAO-Signature: test_signature_secret" \
-H "Content-Type: application/json" \
-d '{"consignment_id":"TEST-123","status":"delivered"}'
```

### Production Webhook

```bash
# 1. Update Pathao Production Dashboard
Webhook URL: https://your-domain.com/admin/pathao/status-update
Signature: your_production_webhook_signature_here
Header: X-PATHAO-Signature: your_production_webhook_signature_here

# 2. Configure firewall to allow Pathao IP
# (Contact Pathao for their IP addresses)

# 3. Test webhook
curl -X POST \
https://your-domain.com/admin/pathao/status-update \
-H "X-PATHAO-Signature: your_production_webhook_signature_here" \
-H "Content-Type: application/json" \
-d '{"consignment_id":"PTH-123","status":"delivered"}'
```

---

## Access Token Management

### Development Token

```php
// Tokens are cached for 7 days
// Clear to force refresh
php artisan tinker
>>> Cache::forget('pathao_access_token');

// View cached token
>>> Cache::get('pathao_access_token');
```

### Production Token

```php
// Production tokens are also cached
// Tokens auto-refresh on 401 response
// Monitor token expiration in logs

// Check token expiration
Log::info('Pathao Token:', [
    'token' => substr(Cache::get('pathao_access_token'), 0, 20) . '...',
    'expires' => now()->addDays(7)
]);
```

---

## City & Zone Data Caching

### Development Caching

```bash
# Cities are cached permanently as 'city-list'
# Zones are cached permanently as 'zone-list-{city_id}'

# View cached cities
php artisan tinker
>>> Cache::get('city-list');

# View cached zones for city ID 1
>>> Cache::get('zone-list-1');

# Clear all Pathao caches
>>> Cache::forget('city-list');
>>> Cache::forget('zone-list-1');
```

### Production Caching

```php
// In production, data is cached permanently
// Refresh manually when Pathao updates cities/zones

// Refresh city list
Route::get('/admin/pathao/refresh-cities', function () {
    Cache::forget('city-list');
    $pathao = new PathaoService();
    return $pathao->getCity();
});

// Refresh zone list
Route::get('/admin/pathao/refresh-zones/{cityId}', function ($cityId) {
    Cache::forget('zone-list-' . $cityId);
    $pathao = new PathaoService();
    return $pathao->getZone((object)['city_id' => $cityId]);
});
```

---

## Testing by Environment

### Development Testing

```php
// tests/Unit/PathaoServiceTest.php

use Tests\TestCase;
use App\Services\PathaoService;

class PathaoServiceTest extends TestCase
{
    public function test_get_access_token()
    {
        $service = new PathaoService();
        $this->assertNotNull($service->getAccessToken());
    }

    public function test_get_city_list()
    {
        $service = new PathaoService();
        $cities = $service->getCity();
        $this->assertArrayHasKey('data', $cities);
    }

    public function test_get_zone_list()
    {
        $service = new PathaoService();
        $zones = $service->getZone((object)['city_id' => 1]);
        $this->assertArrayHasKey('data', $zones);
    }
}
```

### Production Testing

```bash
# 1. Test with small delivery first
# Create delivery with low COD amount

# 2. Verify entire flow
- Order creation ✓
- Consignment ID generation ✓
- SMS notification ✓
- Webhook reception ✓
- Status update ✓

# 3. Monitor logs
tail -f storage/logs/laravel.log | grep Pathao

# 4. Check database
php artisan tinker
>>> Order::where('delivery_type', 'pathao')->get();
```

### 2. Config File (`config/pathao.php`)

```php
<?php

return [
    'host' => env('PATHAO_COURIER_HOST'),
    'store_id' => env('PATHAO_STORE_ID'),
    'client_id' => env('PATHAO_COURIER_CLIENT_ID'),
    'client_secret' => env('PATHAO_COURIER_CLIENT_SECRET'),
    'username' => env('PATHAO_COURIER_USERNAME'),
    'password' => env('PATHAO_COURIER_PASSWORD'),
    'grant_type' => env('PATHAO_COURIER_GRANT_TYPE'),
    'webhook_signature' => env('PATHAO_WEBHOOK_SIGNATURE')
];
```

### 3. Route Configuration (`routes/admin.php`)

```php
// Pathao Courier Routes
Route::post('pathao/status-update', [PathaoCourierController::class, 'updateStatus'])
    ->middleware('pathao.signature');

Route::middleware(['XSS'])->group(function () {
    // Admin routes with middleware
    Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'permission']], function () {
        Route::group(['prefix' => 'orders'], function () {
            // Pathao delivery routes
            Route::get('delivery/pathao', [PathaoCourierController::class, 'index'])
                ->name('admin.delivery.pathao');
            Route::post('delivery/pathao', [PathaoCourierController::class, 'store'])
                ->name('admin.delivery.pathao.store');
        });

        // API endpoints for city/zone
        Route::get('pathao/city', [PathaoCourierController::class, 'city']);
        Route::get('pathao/zone', [PathaoCourierController::class, 'zone']);
    });
});
```

### 4. Middleware Configuration (`app/Http/Kernel.php`)

```php
protected $middlewareAliases = [
    // ...
    'pathao.signature' => \App\Http\Middleware\ValidatePathaoSignature::class,
];
```

---

## API Integration

### PathaoService Class

The main service class handles all Pathao API interactions.

**Location**: `app/Services/PathaoService.php`

```php
<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PathaoService
{
    protected $accessToken;
    protected $header;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();
        $this->header = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create delivery order
     */
    public function order($data)
    {
        try {
            $response = Http::withHeaders($this->header)
                ->post(config('pathao.host') . '/orders', [
                    'merchant_order_id' => $data->orderId,
                    'store_id' => config('pathao.store_id'),
                    'recipient_name' => $data->name,
                    'recipient_phone' => $this->getValidPhone($data->phone),
                    'recipient_address' => $data->address,
                    'recipient_city' => $data->city,
                    'recipient_zone' => $data->zone,
                    'delivery_type' => $data->delivery_type,
                    'item_type' => $data->item_type,
                    'item_quantity' => $data->quantity,
                    'item_weight' => $data->weight,
                    'amount_to_collect' => $data->amount_to_collect && !empty($data->amount_to_collect) ? $data->amount_to_collect : 0,
                ]);

            // Handle token expiration
            if ($response->status() === 401) {
                Cache::forget('pathao_access_token');
                $this->getAccessToken();
            }

            // Handle validation errors
            if (intdiv($response->status(), 100) === 4) {
                $responseBody = $response->json();
                $formattedError = [
                    'message' => 'Please fix the given errors',
                    'type' => 'error',
                    'code' => $response->status(),
                    'errors' => $responseBody['errors'] ?? [],
                ];
                throw new \Exception(json_encode($formattedError), $response->status());
            }

            return $response->json();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get access token with caching
     */
    private function getAccessToken()
    {
        if (Cache::has('pathao_access_token')) {
            return Cache::get('pathao_access_token');
        }

        $response = Http::post(config('pathao.host') . '/issue-token', [
            'client_id' => config('pathao.client_id'),
            'client_secret' => config('pathao.client_secret'),
            'username' => config('pathao.username'),
            'password' => config('pathao.password'),
            'grant_type' => config('pathao.grant_type'),
        ]);

        // Cache for 7 days
        $accessToken = $response->json()['access_token'];
        Cache::put('pathao_access_token', $accessToken, now()->addMinutes(60 * 24 * 7));
        return $accessToken;
    }

    /**
     * Get city list (cached)
     */
    public function getCity()
    {
        return Cache::rememberForever('city-list', function () {
            $response = Http::withHeaders($this->header)
                ->get(config('pathao.host') . '/countries/1/city-list');
            return $response->json();
        });
    }

    /**
     * Get zone list by city (cached)
     */
    public function getZone($data)
    {
        return Cache::rememberForever('zone-list-' . $data->city_id, function () use ($data) {
            $response = Http::withHeaders($this->header)
                ->get(config('pathao.host') . '/cities/' . $data->city_id . '/zone-list');
            return $response->json();
        });
    }

    /**
     * Format phone number (remove +88 prefix)
     */
    private function getValidPhone($phone)
    {
        if (Str::contains($phone, '+88')) {
            return Str::replace('+88', '', $phone);
        }
        return $phone;
    }
}
```

---

## Integration Components

### 1. Controller

**Location**: `app/Http/Controllers/Admin/PathaoCourier/PathaoCourierController.php`

```php
<?php

namespace App\Http\Controllers\Admin\PathaoCourier;

use Illuminate\Http\Request;
use App\Services\PathaoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PathaoCourierRequest;
use App\Http\Requests\Admin\PathaoWebhookRequest;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Repositories\Interfaces\Admin\PathaoCourier\PathaoCourierInterface;

class PathaoCourierController extends Controller
{
    private $pathao;
    protected $order;

    public function __construct(OrderInterface $order, PathaoCourierInterface $pathao)
    {
        $this->pathao = $pathao;
        $this->order = $order;
    }

    /**
     * Show Pathao delivery form
     */
    public function index(Request $request)
    {
        $request->validate(['orderId' => 'required|numeric|min:1']);

        $order = $this->order->get($request['orderId']);

        if (!$order) {
            toastr()->error('Order not found');
            return redirect()->route('orders');
        }

        return view('admin.PathaoCourier.index', [
            'orderData' => $order,
            'orderID' => $request['orderId']
        ]);
    }

    /**
     * Create Pathao delivery order
     */
    public function store(PathaoCourierRequest $request, PathaoService $pathaoService)
    {
        try {
            // Get delivery data from Pathao API
            $deliveryData = $pathaoService->order($request);

            // Get order data
            $orderData = $this->order->get($request->orderId);

            // Process order
            return $this->pathao->processOrder($orderData, $deliveryData);

        } catch (\Exception $e) {
            $errorMessage = json_decode($e->getMessage(), true);

            if (is_array($errorMessage)) {
                return response()->json($errorMessage, $errorMessage['code']);
            }

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'type' => 'error',
                'code' => 500,
                'errors' => [],
            ], 500);
        }
    }

    /**
     * Get city list API
     */
    public function city(PathaoService $pathaoService)
    {
        return $pathaoService->getCity();
    }

    /**
     * Get zone list API
     */
    public function zone(Request $request, PathaoService $pathaoService)
    {
        $rules = ['city_id' => 'required|numeric|min:1'];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return $pathaoService->getZone($request);
    }

    /**
     * Handle Pathao webhook status update
     */
    public function updateStatus(PathaoWebhookRequest $request)
    {
        Log::info('Pathao status update:', $request->all());
        $this->pathao->updateStatus($request);
    }
}
```

### 2. Repository

**Location**: `app/Repositories/Admin/PathaoCourier/PathaoCourierRepository.php`

```php
<?php

namespace App\Repositories\Admin\PathaoCourier;

use App\Models\Order;
use App\Services\ElitbuzzSmsService;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\Admin\PathaoCourier\PathaoCourierInterface;

class PathaoCourierRepository implements PathaoCourierInterface
{
    /**
     * Update order status from webhook
     */
    public function updateStatus($data)
    {
        $order = Order::where('pathao_delivery_id', $data->consignment_id)->first();
        $order->save();
        Log::info("Pathao Order Status Updated", [$data->consignment_id]);
    }

    /**
     * Process delivery order
     */
    public function processOrder($orderData, $deliverydata)
    {
        // Update order with Pathao delivery info
        $orderData->delivery_type = 'pathao';
        $orderData->pathao_delivery_id = $deliverydata['data']['consignment_id'];
        $orderData->delivery_fee = $deliverydata['data']['delivery_fee'];
        $orderData->save();

        // Send SMS to customer with tracking ID
        if (isset($orderData->billing_address['phone_no']) || isset($orderData->user->phone)) {
            $smsService = new ElitbuzzSmsService();
            $smsService->pathaoSend(
                $orderData->billing_address['phone_no'] ?? $orderData->user->phone,
                [
                    'customer' => $orderData->billing_address['name'] ?? $orderData->user->first_name,
                    'trackingId' => $orderData->pathao_delivery_id,
                ]
            );
        }

        return $deliverydata;
    }
}
```

### 3. Request Validation

**Location**: `app/Http/Requests/Admin/PathaoCourierRequest.php`

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PathaoCourierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'orderId' => 'required|numeric|min:1',
            'name' => 'required',
            'phone' => 'required|numeric',
            'address' => 'required|string|min:10',
            'city' => 'required|numeric',
            'zone' => 'required|numeric',
            'delivery_type' => 'required|numeric',
            'item_type' => 'required|numeric',
            'quantity' => 'required|numeric',
            'weight' => 'required|numeric',
            'amount_to_collect' => 'required|numeric|min:0'
        ];
    }
}
```

### 4. Webhook Middleware

**Location**: `app/Http/Middleware/ValidatePathaoSignature.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidatePathaoSignature
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('X-PATHAO-Signature');
        $expectedSignature = config('pathao.webhook_signature');

        // Validate signature
        if ($header !== $expectedSignature) {
            return response('Unauthorized', 401);
        }

        return $next($request);
    }
}
```

---

## Order Delivery Flow

### Step-by-Step Process

```
┌─────────────────────────────────────────────────────────────────┐
│                    PATHAO DELIVERY FLOW                          │
└─────────────────────────────────────────────────────────────────┘

1. Admin clicks "Create Delivery" from Order Details
   ↓
2. System opens Pathao delivery form with pre-filled customer data
   ↓
3. Form loads city list from Pathao API (cached)
   ↓
4. Form auto-selects city based on customer's district
   ↓
5. City change triggers zone load for selected city
   ↓
6. System auto-selects zone based on customer's thana
   ↓
7. Admin reviews/edits delivery information
   ↓
8. Admin selects delivery type (Normal/On Demand) & item type
   ↓
9. Admin enters item quantity, weight, and amount to collect
   ↓
10. Form submits to Pathao API via AJAX
   ↓
11. Pathao validates and returns consignment ID & delivery fee
   ↓
12. System updates order with Pathao delivery info
   ↓
13. SMS sent to customer with tracking ID
   ↓
14. Admin redirected to orders list with success message
```

### Request/Response Flow

**Create Order Request:**
```json
POST /orders
{
    "merchant_order_id": "12345",
    "store_id": "your_store_id",
    "recipient_name": "John Doe",
    "recipient_phone": "01700000000",
    "recipient_address": "House 123, Road 10, Dhanmondi",
    "recipient_city": 1,
    "recipient_zone": 10,
    "delivery_type": 48,
    "item_type": 2,
    "item_quantity": 2,
    "item_weight": 0.5,
    "amount_to_collect": 1500.00
}
```

**Success Response:**
```json
{
    "code": 200,
    "message": "Success",
    "data": {
        "consignment_id": "PTH-123456789",
        "delivery_fee": 60.00,
        "estimated_delivery_time": "2-3 days"
    }
}
```

**Error Response:**
```json
{
    "message": "Please fix the given errors",
    "type": "error",
    "code": 422,
    "errors": {
        "recipient_phone": ["The recipient phone field is required."],
        "recipient_zone": ["The selected zone is invalid."]
    }
}
```

---

## API Endpoints

### Pathao API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/issue-token` | POST | Get OAuth access token |
| `/countries/1/city-list` | GET | Get all cities |
| `/cities/{city_id}/zone-list` | GET | Get zones by city |
| `/orders` | POST | Create delivery order |

### Internal Application Endpoints

| Endpoint | Method | Purpose | Middleware |
|----------|--------|---------|------------|
| `/admin/orders/delivery/pathao` | GET | Show delivery form | auth, permission |
| `/admin/orders/delivery/pathao` | POST | Create delivery | auth, permission |
| `/admin/pathao/city` | GET | Get city list API | auth |
| `/admin/pathao/zone` | GET | Get zone list API | auth |
| `/admin/pathao/status-update` | POST | Webhook status update | pathao.signature |

---

## Webhook Integration

### Setting Up Webhook

Pathao sends delivery status updates to your webhook endpoint.

**Webhook URL**: `https://your-domain.com/admin/pathao/status-update`

**Headers**:
```
X-PATHAO-Signature: your_webhook_signature_secret
Content-Type: application/json
```

### Webhook Payload

```json
{
    "consignment_id": "PTH-123456789",
    "status": "delivered",
    "updated_at": "2024-01-15 14:30:00",
    "remarks": "Successfully delivered"
}
```

### Status Values

| Status | Description |
|--------|-------------|
| `pending` | Order created, awaiting pickup |
| `picked_up` | Package picked by Pathao rider |
| `in_transit` | Package is in transit |
| `delivered` | Package successfully delivered |
| `cancelled` | Delivery cancelled |
| `returned` | Package returned to sender |

### Webhook Handler

```php
public function updateStatus(PathaoWebhookRequest $request)
{
    Log::info('Pathao status update:', $request->all());

    // Find order by consignment ID
    $order = Order::where('pathao_delivery_id', $request->consignment_id)->first();

    if ($order) {
        // Update order status
        $order->delivery_status = $request->status;
        $order->save();

        Log::info("Pathao Order Status Updated", [$request->consignment_id]);
    }
}
```

---

## Database Schema

### Orders Table Migration

**Location**: `database/migrations/2024_05_14_150608_add_delivery_column_to_orders_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryColumnToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_type')->nullable()->after('id');
            $table->string('pathao_delivery_id')->nullable()->after('delivery_type');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('pathao_delivery_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'pathao_delivery_id', 'delivery_fee']);
        });
    }
}
```

### Order Model Fields

| Field | Type | Description |
|-------|------|-------------|
| `delivery_type` | string | Delivery method ('pathao', 'steadfast', etc.) |
| `pathao_delivery_id` | string | Pathao consignment ID |
| `delivery_fee` | decimal(10,2) | Delivery charge amount |

---

## Frontend Integration

### Delivery Form View

**Location**: `resources/views/admin/PathaoCourier/index.blade.php`

### Form Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `orderId` | hidden | Yes | Internal order ID |
| `name` | text | Yes | Recipient name (pre-filled) |
| `phone` | text | Yes | Recipient phone (pre-filled) |
| `address` | textarea | Yes | Delivery address (pre-filled) |
| `city` | select | Yes | City dropdown (loaded from API) |
| `zone` | select | Yes | Zone dropdown (loaded from API) |
| `delivery_type` | select | Yes | 48=Normal, 12=On Demand |
| `item_type` | select | Yes | 1=Document, 2=Parcel |
| `quantity` | number | Yes | Item quantity |
| `weight` | number | Yes | Weight in KG |
| `amount_to_collect` | number | Yes | COD amount (0 if prepaid) |

### JavaScript Implementation

```javascript
$(document).ready(function () {
    const baseUrl = "{{ env('APP_URL') }}";
    const orderDistrict = "{{ $orderData->shipping_address['district'] ?? '' }}".trim().toLowerCase();
    const orderThana = "{{ $orderData->shipping_address['thana'] ?? '' }}".trim().toLowerCase();

    // Load cities on page load
    $.ajax({
        url: baseUrl + '/admin/pathao/city',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const cityData = data?.data?.data;
            const $city = $('#city');
            let selectedCityId = null;

            $city.empty();
            $city.append($('<option>', { value: '', text: 'Select a city' }));

            $.each(cityData, function (index, city) {
                const option = $('<option>', {
                    value: city.city_id,
                    text: city.city_name
                });

                // Auto-match district to city
                if (city.city_name.toLowerCase() === orderDistrict) {
                    selectedCityId = city.city_id;
                    option.attr('selected', true);
                }
                $city.append(option);
            });

            if (selectedCityId) {
                $city.val(selectedCityId).trigger('change');
            }
        }
    });

    // Load zones when city changes
    $('#city').change(function () {
        const cityId = $(this).val();
        if (cityId) {
            loadZones(cityId);
        }
    });

    function loadZones(cityId) {
        $.ajax({
            url: baseUrl + '/admin/pathao/zone',
            type: 'GET',
            dataType: 'json',
            data: { city_id: cityId },
            success: function (data) {
                const zoneData = data?.data?.data || [];
                const $zone = $('#zone');

                $zone.empty().append($('<option>', { value: '', text: 'Select Zone' }));

                $.each(zoneData, function (index, zone) {
                    const zoneName = zone.zone_name.trim().toLowerCase();
                    const option = $('<option>', {
                        value: zone.zone_id,
                        text: zone.zone_name
                    });

                    // Auto-match thana to zone
                    if (zoneName === orderThana || zoneName.includes(orderThana)) {
                        option.attr('selected', true);
                    }
                    $zone.append(option);
                });
            }
        });
    }

    // Form submission via AJAX
    $('#orderForm').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                window.location.href = baseUrl + '/admin/orders';
            },
            error: function (error) {
                // Display error messages
                var errors = error.responseJSON.errors;
                var errorMessage = '<div class="alert alert-danger"><ul>';
                $.each(errors, function (key, value) {
                    errorMessage += '<li>' + value[0] + '</li>';
                });
                errorMessage += '</ul></div>';
                $('#orderForm').prepend(errorMessage);
            }
        });
    });
});
```

### Delivery Type Options

| Value | Type | Description |
|-------|------|-------------|
| 48 | Normal | Standard delivery (2-3 days) |
| 12 | On Demand | Express delivery (same day/next day) |

### Item Type Options

| Value | Type | Description |
|-------|------|-------------|
| 1 | Document | Papers, documents (small) |
| 2 | Parcel | Packages, products (large) |

---

## Testing

### Sandbox Environment

Pathao provides a sandbox environment for testing:

```env
# .env - Sandbox
PATHAO_COURIER_HOST=https://hermes-api.pathao.com/alcaldia-sandbox
PATHAO_STORE_ID=test_store_id
PATHAO_COURIER_CLIENT_ID=test_client_id
PATHAO_COURIER_CLIENT_SECRET=test_secret
PATHAO_COURIER_USERNAME=test_username
PATHAO_COURIER_PASSWORD=test_password
PATHAO_WEBHOOK_SIGNATURE=test_signature
```

### Testing Checklist

- [ ] Verify API credentials are correct
- [ ] Test city list API endpoint
- [ ] Test zone list API endpoint
- [ ] Create test delivery order
- [ ] Verify consignment ID is generated
- [ ] Check delivery fee calculation
- [ ] Test SMS notification
- [ ] Verify webhook signature validation
- [ ] Test status update webhook
- [ ] Verify order status updates

### Manual Testing Steps

1. **Test City API**:
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
https://hermes-api.pathao.com/alcaldia/countries/1/city-list
```

2. **Test Zone API**:
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
https://hermes-api.pathao.com/alcaldia/cities/1/zone-list
```

3. **Test Token Generation**:
```bash
curl -X POST \
https://hermes-api.pathao.com/alcaldia/issue-token \
-H "Content-Type: application/json" \
-d '{
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_SECRET",
    "username": "YOUR_USERNAME",
    "password": "YOUR_PASSWORD",
    "grant_type": "password"
}'
```

---

## Troubleshooting

### Common Issues

#### 1. "401 Unauthorized"

**Cause**: Access token expired or invalid credentials

**Solution**:
```bash
# Clear cached token
php artisan tinker
>>> Cache::forget('pathao_access_token');

# Verify credentials in .env
# Ensure credentials match Pathao dashboard
```

#### 2. "City list not loading"

**Cause**: API connectivity issue or invalid access token

**Solution**:
```javascript
// Check browser console for errors
// Verify network request is being made
// Check Laravel logs: storage/logs/laravel.log
```

#### 3. "Zone not auto-selecting"

**Cause**: Thana name doesn't match zone name exactly

**Solution**:
```javascript
// The matching logic is case-insensitive
// Check if thana name exists in Pathao zone list
// Manual selection may be required
```

#### 4. "Webhook not receiving updates"

**Cause**: Signature mismatch or webhook URL not accessible

**Solution**:
```bash
# Check webhook signature matches in config
# Verify webhook URL is publicly accessible
# Check middleware is applied to route
# Test webhook using ngrok for local development
```

#### 5. "SMS not sending"

**Cause**: SMS service configuration issue

**Solution**:
```php
// Check ElitbuzzSmsService configuration
// Verify SMS credentials in .env
// Check phone number format (remove +88)
```

### Debug Mode

Enable detailed logging:

```php
// In PathaoService.php
Log::info('Pathao Request:', [
    'endpoint' => config('pathao.host') . '/orders',
    'data' => $data
]);

Log::info('Pathao Response:', [
    'status' => $response->status(),
    'body' => $response->json()
]);
```

---

## Logging & Status Tracking

### Comprehensive Logging Implementation

Track all Pathao operations and status changes with detailed logging:

#### 1. Order Creation Logging

**Location**: `app/Services/PathaoService.php`

```php
use Illuminate\Support\Facades\Log;

public function order($data)
{
    $endpoint = config('pathao.host') . '/orders';
    $requestData = [
        'merchant_order_id' => $data->orderId,
        'store_id' => config('pathao.store_id'),
        'recipient_name' => $data->name,
        'recipient_phone' => $this->getValidPhone($data->phone),
        'recipient_address' => $data->address,
        'recipient_city' => $data->city,
        'recipient_zone' => $data->zone,
        'delivery_type' => $data->delivery_type,
        'item_type' => $data->item_type,
        'item_quantity' => $data->quantity,
        'item_weight' => $data->weight,
        'amount_to_collect' => $data->amount_to_collect ?? 0,
    ];

    // Log: Request initiation
    Log::info('Pathao Order Request Initiated', [
        'order_id' => $data->orderId,
        'endpoint' => $endpoint,
        'recipient_name' => $data->name,
        'recipient_phone' => $this->getValidPhone($data->phone),
        'city' => $data->city,
        'zone' => $data->zone,
        'amount_to_collect' => $requestData['amount_to_collect'],
        'delivery_type' => $data->delivery_type,
    ]);

    try {
        $response = Http::withHeaders($this->header)
            ->post($endpoint, $requestData);

        // Log: Response received
        Log::info('Pathao Order Response Received', [
            'order_id' => $data->orderId,
            'status' => $response->status(),
            'success' => $response->successful(),
        ]);

        if ($response->status() === 401) {
            Log::warning('Pathao Token Expired - Refreshing', [
                'order_id' => $data->orderId,
                'old_token' => substr($this->accessToken, 0, 20) . '...'
            ]);

            Cache::forget('pathao_access_token');
            $this->getAccessToken();

            Log::info('Pathao Token Refreshed Successfully', [
                'order_id' => $data->orderId,
                'new_token' => substr($this->accessToken, 0, 20) . '...'
            ]);
        }

        if (intdiv($response->status(), 100) === 4) {
            $responseBody = $response->json();

            Log::error('Pathao Order Validation Failed', [
                'order_id' => $data->orderId,
                'status' => $response->status(),
                'errors' => $responseBody['errors'] ?? [],
            ]);

            // ... error handling
        }

        $responseData = $response->json();

        // Log: Success response
        if ($response->successful() && isset($responseData['data']['consignment_id'])) {
            Log::info('Pathao Order Created Successfully', [
                'order_id' => $data->orderId,
                'consignment_id' => $responseData['data']['consignment_id'] ?? null,
                'delivery_fee' => $responseData['data']['delivery_fee'] ?? null,
                'status' => $response->status(),
                'response_data' => $responseData['data'] ?? [],
            ]);
        }

        return $responseData;

    } catch (\Exception $e) {
        // Log: Exception caught
        Log::error('Pathao Order Request Exception', [
            'order_id' => $data->orderId,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
}
```

#### 2. Webhook Status Update Logging

**Location**: `app/Repositories/Admin/PathaoCourier/PathaoCourierRepository.php`

```php
use Illuminate\Support\Facades\Log;

public function updateStatus($data)
{
    // Log incoming webhook
    Log::info('Pathao Webhook Status Update Received', [
        'consignment_id' => $data->consignment_id,
        'status' => $data->status,
        'message' => $data->message ?? null,
        'updated_at' => $data->updated_at ?? null,
        'raw_data' => $data->all(),
    ]);

    $order = Order::where('pathao_delivery_id', $data->consignment_id)->first();

    if ($order) {
        $oldStatus = $order->delivery_status;

        // Update order status
        $order->delivery_status = $data->status;
        $order->save();

        // Log status change
        Log::info('Pathao Order Status Updated', [
            'order_id' => $order->id,
            'consignment_id' => $order->pathao_delivery_id,
            'old_status' => $oldStatus,
            'new_status' => $data->status,
            'updated_at' => $order->updated_at->toDateTimeString(),
        ]);
    } else {
        // Log: Order not found
        Log::warning('Pathao Webhook: Order not found', [
            'consignment_id' => $data->consignment_id,
            'status' => $data->status,
        ]);
    }
}
```

### Viewing Pathao Logs

#### View All Pathao Logs

```bash
# View real-time logs
tail -f storage/logs/laravel.log | grep -i pathao

# View today's logs
grep -i "Pathao" storage/logs/laravel-$(date +%Y-%m-%d).log

# View all Pathao logs
grep -i "Pathao" storage/logs/laravel.log
```

#### Filter by Log Level

```bash
# Only errors
grep "Pathao.*ERROR" storage/logs/laravel.log

# Only warnings
grep "Pathao.*WARNING" storage/logs/laravel.log

# Only info logs
grep "Pathao.*INFO" storage/logs/laravel.log
```

#### Filter by Order ID

```bash
# All logs for specific order
grep "Pathao.*order_id.*12345" storage/logs/laravel.log

# Order creation flow for specific order
grep "Pathao.*12345" storage/logs/laravel.log | grep -E "(Initiated|Received|Success|Created)"
```

#### Filter by Consignment ID

```bash
# All logs for specific consignment
grep "PTH-123456789" storage/logs/laravel.log

# Status changes for specific consignment
grep "PTH-123456789" storage/logs/laravel.log | grep "Status"
```

### Log Examples

#### Successful Order Creation

```
[2024-01-15 14:30:22] local.INFO: Pathao Order Request Initiated {"order_id":12345,"endpoint":"https://hermes-api.pathao.com/alcaldia/orders","recipient_name":"John Doe","recipient_phone":"01700000000","city":1,"zone":10,"amount_to_collect":1500,"delivery_type":48}

[2024-01-15 14:30:23] local.INFO: Pathao Order Response Received {"order_id":12345,"status":200,"success":true}

[2024-01-15 14:30:23] local.INFO: Pathao Order Created Successfully {"order_id":12345,"consignment_id":"PTH-123456789","delivery_fee":60.00,"status":200}

[2024-01-15 14:30:24] local.INFO: Pathao: Processing Order Update {"order_id":12345,"consignment_id":"PTH-123456789","delivery_fee":60.00}

[2024-01-15 14:30:25] local.INFO: Pathao: SMS Sent Successfully {"order_id":12345,"phone":"01700000000"}
```

#### Status Update via Webhook

```
[2024-01-15 16:45:10] local.INFO: Pathao Webhook Status Update Received {"consignment_id":"PTH-123456789","status":"picked_up","message":"Package picked by rider","updated_at":"2024-01-15 16:45:10"}

[2024-01-15 16:45:10] local.INFO: Pathao Order Status Updated {"order_id":12345,"consignment_id":"PTH-123456789","old_status":"pending","new_status":"picked_up","updated_at":"2024-01-15 16:45:10"}
```

#### Token Refresh

```
[2024-01-15 14:30:22] local.WARNING: Pathao Token Expired - Refreshing {"order_id":12345,"old_token":"eyJ0eXAiOiJKV1QiLCJhbGc..."}

[2024-01-15 14:30:23] local.INFO: Pathao: Requesting new access token {"endpoint":"https://hermes-api.pathao.com/alcaldia/issue-token","client_id":"client_123","username":"merchant@domain.com"}

[2024-01-15 14:30:23] local.INFO: Pathao: New access token cached {"token_preview":"eyJ0eXAiOiJKV1QiLCJhbGc...","expires_at":"2024-01-22 14:30:23"}
```

#### Validation Error

```
[2024-01-15 14:35:11] local.ERROR: Pathao Order Validation Failed {"order_id":12346,"status":422,"errors":{"recipient_zone":["The selected zone is invalid."]}}
```

### Log Analysis Commands

#### Check Order Status Timeline

```bash
# Get complete timeline for an order
grep -E "consignment_id.*PTH-123456789|order_id.*12345" storage/logs/laravel.log | grep "Status"
```

#### Monitor Token Activity

```bash
# Check token refresh activity
grep -i "Token" storage/logs/laravel.log | grep Pathao
```

#### Count API Calls

```bash
# Count total Pathao API calls today
grep -c "Pathao.*Request" storage/logs/laravel-$(date +%Y-%m-%d).log

# Count successful orders
grep -c "Pathao Order Created Successfully" storage/logs/laravel-$(date +%Y-%m-%d).log

# Count errors
grep -c "Pathao.*ERROR" storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Clear Cache

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Clear specific cache
php artisan tinker
>>> Cache::forget('pathao_access_token');
>>> Cache::forget('city-list');
>>> Cache::forget('zone-list-1'); // Replace 1 with city_id
```

---

## File Reference

### Complete File Structure

```
project/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       └── PathaoCourier/
│   │   │           └── PathaoCourierController.php
│   │   ├── Middleware/
│   │   │   └── ValidatePathaoSignature.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── PathaoCourierRequest.php
│   │           └── PathaoWebhookRequest.php
│   ├── Repositories/
│   │   ├── Admin/
│   │   │   └── PathaoCourier/
│   │   │       └── PathaoCourierRepository.php
│   │   └── Interfaces/
│   │       └── Admin/
│   │           └── PathaoCourier/
│   │               └── PathaoCourierInterface.php
│   └── Services/
│       └── PathaoService.php
├── config/
│   └── pathao.php
├── resources/
│   └── views/
│       └── admin/
│           └── PathaoCourier/
│               └── index.blade.php
└── routes/
    └── admin.php
```

---

## SMS Notification

### Pathao Delivery SMS Template

The system sends SMS to customer when delivery is created:

```
Dear {customer_name},

Your order has been shipped via Pathao Courier.
Tracking ID: {trackingId}

Track your delivery at: https://pathao.com/track/{trackingId}

Thank you for shopping with us!
```

### SMS Service Integration

**Location**: `app/Services/ElitbuzzSmsService.php`

```php
public function pathaoSend($phone, $data)
{
    $message = "Dear " . $data['customer'] . ", Your order has been shipped via Pathao. Tracking ID: " . $data['trackingId'];

    $this->send($phone, $message);
}
```

---

## Security Best Practices

### 1. Environment Variables

Never hardcode credentials:

```php
// ❌ BAD
$host = 'https://hermes-api.pathao.com/alcaldia';
$clientId = 'your_client_id';

// ✅ GOOD
$host = config('pathao.host');
$clientId = config('pathao.client_id');
```

### 2. Webhook Signature Validation

Always verify webhook signature:

```php
// Middleware validates X-PATHAO-Signature header
// Against configured signature in .env
Route::post('pathao/status-update', [PathaoCourierController::class, 'updateStatus'])
    ->middleware('pathao.signature');
```

### 3. Access Token Caching

Cache tokens to avoid frequent API calls:

```php
// Token cached for 7 days
Cache::put('pathao_access_token', $accessToken, now()->addMinutes(60 * 24 * 7));
```

### 4. Input Validation

Validate all user inputs:

```php
// PathaoCourierRequest.php
public function rules()
{
    return [
        'phone' => 'required|numeric',
        'amount_to_collect' => 'required|numeric|min:0',
        'weight' => 'required|numeric|min:0',
    ];
}
```

---

## Support & Resources

### Official Pathao Resources

- **Website**: https://pathao.com/
- **Merchant Dashboard**: https://merchant.pathao.com/
- **API Documentation**: Contact Pathao support for API docs
- **Support Email**: support@pathao.com
- **Phone**: +880 1777 777 777

### Getting Help

For project-specific issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify configuration in `config/pathao.php`
3. Test API endpoints manually
4. Check environment variables in `.env`

---

## License & Disclaimer

This integration guide is for the Dolbear E-commerce Project. Pathao is a registered trademark of Pathao Limited.

**Last Updated**: January 2026
**Version**: 1.0