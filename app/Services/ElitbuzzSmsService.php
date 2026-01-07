<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class ElitbuzzSmsService
{
    protected $apiKey;
    protected $senderId;
    protected $apiUrl;
    protected $type;

    public function __construct()
    {
        $this->apiUrl = config('elitbuzz.host');
        $this->type = config('elitbuzz.type');
        $this->apiKey = config('elitbuzz.api_key');
        $this->senderId = config('elitbuzz.sender_id');
    }

    public function orderConfirm($phone, $data)
    {
        $message = "Hi {$data['customer']}, Your Dolbear order has been confirmed and is ready to be shipped! \n"
            . "Tracking Number: {$data['tracking_number']}\n"
            . "Invoice: {$data['invoice_url']}\n\n"
            . "For Any Assistance, Call 01894971070 \n"
            . "Think Tech, Think Dolbear.";

        return $this->sendSms($phone, $message);
    }

    public function orderCreate($phone, $data)
    {
        $message = "Hi {$data['customer']}, your order has been placed successfully! \n"
            . "Order ID: {$data['tracking_number']}\n\n"
            . "Thank you for shopping with Dolbear. We’re processing your order and will update you soon.";

        return $this->sendSms($phone, $message);
    }

    public function resetPassword($phone, $data)
    {
        $message = "Your Dolbear verification code is: {$data['otp']} \n"
            . "(Valid for 5 minutes. Do not share this code with anyone.)\n"
            . "Helpline: https://www.facebook.com/dolbear.official or 01894971070.\n";

        return $this->sendSms($phone, $message);
    }

    public function login($phone, $data)
    {
        $message = "Your otp verification code is: " . $data['otp'];
        return $this->sendSms($phone, $message);
    }

    public function registration($phone, $data)
    {
        $message = "Your Dolbear verification code is: {$data['otp']} \n"
            . "(Valid for 5 minutes. Do not share this code with anyone.)\n"
            . "Helpline: https://www.facebook.com/dolbear.official or 01894971070.\n";

        return $this->sendSms($phone, $message);
    }


    public function pathaoSend($phone, $data)
    {
        $message = "Hi {$data['customer']}, your Dolbear order has been confirmed and is ready to be shipped!\n"
            . "Pathao Delivery ID: {$data['trackingId']}\n"
            . "For any assistance, call 01894971070\n"
            . "Think Tech, Think Dolbear.";

        return $this->sendSms($phone, $message);
    }

    public function sendSms($number, $message)
    {
        $formatPhone = $this->formatPhoneNumber($number);

        $response = Http::post($this->apiUrl.'/smsapi', [
            'api_key' => $this->apiKey,
            'type' => $this->type,
            'contacts' => $formatPhone,
            'senderid' => $this->senderId,
            'msg' => $message,
        ]);

        if (strpos($response->body(), "SMS SUBMITTED:") !== false) {
            return true;
        }

        return false;
    }

    private function formatPhoneNumber($number)
    {
        // Remove all non-numeric characters
        $number = preg_replace('/\D/', '', $number);

        // Remove leading 0s and add the country code if necessary
        if (substr($number, 0, 1) === '0') {
            $number = '880' . substr($number, 1);
        } elseif (substr($number, 0, 3) !== '880') {
            $number = '880' . $number;
        }

        return $number;
    }

}
