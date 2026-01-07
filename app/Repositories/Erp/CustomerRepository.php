<?php

namespace App\Repositories\Erp;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerRepository
{
    protected $client;

    public function __construct()
    {
        $this->header = [
            'Accept'        => 'application/json',
            'Authorization' => 'token '.config('erp.api_key'),
            'Content-Type' => 'application/json',
        ];
    }

    public function findByMobile($mobile)
    {
        info('finding by phone');

        try {
            $response = Http::withHeaders($this->header)
                ->get(config('erp.base_url') . '/api/resource/Customer', [
                    'fields'  => json_encode(["*"]),
                    'filters' => json_encode([["mobile_no", "=", $mobile]]),
                ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];

                info('result', $data);

                return $data[0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('ERP Customer Find Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function store($data)
    {
        try {
            $response = Http::withHeaders($this->header)
            ->post(config('erp.base_url').'/api/resource/Customer', $this->formatPayload($data));

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('ERP Customer Sync Failed (Create): ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('ERP Customer Sync Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function update(string $customerName, array $data)
    {
        try {
            $response = Http::withHeaders($this->header)
                ->put(config('erp.base_url') . '/api/resource/Customer/' . $customerName, $this->formatPayload($data));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ERP Customer Sync Failed (Update): ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('ERP Customer Sync Exception (Update): ' . $e->getMessage());
            return false;
        }
    }

    protected function formatPayload(array $data): array
    {
        $phone = $data['phone'] ?? '';
        if (str_starts_with($phone, '+88')) {
            $phone = substr($phone, 3);
        }

        return [
            "customer_name"   => $data['first_name'] .' '. $data['last_name'],
            "customer_group"  => $data['customer_group'] ?? "Online E-Commerce",
            "territory"       => $data['territory'] ?? "Online Channels",
            "mobile_no"       => $phone,
            "gender"          => $data['gender'] ?? '',
            "country"         => $data['country'] ?? "Bangladesh",
        ];
    }
}
