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

            if ($response->status() === 401) {
                // Invalidate cache access_token
                Cache::forget('pathao_access_token');
                $this->getAccessToken();
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
