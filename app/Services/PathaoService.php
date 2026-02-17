<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    public function order($data)
    {
        try {
            // Build item description from order details
            $itemDescription = $this->buildItemDescription($data);

            $payload = [
                'store_id' => (int) config('pathao.store_id'),
                'merchant_order_id' => (string) $data->orderId,
                'recipient_name' => $data->name,
                'recipient_phone' => $this->getValidPhone($data->phone),
                'recipient_address' => $this->buildFullAddress($data),
                'delivery_type' => (int) $data->delivery_type,
                'item_type' => (int) $data->item_type,
                'item_quantity' => (int) $data->quantity,
                'item_weight' => (float) $data->weight,
                'amount_to_collect' => (int) ($data->amount_to_collect && !empty($data->amount_to_collect) ? $data->amount_to_collect : 0),
                'item_description' => $itemDescription,
            ];

            // Include recipient_city and recipient_zone from form
            // Form sends these as 'city' and 'zone' - map them to Pathao API fields
            if (!empty($data->city)) {
                $payload['recipient_city'] = (int) $data->city;
            }
            if (!empty($data->zone)) {
                $payload['recipient_zone'] = (int) $data->zone;
            }

            $response = Http::withHeaders($this->header)
                ->post(config('pathao.host') . '/orders', $payload);

            if ($response->status() === 401) {
                // Invalidate cache and retry with new token
                Cache::forget('pathao_access_token');
                $this->accessToken = $this->getAccessToken();
                $this->header['Authorization'] = 'Bearer ' . $this->accessToken;

                // Retry the request with new token
                $response = Http::withHeaders($this->header)
                    ->post(config('pathao.host') . '/orders', $payload);
            }

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
     * Build item description from order data
     * Generates detailed product list from order details
     */
    private function buildItemDescription($data)
    {
        // If custom description is provided, use it
        if (!empty($data->item_description)) {
            return $data->item_description;
        }

        // Build product description from order details
        $description = "Order #{$data->orderId}: ";
        $productNames = [];

        if (!empty($data->products) && is_array($data->products)) {
            foreach ($data->products as $product) {
                if (!empty($product['product_name'])) {
                    $qty = !empty($product['quantity']) ? " x {$product['quantity']}" : '';
                    $productNames[] = $product['product_name'] . $qty;
                }
            }
        }

        if (!empty($productNames)) {
            $description .= implode(', ', $productNames);
        } else {
            $description .= "E-commerce items";
        }

        // Add total amount info
        if (!empty($data->amount_to_collect) && $data->amount_to_collect > 0) {
            $description .= " | COD: {$data->amount_to_collect} BDT";
        }

        return $description;
    }

    /**
     * Build full address from shipping address components
     */
    private function buildFullAddress($data)
    {
        $addressParts = [];

        // Start with the main address field
        if (!empty($data->address)) {
            $addressParts[] = $data->address;
        }

        // Add district if available
        if (!empty($data->district)) {
            $addressParts[] = $data->district;
        }

        // Add thana if available
        if (!empty($data->thana)) {
            $addressParts[] = $data->thana;
        }

        // Always end with Bangladesh
        $addressParts[] = 'Bangladesh';

        return implode(', ', $addressParts);
    }

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

        // Request successful, extract access token
        $accessToken = $response->json()['access_token'];
        // Cache the access token
        Cache::put('pathao_access_token', $accessToken, now()->addMinutes(60 * 24 * 7)); // Cache for 7days
        return $accessToken;
    }


    public function getCity()
    {
        return Cache::remember('pathao_city_list', now()->addHours(24), function () {
            $response = Http::withHeaders($this->header)
                ->get(config('pathao.host') . '/city-list');

            if (!$response->successful()) {
                Log::error('Pathao City List API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return $response->json();
        });

    }

    public function getZone($data)
    {
        return Cache::rememberForever('zone-list-' . $data->city_id, function () use ($data) {
            $response = Http::withHeaders($this->header)
                ->get(config('pathao.host') . '/cities/' . $data->city_id . '/zone-list');

            return $response->json();
        });
    }

    private function getValidPhone($phone)
    {
        if (Str::contains($phone, '+88')) {
            return $phone = Str::replace('+88', '', $phone);
        }

        return $phone;
    }
}
