<?php

namespace App\Repositories\Erp;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressRepository
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

    public function checkAddress($userCode)
    {
        info('checking address');

        try {
            $response = Http::withHeaders($this->header)
                ->get(config('erp.base_url') . '/api/resource/Address', [
                    'fields'  => json_encode(["*"]),
                    'filters' => json_encode([["Dynamic Link",  "link_name", "=", $userCode]]),
                ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];

                info('address result', $data);

                return $data[0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('ERP Address Find Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function store($data)
    {
        info('address create payload', $this->formatPayload($data));

        try {
            $response = Http::withHeaders($this->header)
            ->post(config('erp.base_url').'/api/resource/Address', $this->formatPayload($data));

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('ERP Address Sync Failed (Create): ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('ERP Address Sync Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function update(string $name, array $data)
    {
        info('update payload', $this->formatPayload($data));

        try {
            $response = Http::withHeaders($this->header)
                ->put(config('erp.base_url') . '/api/resource/Address/' . $name, $this->formatPayload($data));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ERP Address Sync Failed (Update): ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('ERP Address Sync Exception (Update): ' . $e->getMessage());
            return false;
        }
    }

    protected function formatPayload(array $data): array
    {
        return [
            'doctype'             => 'Address',
            'address_type'        => $data['address_type'] ?? 'Shipping',
            'country'             => $data['country'] ?? 'Bangladesh',
            'is_primary_address'  => $data['is_primary_address'] ?? 0,
            'is_shipping_address' => $data['is_shipping_address'] ?? 1,
            'disabled'            => $data['disabled'] ?? 0,
            'email_id'            => $data['email'] ?? '',
            'phone'               => $data['phone'] ?? '',
            'city'                => $data['city'] ?? '',
            'state'               => $data['state'] ?? '',
            'address_title'       => $data['address_title'] ?? '',
            'address_line1'       => $data['address_line'] ?? '',
            'address_line2'       => $data['address_line2'] ?? '',
            'links'               => [
                [
                    'doctype'      => 'Dynamic Link',
                    'link_doctype' => $data['link_doctype'] ?? 'Customer',
                    'link_name'    => $data['link_name'] ?? '',
                ],
            ]
        ];
    }

}
