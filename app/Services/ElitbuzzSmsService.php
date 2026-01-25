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
            . "Thank you for shopping with Dolbear. We're processing your order and will update you soon.";

        return $this->sendSms($phone, $message);
    }

    public function resetPassword($phone, $data)
    {
        $otp = $data['otp'];

        // Developer: Log OTP for testing purposes
        \Log::info('🔔 DEVELOPER OTP - Reset Password', [
            'phone' => $phone,
            'otp' => $otp,
            'purpose' => 'password_reset',
            'message' => "Your Dolbear verification code is: {$otp}"
        ]);

        $message = "Your Dolbear verification code is: {$otp} \n"
            . "(Valid for 5 minutes. Do not share this code with anyone.)\n"
            . "Helpline: https://www.facebook.com/dolbear.official or 01894971070.\n";

        return $this->sendSms($phone, $message);
    }

    public function login($phone, $data)
    {
        $otp = $data['otp'];

        // Developer: Log OTP for testing purposes
        \Log::info('🔔 DEVELOPER OTP - Login', [
            'phone' => $phone,
            'otp' => $otp,
            'purpose' => 'login',
            'message' => "Your otp verification code is: {$otp}"
        ]);

        $message = "Your otp verification code is: " . $otp;
        return $this->sendSms($phone, $message);
    }

    public function registration($phone, $data)
    {
        $otp = $data['otp'];

        // Developer: Log OTP for testing purposes
        \Log::info('🔔 DEVELOPER OTP - Registration', [
            'phone' => $phone,
            'otp' => $otp,
            'purpose' => 'registration',
            'message' => "Your Dolbear verification code is: {$otp}"
        ]);

        $message = "Your Dolbear verification code is: {$otp} \n"
            . "(Valid for 5 minutes. Do not share this code with anyone.)\n"
            . "Helpline: https://www.facebook.com/dolbear.official or 01894971070.\n";

        return $this->sendSms($phone, $message);
    }


    public function pathaoSend($phone, $data)
    {
        $message = "Hi {$data['customer']}, Your Dolbear order has been confirmed and is ready to be shipped!\n"
            . "Pathao Delivery ID: {$data['trackingId']}\n"
            . "For any assistance, call 01894971070\n"
            . "Think Tech, Think Dolbear.";

        return $this->sendSms($phone, $message);
    }

    public function sendSms($number, $message)
    {
        $formatPhone = $this->formatPhoneNumber($number);

        try {
            // Build query parameters as per documentation
            $params = [
                'api_key' => $this->apiKey,
                'type' => $this->type,
                'contacts' => $formatPhone,
                'senderid' => $this->senderId,
                'msg' => $message,
                'label' => 'transactional', // Add label parameter
            ];

            $fullUrl = $this->apiUrl . '/smsapi?' . http_build_query($params);

            \Log::info('Elitbuzz SMS Request', [
                'phone' => $formatPhone,
                'url' => $fullUrl,
                'params' => $params,
            ]);

            // Make GET request as per documentation
            $response = Http::get($fullUrl);

            $body = trim($response->body());
            $statusCode = $response->status();

            // Log response for debugging
            \Log::info('Elitbuzz SMS API Response', [
                'phone' => $formatPhone,
                'status' => $statusCode,
                'body' => $body,
            ]);

            // Check for error codes
            if (is_numeric($body) && $body != '1000' && $body != '1101') {
                $errorMessages = [
                    '1002' => 'Sender ID/Masking Not Found',
                    '1003' => 'API Not Found',
                    '1004' => 'SPAM Detected',
                    '1005' => 'Internal Error',
                    '1006' => 'Internal Error',
                    '1007' => 'Balance Insufficient',
                    '1008' => 'Message is empty',
                    '1009' => 'Message Type Not Set',
                    '1010' => 'Invalid User & Password',
                    '1011' => 'Invalid User Id',
                    '1012' => 'Invalid Number',
                    '1013' => 'API limit error',
                    '1014' => 'No matching template',
                    '1015' => 'SMS Content Validation Fails',
                    '1016' => 'IP address not allowed - Please whitelist your server IP with SMS provider',
                    '1019' => 'SMS Purpose Missing',
                ];

                $errorMessage = $errorMessages[$body] ?? "Unknown error (Code: $body)";

                \Log::error('Elitbuzz SMS Error - ' . $errorMessage, [
                    'phone' => $formatPhone,
                    'error_code' => $body,
                    'error_message' => $errorMessage,
                ]);

                // Special handling for IP restriction (Error 1016)
                if ($body == '1016') {
                    \Log::error('IP ADDRESS NOT WHITELISTED - Contact SMS provider to whitelist your server IP', [
                        'phone' => $formatPhone,
                        'server_ip' => request()->ip(),
                        'action_required' => 'Contact support@elitbuzz-bd.com with your server IP',
                    ]);
                }

                return false;
            }

            // Check for success - HTTP 200 with numeric ID (1000 or 1101) or "SMS SUBMITTED"
            if ($statusCode == 200) {
                if (is_numeric($body) || strpos($body, 'SMS SUBMITTED') !== false) {
                    \Log::info('Elitbuzz SMS Sent Successfully', [
                        'phone' => $formatPhone,
                        'message_id' => $body
                    ]);
                    return true;
                }
            }

            // Log failure
            \Log::error('Elitbuzz SMS Failed', [
                'phone' => $formatPhone,
                'status' => $statusCode,
                'response' => $body,
            ]);

            return false;
        } catch (\Exception $e) {
            // Log exception
            \Log::error('Elitbuzz SMS Exception', [
                'phone' => $formatPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
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
