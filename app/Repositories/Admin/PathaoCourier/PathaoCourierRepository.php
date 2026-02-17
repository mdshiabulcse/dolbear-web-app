<?php

namespace App\Repositories\Admin\PathaoCourier;

use App\Models\Order;
use App\Services\ElitbuzzSmsService;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\Admin\PathaoCourier\PathaoCourierInterface;

class PathaoCourierRepository implements PathaoCourierInterface
{

    public function index($id)
    {
        return true;
    }

    public function order($id, $data)
    {

    }

    public function updateStatus($data)
    {
        Log::info('Pathao Webhook Status Update Received', [
            'consignment_id' => $data->consignment_id,
            'order_status' => $data->order_status,
            'message' => $data->message ?? null,
            'updated_at' => $data->updated_at ?? null,
            'raw_data' => $data->all(),
        ]);

        $order = Order::where('pathao_delivery_id', $data->consignment_id)->first();

        if ($order) {
            $oldStatus = $order->delivery_status;

            // Update order status - use order_status from webhook
            $order->delivery_status = $data->order_status;
            $order->save();

            // Log status change
            Log::info('Pathao Order Status Updated', [
                'order_id' => $order->id,
                'consignment_id' => $order->pathao_delivery_id,
                'old_status' => $oldStatus,
                'new_status' => $data->order_status,
                'updated_at' => $order->updated_at->toDateTimeString(),
            ]);
        } else {
            // Log: Order not found
            Log::warning('Pathao Webhook: Order not found', [
                'consignment_id' => $data->consignment_id,
                'order_status' => $data->order_status,
            ]);
        }
    }

    public function processOrder($orderData, $deliverydata)
    {
        $orderData->delivery_type = 'pathao';
        $orderData->pathao_delivery_id = $deliverydata['data']['consignment_id'];
        $orderData->delivery_fee = $deliverydata['data']['delivery_fee'];
        $orderData->save();

        if(isset($orderData->billing_address['phone_no']) || isset($orderData->user->phone))
        {
            $smsService = new ElitbuzzSmsService();
            $smsService->pathaoSend($orderData->billing_address['phone_no'] ?? $orderData->user->phone, [
                'customer' => $orderData->billing_address['name'] ?? $orderData->user->first_name,
                'trackingId' => $orderData->pathao_delivery_id,
            ]);
        }

        return $deliverydata;
    }

}
