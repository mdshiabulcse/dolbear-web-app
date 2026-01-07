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
        $order = Order::where('pathao_delivery_id', $data->consignment_id)->first();
        $order->save();
        Log::info("Pathao Order Status Updated", [$data->consignment_id]);
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
