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
        $endpoint = config('pathao.host') . '/orders';

        // Build special instruction with product information if available
        $specialInstruction = $data->special_instruction ?? '';
        if (!empty($data->product_info)) {
            $specialInstruction = trim($specialInstruction . "\n\nProducts:\n" . $data->product_info);
        }

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
            'amount_to_collect' => $data->amount_to_collect && !empty($data->amount_to_collect) ? $data->amount_to_collect : 0,
        ];

        // Add special_instruction if it has content
        if (!empty($specialInstruction)) {
            $requestData['special_instruction'] = $specialInstruction;
        }

        // Add item_description if provided
        if (!empty($data->item_description)) {
            $requestData['item_description'] = $data->item_description;
        }

        // Log: Complete request data being sent to Pathao
        Log::info('========== PATHAO ORDER REQUEST ==========', [
            'order_id' => $data->orderId,
            'endpoint' => $endpoint,
        ]);

        Log::info('PATHAO: Recipient Info', [
            'name' => $data->name,
            'phone' => $this->getValidPhone($data->phone),
            'address' => $data->address,
            'city_id' => $data->city,
            'zone_id' => $data->zone,
        ]);

        Log::info('PATHAO: Item Info', [
            'item_type' => $data->item_type,
            'item_quantity' => $data->quantity,
            'item_weight' => $data->weight . ' KG',
            'delivery_type' => $data->delivery_type,
            'amount_to_collect' => $requestData['amount_to_collect'],
        ]);

        if (!empty($specialInstruction)) {
            Log::info('PATHAO: Special Instruction', [
                'instruction' => $specialInstruction,
            ]);
        }

        Log::info('PATHAO: Complete Payload', $requestData);
        Log::info('==========================================');

        try {
            $response = Http::withHeaders($this->header)
                ->post($endpoint, $requestData);

            // Log: Response received
            Log::info('========== PATHAO RESPONSE ==========', [
                'order_id' => $data->orderId,
                'status_code' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->status() === 401) {
                Log::warning('PATHAO: Token Expired - Refreshing', [
                    'order_id' => $data->orderId,
                    'old_token' => substr($this->accessToken, 0, 20) . '...'
                ]);

                // Invalidate cache access_token
                Cache::forget('pathao_access_token');
                $this->getAccessToken();

                Log::info('PATHAO: Token Refreshed Successfully', [
                    'order_id' => $data->orderId,
                    'new_token' => substr($this->accessToken, 0, 20) . '...'
                ]);
            }

            if (intdiv($response->status(), 100) === 4) {
                $responseBody = $response->json();

                Log::error('PATHAO: Validation Failed', [
                    'order_id' => $data->orderId,
                    'status' => $response->status(),
                    'errors' => $responseBody['errors'] ?? [],
                    'full_response' => $responseBody,
                ]);

                $formattedError = [
                    'message' => 'Please fix the given errors',
                    'type' => 'error',
                    'code' => $response->status(),
                    'errors' => $responseBody['errors'] ?? [],
                ];

                throw new \Exception(json_encode($formattedError), $response->status());
            }

            $responseData = $response->json();

            // Log: Full response data
            Log::info('PATHAO: Response Body', $responseData);

            // Log: Success response
            if ($response->successful() && isset($responseData['data']['consignment_id'])) {
                Log::info('========== PATHAO SUCCESS ==========', [
                    'order_id' => $data->orderId,
                    'consignment_id' => $responseData['data']['consignment_id'] ?? null,
                    'delivery_fee' => $responseData['data']['delivery_fee'] ?? null,
                    'order_status' => $responseData['data']['order_status'] ?? null,
                ]);
                Log::info('====================================');
            }

            return $responseData;

        } catch (\Exception $e) {
            Log::error('========== PATHAO EXCEPTION ==========', [
                'order_id' => $data->orderId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Log::error('======================================');
            throw $e;
        }

    }

    private function getAccessToken()
    {
        if (Cache::has('pathao_access_token')) {
            return Cache::get('pathao_access_token');
        }

        $endpoint = config('pathao.host') . '/issue-token';
        $requestData = [
            'client_id' => config('pathao.client_id'),
            'client_secret' => config('pathao.client_secret'),
            'username' => config('pathao.username'),
            'password' => config('pathao.password'),
            'grant_type' => config('pathao.grant_type'),
        ];

        Log::info('Pathao: Requesting new access token', [
            'endpoint' => $endpoint,
            'client_id' => config('pathao.client_id'),
            'username' => config('pathao.username'),
        ]);

        $response = Http::post($endpoint, $requestData);

        if (!$response->successful()) {
            Log::error('Pathao: Failed to get access token', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new Exception('Failed to get Pathao access token');
        }

        // Request successful, extract access token
        $responseData = $response->json();
        $accessToken = $responseData['access_token'];

        // Cache the access token
        Cache::put('pathao_access_token', $accessToken, now()->addMinutes(60 * 24 * 7)); // Cache for 7days

        Log::info('Pathao: New access token cached', [
            'token_preview' => substr($accessToken, 0, 20) . '...',
            'expires_at' => now()->addMinutes(60 * 24 * 7)->toDateTimeString(),
        ]);

        return $accessToken;
    }


    public function getCity()
    {
        return Cache::rememberForever('city-list', function () {
            $response = Http::withHeaders($this->header)
                ->get(config('pathao.host') . '/countries/1/city-list');

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
