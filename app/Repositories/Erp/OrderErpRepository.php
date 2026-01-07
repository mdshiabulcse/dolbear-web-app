<?php

namespace App\Repositories\Erp;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderErpRepository
{
    protected $header;

    public function __construct()
    {
        $this->header = [
            'Accept' => 'application/json',
            'Authorization' => 'token ' . env('ERP_API_KEY'),
            'Content-Type' => 'application/json',
        ];
    }

    public function store($order)
    {
        try {
            $payload = $this->formatPayload($order);

            info('order payload', $payload);

            $response = Http::withHeaders($this->header)
                ->post(env('ERP_BASE_URL') . '/api/resource/Sales Invoice', $payload);

            if ($response->successful()) {

                $res = $response->json();

                if (isset($res['data']['name'])) {
                    $order->erp_code = $res['data']['name'];
                    $order->erp_sync = 1;
                    $order->save();
                }

                return $response->json();
            } else {
                Log::error('ERP Order Sync Failed (Create): ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('ERP Customer Sync Exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function formatPayload(Order $order): array
    {
        $orderItems = $order->orderDetails->map(function ($detail) {
            return [
                'item_code' => $detail->product->code,
                'item_name' => $detail->product->product_name,
                'qty' => $detail->quantity,
                'rate' => (int) $detail->price - (int) $detail->discount,
            ];
        })->toArray();

        $parts = [
            $order->shipping_address['name'] ?? null,
            $order->shipping_address['phone_no'] ?? null,
            $order->shipping_address['email'] ?? null,
            $order->shipping_address['address'] ?? null,
            $order->shipping_address['thana'] ?? null,
            $order->shipping_address['district'] ?? null,
            $order->shipping_address['division'] ?? null,
        ];

        $shippingAddress = implode(', ', array_filter($parts));

        return [
            'customer' => $order->user && $order->user->code ? $order->user->code : '',
            'order_type' => 'Shopping Cart',
            'is_pos' => 1,
            'pos_profile' => "Dolbear Online",
            'shipping_address' => $shippingAddress,
            'posting_date' => date('Y-m-d'),
            'po_date' => date('Y-m-d'),
            'po_no' => $order->code,
            'apply_discount_on' => "Net Total",
            'discount_amount' => $order->coupon_discount ?? 0,
            'items' => $orderItems,
            'shipping_address_name' => optional($order->user->deliveryAddress)->code,
            "payments" => [
                [
                    "mode_of_payment" => "Cash - Dolbear HQ",
                    "amount" => 0
                ]
            ]
        ];
    }

}
