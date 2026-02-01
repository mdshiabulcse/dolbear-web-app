<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Repositories\Interfaces\Admin\OrderInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SslCommerzPaymentController extends Controller
{
    protected $order;

    public function __construct(OrderInterface $order)
    {
        $this->order = $order;
    }

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    /**
     * Initiate SSLCOMMERZ Payment
     * This method is called from cart_new.vue after order confirmation
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        Log::info('SSLCOMMERZ Payment: Initiated', [
            'trx_id' => $request->input('trx_id'),
            'code' => $request->input('code'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            // Get transaction details from request
            $trx_id = $request->input('trx_id');
            $code = $request->input('code');

            if (!$trx_id) {
                Log::error('SSLCOMMERZ Payment: Missing transaction ID');
                return back()->with(['error' => 'Transaction ID is required']);
            }

            // Find orders by transaction ID
            $orders = $this->order->takePaymentOrder($trx_id);

            if (!$orders || count($orders) == 0) {
                Log::error('SSLCOMMERZ Payment: No orders found', ['trx_id' => $trx_id]);
                return back()->with(['error' => 'Order not found']);
            }

            Log::info('SSLCOMMERZ Payment: Orders found', [
                'trx_id' => $trx_id,
                'order_count' => count($orders),
            ]);

            // Get code from first order if not provided in request
            $first_order = $orders->first();
            if (empty($code)) {
                $code = $first_order->code;
                Log::info('SSLCOMMERZ Payment: Code retrieved from order', [
                    'trx_id' => $trx_id,
                    'code' => $code,
                ]);
            }

            // Get BDT currency
            $bdt_currency = $this->getCurrency();
            if (!$bdt_currency) {
                Log::error('SSLCOMMERZ Payment: BDT currency not configured');
                return back()->with(['error' => 'BDT currency not configured. Please contact admin.']);
            }

            // Calculate amount in BDT
            $active_currency = $this->activeCurrencyCheck();
            $amount_result = $this->amountCalculator($orders, $request->all(), $active_currency, $bdt_currency);
            $total_amount = round($amount_result['total_amount'], 2);

            Log::info('SSLCOMMERZ Payment: Amount calculated', [
                'trx_id' => $trx_id,
                'total_amount' => $total_amount,
                'currency' => 'BDT',
                'db_amount' => $amount_result['db_amount'],
            ]);

            // Get customer information from first order
            $customer_info = $this->getCustomerInfo($first_order);

            // Determine API domain (sandbox or production)
            $is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
            $api_domain = $is_sandbox
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com';

            Log::info('SSLCOMMERZ Payment: API Mode', [
                'is_sandbox' => $is_sandbox,
                'api_domain' => $api_domain,
            ]);

            // Prepare callback URLs - URL encode the code to handle # character
            $success_url = url('success?trx_id=' . $trx_id . '&code=' . urlencode($code));
            $fail_url = url('fail?trx_id=' . $trx_id);
            $cancel_url = url('cancel?trx_id=' . $trx_id);
            $ipn_url = url('ipn');

            Log::info('SSLCOMMERZ Payment: Callback URLs configured', [
                'success_url' => $success_url,
                'fail_url' => $fail_url,
                'cancel_url' => $cancel_url,
                'ipn_url' => $ipn_url,
            ]);

            // Get SSLCOMMERZ credentials from database settings
            $store_id = settingHelper('sslcommerz_id');
            $store_password = settingHelper('sslcommerz_password');

            if (empty($store_id) || empty($store_password)) {
                Log::error('SSLCOMMERZ Payment: Credentials not configured', [
                    'store_id_exists' => !empty($store_id),
                    'store_password_exists' => !empty($store_password),
                ]);
                return back()->with(['error' => 'SSLCOMMERZ not configured. Please contact admin.']);
            }

            // Prepare payment data for SSLCOMMERZ
            $post_data = array();
            $post_data['total_amount'] = $total_amount;
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = $trx_id;

            // CUSTOMER INFORMATION
            $post_data['cus_name'] = $customer_info['name'];
            $post_data['cus_email'] = $customer_info['email'];
            $post_data['cus_add1'] = $customer_info['address'];
            $post_data['cus_add2'] = $customer_info['address_2'] ?? "";
            $post_data['cus_city'] = $customer_info['city'] ?? "";
            $post_data['cus_state'] = $customer_info['state'] ?? "";
            $post_data['cus_postcode'] = $customer_info['postcode'] ?? "";
            $post_data['cus_country'] = $customer_info['country'] ?? "Bangladesh";
            $post_data['cus_phone'] = $customer_info['phone'];
            $post_data['cus_fax'] = "";

            // SHIPMENT INFORMATION
            $post_data['ship_name'] = $customer_info['name'];
            $post_data['ship_add1'] = $customer_info['shipping_address'] ?? $customer_info['address'];
            $post_data['ship_add2'] = $customer_info['shipping_address_2'] ?? "";
            $post_data['ship_city'] = $customer_info['shipping_city'] ?? "";
            $post_data['ship_state'] = $customer_info['shipping_state'] ?? "";
            $post_data['ship_postcode'] = $customer_info['shipping_postcode'] ?? "";
            $post_data['ship_phone'] = $customer_info['phone'];
            $post_data['ship_country'] = $customer_info['shipping_country'] ?? "Bangladesh";

            $post_data['shipping_method'] = "YES";
            $post_data['num_of_item'] = count($orders);
            $post_data['product_name'] = "Order Payment";
            $post_data['product_category'] = "E-commerce";
            $post_data['product_profile'] = "physical-goods";

            // OPTIONAL PARAMETERS - Store additional info for IPN
            $post_data['value_a'] = $code ?? ""; // Order code - will be returned in callbacks
            $post_data['value_b'] = ""; // Reserved for future use
            $post_data['value_c'] = ""; // Reserved for future use
            $post_data['value_d'] = ""; // Reserved for future use

            Log::info('SSLCOMMERZ Payment: Payment data prepared', [
                'trx_id' => $trx_id,
                'amount' => $total_amount,
                'customer_email' => $customer_info['email'],
                'customer_name' => $customer_info['name'],
            ]);

            // Add callback URLs to post_data for dynamic URLs with query parameters
            $post_data['success_url'] = $success_url;
            $post_data['fail_url'] = $fail_url;
            $post_data['cancel_url'] = $cancel_url;
            $post_data['ipn_url'] = $ipn_url;

            // Configure SSLCOMMERZ config dynamically - MUST be before creating SslCommerzNotification
            config(['sslcommerz.apiDomain' => $api_domain]);
            config(['sslcommerz.apiCredentials.store_id' => $store_id]);
            config(['sslcommerz.apiCredentials.store_password' => $store_password]);
            config(['sslcommerz.connect_from_localhost' => env('IS_LOCALHOST', false)]);

            // Initiate SSLCOMMERZ payment
            $sslc = new SslCommerzNotification();

            // Make payment request to SSLCOMMERZ
            $payment_options = $sslc->makePayment($post_data, 'hosted');

            if (!is_array($payment_options)) {
                // Success - redirect to SSLCOMMERZ gateway
                Log::info('SSLCOMMERZ Payment: Redirecting to gateway', [
                    'trx_id' => $trx_id,
                    'gateway_url' => $payment_options,
                ]);
                return redirect($payment_options);
            }

            // Error - array returned with error message
            Log::error('SSLCOMMERZ Payment: Failed to create payment', [
                'trx_id' => $trx_id,
                'response' => $payment_options,
            ]);
            return back()->with(['error' => 'Failed to initiate payment: ' . ($payment_options['message'] ?? 'Unknown error')]);

        } catch (\Exception $e) {
            Log::error('SSLCOMMERZ Payment: Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'trx_id' => $request->input('trx_id'),
            ]);
            return back()->with(['error' => 'Payment initiation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Payment via Ajax (Alternative method)
     */
    public function payViaAjax(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Success Callback - When payment is successful
     * User is redirected here after successful payment at SSLCOMMERZ gateway
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function success(Request $request)
    {
        Log::info('SSLCOMMERZ Success: Callback received', [
            'tran_id' => $request->input('tran_id'),
            'val_id' => $request->input('val_id'),
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'status' => $request->input('status'),
            'card_type' => $request->input('card_type'),
            'ip' => $request->ip(),
            'all_data' => $request->all(),
        ]);

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $code = $request->input('value_a'); // Get code from value_a parameter

        try {
            // Find orders
            $orders = $this->order->takePaymentOrder($tran_id);
            if (!$orders || count($orders) == 0) {
                Log::error('SSLCOMMERZ Success: Order not found', ['tran_id' => $tran_id]);
                return "Order not found. Please contact support.";
            }

            $first_order = $orders->first();

            // Check if already processed
            if ($first_order->is_completed == 1) {
                Log::info('SSLCOMMERZ Success: Order already completed', [
                    'tran_id' => $tran_id,
                    'redirecting_to_invoice' => true,
                ]);
                // Already completed - redirect to invoice
                if ($code) {
                    return redirect('get-invoice/' . $code)->with('success', 'Payment already completed.');
                } else {
                    return redirect('invoice/' . $tran_id)->with('success', 'Payment already completed.');
                }
            }

            // Validate transaction with SSLCOMMERZ API
            $is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
            $api_domain = $is_sandbox
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com';

            config(['sslcommerz.apiDomain' => $api_domain]);
            config(['sslcommerz.apiCredentials.store_id' => settingHelper('sslcommerz_id')]);
            config(['sslcommerz.apiCredentials.store_password' => settingHelper('sslcommerz_password')]);

            Log::info('SSLCOMMERZ Success: Validating transaction', [
                'tran_id' => $tran_id,
                'api_domain' => $api_domain,
            ]);

            $sslc = new SslCommerzNotification();
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);

            if ($validation) {
                Log::info('SSLCOMMERZ Success: Transaction validated successfully', [
                    'tran_id' => $tran_id,
                    'amount' => $amount,
                    'currency' => $currency,
                ]);

                // Update order status using the order repository
                $data = [
                    'trx_id' => $tran_id,
                    'payment_type' => 'ssl_commerze',
                    'card_type' => $request->input('card_type', ''),
                ];

                if ($code) {
                    $data['code'] = $code;
                }

                // Complete the order
                $user = $first_order->user ?? null;
                $offlineMethod = app('App\Repositories\Interfaces\Admin\Addon\OfflineMethodInterface');

                DB::beginTransaction();
                try {
                    $this->order->completeOrder($data, $user, $offlineMethod);
                    DB::commit();

                    Log::info('SSLCOMMERZ Success: Order completed successfully', [
                        'tran_id' => $tran_id,
                        'code' => $code,
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('SSLCOMMERZ Success: Failed to complete order', [
                        'error' => $e->getMessage(),
                        'tran_id' => $tran_id,
                    ]);
                    throw $e;
                }

                // Redirect to invoice
                if ($code) {
                    return redirect('get-invoice/' . $code)->with('success', 'Payment completed successfully!');
                } else {
                    return redirect('invoice/' . $tran_id)->with('success', 'Payment completed successfully!');
                }
            } else {
                Log::error('SSLCOMMERZ Success: Transaction validation failed', [
                    'tran_id' => $tran_id,
                    'error' => $sslc->error ?? 'Unknown error',
                ]);
                return redirect('payment')->with('error', 'Payment validation failed. Please contact support with transaction ID: ' . $tran_id);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SSLCOMMERZ Success: Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'tran_id' => $tran_id,
            ]);
            return redirect('payment')->with('error', 'Error processing payment. Please contact support with transaction ID: ' . $tran_id);
        }
    }

    /**
     * Fail Callback - When payment fails
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fail(Request $request)
    {
        Log::info('SSLCOMMERZ Fail: Callback received', [
            'tran_id' => $request->input('tran_id'),
            'status' => $request->input('status'),
            'error' => $request->input('error'),
            'ip' => $request->ip(),
            'all_data' => $request->all(),
        ]);

        $tran_id = $request->input('tran_id');

        try {
            $orders = $this->order->takePaymentOrder($tran_id);
            if ($orders && count($orders) > 0) {
                $first_order = $orders->first();

                // Update order status to failed if still pending
                if ($first_order->is_completed == 0) {
                    foreach ($orders as $order) {
                        $order->update(['is_completed' => -1]); // -1 = failed
                    }

                    Log::info('SSLCOMMERZ Fail: Order marked as failed', [
                        'tran_id' => $tran_id,
                        'order_count' => count($orders),
                    ]);
                }
            }

            return redirect('payment')->with('error', 'Payment failed. Transaction ID: ' . $tran_id . '. Please try again or contact support.');

        } catch (\Exception $e) {
            Log::error('SSLCOMMERZ Fail: Exception occurred', [
                'error' => $e->getMessage(),
                'tran_id' => $tran_id,
            ]);
            return redirect('payment')->with('error', 'Payment processing error. Transaction ID: ' . $tran_id);
        }
    }

    /**
     * Cancel Callback - When user cancels payment at gateway
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request)
    {
        Log::info('SSLCOMMERZ Cancel: Callback received', [
            'tran_id' => $request->input('tran_id'),
            'ip' => $request->ip(),
            'all_data' => $request->all(),
        ]);

        return redirect('payment')->with('info', 'Payment cancelled. You can try again when ready.');
    }

    /**
     * IPN (Instant Payment Notification) Handler
     * Server-to-server callback from SSLCOMMERZ
     * This is the PRIMARY method for updating order status reliably
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ipn(Request $request)
    {
        Log::info('SSLCOMMERZ IPN: Received callback', [
            'all_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tran_id' => $request->input('tran_id'),
            'val_id' => $request->input('val_id'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'card_type' => $request->input('card_type'),
        ]);

        try {
            $tran_id = $request->input('tran_id');

            if (!$tran_id) {
                Log::error('SSLCOMMERZ IPN: Missing transaction ID');
                return response()->json(['error' => 'Missing transaction ID'], 400);
            }

            // Find orders by transaction ID
            $orders = $this->order->takePaymentOrder($tran_id);

            if (!$orders || count($orders) == 0) {
                Log::warning('SSLCOMMERZ IPN: No orders found', [
                    'tran_id' => $tran_id,
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            $first_order = $orders->first();

            Log::info('SSLCOMMERZ IPN: Orders found', [
                'tran_id' => $tran_id,
                'order_count' => count($orders),
                'current_status' => $first_order->is_completed,
            ]);

            // Check if already processed - prevent duplicate processing
            if ($first_order->is_completed == 1) {
                Log::info('SSLCOMMERZ IPN: Order already completed - skipping', [
                    'tran_id' => $tran_id,
                ]);
                return response()->json(['success' => true, 'message' => 'Order already completed']);
            }

            // Get amount and currency for validation
            $bdt_currency = $this->getCurrency();
            $active_currency = $this->activeCurrencyCheck();
            $amount_result = $this->amountCalculator($orders, [], $active_currency, $bdt_currency);
            $amount = $amount_result['total_amount'];
            $currency = "BDT";

            // Configure SSLCOMMERZ for validation
            $is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
            $api_domain = $is_sandbox
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com';

            config(['sslcommerz.apiDomain' => $api_domain]);
            config(['sslcommerz.apiCredentials.store_id' => settingHelper('sslcommerz_id')]);
            config(['sslcommerz.apiCredentials.store_password' => settingHelper('sslcommerz_password')]);

            Log::info('SSLCOMMERZ IPN: Validating transaction', [
                'tran_id' => $tran_id,
                'amount' => $amount,
                'api_domain' => $api_domain,
            ]);

            // Validate transaction with SSLCOMMERZ API
            $sslc = new SslCommerzNotification();
            $post_data = $request->all();
            $validation = $sslc->orderValidate($post_data, $tran_id, $amount, $currency);

            if (!$validation) {
                Log::error('SSLCOMMERZ IPN: Transaction validation failed', [
                    'tran_id' => $tran_id,
                    'error' => $sslc->error ?? 'Unknown validation error',
                ]);
                return response()->json(['error' => 'Validation failed'], 400);
            }

            Log::info('SSLCOMMERZ IPN: Transaction validated successfully', [
                'tran_id' => $tran_id,
            ]);

            // Complete the order
            $code = $request->input('value_a'); // Get code from value_a parameter
            $data = [
                'trx_id' => $tran_id,
                'payment_type' => 'ssl_commerze',
                'card_type' => $request->input('card_type', ''),
            ];

            if ($code) {
                $data['code'] = $code;
            }

            $user = $first_order->user ?? null;
            $offlineMethod = app('App\Repositories\Interfaces\Admin\Addon\OfflineMethodInterface');

            DB::beginTransaction();
            try {
                $this->order->completeOrder($data, $user, $offlineMethod);
                DB::commit();

                Log::info('SSLCOMMERZ IPN: Order completed successfully', [
                    'tran_id' => $tran_id,
                    'code' => $code,
                    'card_type' => $request->input('card_type'),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('SSLCOMMERZ IPN: Failed to complete order', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'tran_id' => $tran_id,
                ]);
                return response()->json(['error' => 'Failed to complete order'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'IPN processed successfully',
                'tran_id' => $tran_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SSLCOMMERZ IPN: Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'tran_id' => $request->input('tran_id'),
            ]);

            return response()->json([
                'error' => 'IPN processing failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Get BDT currency from database
     */
    private function getCurrency()
    {
        return \App\Models\Currency::where('code', 'BDT')->first();
    }

    /**
     * Get active currency from system
     */
    private function activeCurrencyCheck($data = [])
    {
        $user_currency = currencyCheck();

        if (settingHelper('default_currency')) {
            $default_currency = settingHelper('default_currency');
        } else {
            $default_currency = 1;
        }

        // Try to get user's selected currency, fallback to default
        $currency = \App\Models\Currency::find($user_currency);
        if (!$currency) {
            $currency = \App\Models\Currency::find($default_currency);
        }

        return $currency;
    }

    /**
     * Calculate amount in BDT
     */
    private function amountCalculator($orders, $data, $active_currency, $bdt_currency)
    {
        $amount = 0;
        if ($orders && count($orders) > 0) {
            if ($active_currency && $bdt_currency) {
                // Convert to BDT if needed
                $amount = $orders->sum('total_payable') * $active_currency->exchange_rate / $bdt_currency->exchange_rate;
            } else {
                $amount = $orders->sum('total_payable');
            }
        }

        return [
            'total_amount' => $amount,
            'db_amount' => $orders->sum('total_payable') ?? 0
        ];
    }

    /**
     * Get customer information from order
     */
    private function getCustomerInfo($order)
    {
        $info = [
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address' => 'Dhaka, Bangladesh',
        ];

        // Try to get from order's billing address
        if ($order && $order->billing_address) {
            $billing = $order->billing_address;

            $info['name'] = $billing['name'] ?? $info['name'];
            $info['email'] = $billing['email'] ?? $info['email'];
            $info['phone'] = $billing['phone'] ?? $billing['phone_no'] ?? $info['phone'];
            $info['address'] = $billing['address'] ?? $info['address'];
            $info['address_2'] = $billing['address_2'] ?? '';
            $info['city'] = $billing['city_name'] ?? '';
            $info['state'] = $billing['state_name'] ?? '';
            $info['postcode'] = $billing['postal_code'] ?? '';
            $info['country'] = $billing['country_name'] ?? 'Bangladesh';
        }

        // Try to get from order's shipping address
        if ($order && $order->shipping_address) {
            $shipping = $order->shipping_address;

            $info['shipping_address'] = $shipping['address'] ?? $info['address'];
            $info['shipping_address_2'] = $shipping['address_2'] ?? '';
            $info['shipping_city'] = $shipping['city_name'] ?? '';
            $info['shipping_state'] = $shipping['state_name'] ?? '';
            $info['shipping_postcode'] = $shipping['postal_code'] ?? '';
            $info['shipping_country'] = $shipping['country_name'] ?? 'Bangladesh';
        } else {
            // Fallback to billing address for shipping
            $info['shipping_address'] = $info['address'];
            $info['shipping_city'] = $info['city'];
            $info['shipping_state'] = $info['state'];
            $info['shipping_postcode'] = $info['postcode'];
            $info['shipping_country'] = $info['country'];
        }

        return $info;
    }
}