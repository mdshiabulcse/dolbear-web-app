<?php

namespace App\Repositories\Interfaces\Admin\PathaoCourier;

interface PathaoCourierInterface
{
    public function index($id);

    public function order($id, $request);

    public function updateStatus($request);

    public function processOrder($orderData, $deliverydata);
}
