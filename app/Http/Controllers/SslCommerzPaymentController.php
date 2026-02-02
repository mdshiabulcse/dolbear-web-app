<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Repositories\Interfaces\Admin\OrderInterface;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentLogService;

/**
 * SSLCOMMERZ Payment Controller
 *
 * Handles all SSLCOMMERZ payment gateway operations including:
 * - Payment initiation
 * - Success/Fail/Cancel callbacks
 * - IPN (Instant Payment Notification) processing
 *
 * @package App\Http\Controllers
 */
class SslCommerzPaymentController extends Controller
{
    /** @var OrderInterface Order repository */
    protected $order;

    /** @var PaymentLogService Payment logging service */
    protected $paymentLog;

    /**
     * Constructor - Inject dependencies
     */
    public function __construct(OrderInterface $order, PaymentLogService $paymentLog)
    {
        $this->order = $order;
        $this->paymentLog = $paymentLog;
    }

    /**
     * Example: Easy Checkout Page
     * Displays a simple checkout example page
     */
    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    /**
     * Example: Hosted Checkout Page
     * Displays a hosted checkout example page
     */
    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    /**
     * Initiate SSLCOMMERZ Payment
     *
     * Called from cart_new.vue after order confirmation
     * Validates order, prepares payment data, and redirects to SSLCOMMERZ gateway
     *
     * @param Request $request Contains trx_id (transaction ID) and code (order code)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Get transaction details
        $trx_id = $request->input('trx_id');
        $code = $request->input('code');

        try {
            // ─────────────────────────────────────────────────────────────
            // STEP 1: Validate input
            // ─────────────────────────────────────────────────────────────
            if (!$trx_id) {
                Log::error('SSLCOMMERZ: Missing transaction ID', ['ip' => $request->ip()]);
                $this->paymentLog->logError([
                    'gateway' => 'sslcommerz',
                    'error_code' => 'MISSING_TRX_ID',
                    'error_message' => 'Transaction ID is required',
                    'ip_address' => $request->ip(),
                ]);
                return back()->with(['error' => 'Transaction ID is required']);
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 2: Find orders by transaction ID
            // ─────────────────────────────────────────────────────────────
            $orders = $this->order->takePaymentOrder($trx_id);

            if (!$orders || count($orders) == 0) {
                Log::error('SSLCOMMERZ: Order not found', ['trx_id' => $trx_id, 'ip' => $request->ip()]);
                $this->paymentLog->logError([
                    'gateway' => 'sslcommerz',
                    'trx_id' => $trx_id,
                    'error_code' => 'ORDER_NOT_FOUND',
                    'error_message' => 'Order not found for transaction ID: ' . $trx_id,
                    'ip_address' => $request->ip(),
                ]);
                return back()->with(['error' => 'Order not found']);
            }

            $first_order = $orders->first();
            $code = $code ?: $first_order->code; // Use order code if not provided

            // ─────────────────────────────────────────────────────────────
            // STEP 3: Calculate amount in BDT
            // ─────────────────────────────────────────────────────────────
            $bdt_currency = $this->getCurrency();
            if (!$bdt_currency) {
                Log::error('SSLCOMMERZ: BDT currency not configured');
                $this->paymentLog->logError([
                    'gateway' => 'sslcommerz',
                    'error_code' => 'CURRENCY_NOT_FOUND',
                    'error_message' => 'BDT currency not configured',
                ]);
                return back()->with(['error' => 'BDT currency not configured. Please contact admin.']);
            }

            $active_currency = $this->activeCurrencyCheck();
            $amount_result = $this->amountCalculator($orders, $request->all(), $active_currency, $bdt_currency);
            $total_amount = round($amount_result['total_amount'], 2);

            // ─────────────────────────────────────────────────────────────
            // STEP 4: Get customer information
            // ─────────────────────────────────────────────────────────────
            $customer_info = $this->getCustomerInfo($first_order);

            // ─────────────────────────────────────────────────────────────
            // STEP 5: Configure SSLCOMMERZ API
            // ─────────────────────────────────────────────────────────────
            $is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
            $api_domain = $is_sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';
            $environment = $is_sandbox ? 'sandbox' : 'production';

            $store_id = settingHelper('sslcommerz_id');
            $store_password = settingHelper('sslcommerz_password');

            if (empty($store_id) || empty($store_password)) {
                Log::error('SSLCOMMERZ: Credentials not configured');
                $this->paymentLog->logError([
                    'gateway' => 'sslcommerz',
                    'error_code' => 'CREDENTIALS_MISSING',
                    'error_message' => 'SSLCOMMERZ credentials not configured',
                    'environment' => $environment,
                ]);
                return back()->with(['error' => 'SSLCOMMERZ not configured. Please contact admin.']);
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 6: Generate gateway transaction ID
            // ─────────────────────────────────────────────────────────────
            $sslcommerz_tran_id = date('YmdHis') . rand(1000, 9999); // Add random suffix to prevent collisions

            // Save to orders for IPN matching
            foreach ($orders as $order) {
                $order->gateway_tran_id = $sslcommerz_tran_id;
                $order->save();
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 7: Prepare payment data
            // ─────────────────────────────────────────────────────────────
            $success_url = url('success?trx_id=' . $trx_id . '&code=' . urlencode($code));
            $fail_url = url('fail?trx_id=' . $trx_id);
            $cancel_url = url('cancel?trx_id=' . $trx_id);
            $ipn_url = url('ipn');

            $post_data = [
                'total_amount' => $total_amount,
                'currency' => 'BDT',
                'tran_id' => $sslcommerz_tran_id,
                'success_url' => $success_url,
                'fail_url' => $fail_url,
                'cancel_url' => $cancel_url,
                'ipn_url' => $ipn_url,
                // Customer Info
                'cus_name' => $customer_info['name'],
                'cus_email' => $customer_info['email'],
                'cus_add1' => $customer_info['address'],
                'cus_add2' => $customer_info['address_2'] ?? '',
                'cus_city' => $customer_info['city'] ?? '',
                'cus_state' => $customer_info['state'] ?? '',
                'cus_postcode' => $customer_info['postcode'] ?? '',
                'cus_country' => $customer_info['country'] ?? 'Bangladesh',
                'cus_phone' => $customer_info['phone'],
                'cus_fax' => '',
                // Shipping Info
                'ship_name' => $customer_info['name'],
                'ship_add1' => $customer_info['shipping_address'] ?? $customer_info['address'],
                'ship_add2' => $customer_info['shipping_address_2'] ?? '',
                'ship_city' => $customer_info['shipping_city'] ?? '',
                'ship_state' => $customer_info['shipping_state'] ?? '',
                'ship_postcode' => $customer_info['shipping_postcode'] ?? '',
                'ship_country' => $customer_info['shipping_country'] ?? 'Bangladesh',
                'ship_phone' => $customer_info['phone'],
                // Product Info
                'shipping_method' => 'YES',
                'num_of_item' => count($orders),
                'product_name' => 'Order Payment',
                'product_category' => 'E-commerce',
                'product_profile' => 'physical-goods',
                // Optional parameters (returned in callbacks)
                'value_a' => $code, // Order code
                'value_b' => '',
                'value_c' => '',
                'value_d' => '',
            ];

            // ─────────────────────────────────────────────────────────────
            // STEP 8: Log payment initiation
            // ─────────────────────────────────────────────────────────────
            $this->paymentLog->logInitiation([
                'order_id' => $first_order->id,
                'trx_id' => $trx_id,
                'gateway_tran_id' => $sslcommerz_tran_id,
                'order_code' => $code,
                'gateway' => 'sslcommerz',
                'amount' => $total_amount,
                'currency' => 'BDT',
                'environment' => $environment,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'initiated_at' => now(),
            ]);

            Log::info('SSLCOMMERZ: Payment initiated', [
                'trx_id' => $trx_id,
                'gateway_tran_id' => $sslcommerz_tran_id,
                'amount' => $total_amount,
                'environment' => $environment,
            ]);

            // ─────────────────────────────────────────────────────────────
            // STEP 9: Call SSLCOMMERZ API
            // ─────────────────────────────────────────────────────────────
            config(['sslcommerz.apiDomain' => $api_domain]);
            config(['sslcommerz.apiCredentials.store_id' => $store_id]);
            config(['sslcommerz.apiCredentials.store_password' => $store_password]);
            config(['sslcommerz.connect_from_localhost' => env('IS_LOCALHOST', false)]);

            $sslc = new SslCommerzNotification();
            $payment_options = $sslc->makePayment($post_data, 'hosted');

            // ─────────────────────────────────────────────────────────────
            // STEP 10: Handle response
            // ─────────────────────────────────────────────────────────────
            if (!is_array($payment_options)) {
                // Success - redirect to SSLCOMMERZ gateway
                $this->paymentLog->logRedirect([
                    'order_id' => $first_order->id,
                    'trx_id' => $trx_id,
                    'gateway_tran_id' => $sslcommerz_tran_id,
                    'gateway' => 'sslcommerz',
                    'environment' => $environment,
                    'notes' => 'Redirected to gateway: ' . $payment_options,
                ]);

                Log::info('SSLCOMMERZ: Redirecting to gateway', [
                    'trx_id' => $trx_id,
                    'gateway_url' => $payment_options,
                ]);

                return redirect($payment_options);
            }

            // Error - SSLCOMMERZ returned error array
            $error_msg = $payment_options['failedreason'] ?? $payment_options['message'] ?? 'Unknown error';

            Log::error('SSLCOMMERZ: Failed to create payment', [
                'trx_id' => $trx_id,
                'error' => $error_msg,
            ]);

            $this->paymentLog->logError([
                'order_id' => $first_order->id,
                'trx_id' => $trx_id,
                'gateway_tran_id' => $sslcommerz_tran_id,
                'gateway' => 'sslcommerz',
                'error_code' => 'GATEWAY_ERROR',
                'error_message' => $error_msg,
                'response_data' => $payment_options,
                'environment' => $environment,
            ]);

            return back()->with(['error' => 'Failed to initiate payment: ' . $error_msg]);

        } catch (\Exception $e) {
            Log::error('SSLCOMMERZ: Exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trx_id' => $trx_id ?? 'N/A',
            ]);

            $this->paymentLog->logError([
                'trx_id' => $trx_id ?? 'N/A',
                'gateway' => 'sslcommerz',
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
                'error_code' => 'LINE_' . $e->getLine(),
            ]);

            return back()->with(['error' => 'Payment initiation failed. Please try again.']);
        }
    }

    /**
     * Payment via Ajax (Alternative method)
     * Simply calls the index method for AJAX requests
     */
    public function payViaAjax(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Success Callback - Payment Completed Successfully
     *
     * User is redirected here after successful payment at SSLCOMMERZ gateway
     * Validates transaction and completes the order
     *
     * @param Request $request Contains SSLCOMMERZ response data
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        // Extract SSLCOMMERZ response data
        $tran_id = $request->input('tran_id'); // SSLCommerz gateway transaction ID
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $code = $request->input('value_a'); // Order code from value_a
        $internal_trx_id = $request->input('trx_id'); // Our internal trx_id
        $url_code = $request->input('code'); // Code from URL

        // Decode URL code if needed
        if (empty($code) && !empty($url_code)) {
            $code = urldecode($url_code);
        }

        Log::info('SSLCOMMERZ: Success callback received', [
            'gateway_tran_id' => $tran_id,
            'amount' => $amount,
            'currency' => $currency,
        ]);

        try {
            // ─────────────────────────────────────────────────────────────
            // STEP 1: Find order (try multiple methods)
            // ─────────────────────────────────────────────────────────────
            $orders = null;

            // Method 1: By gateway transaction ID (PRIMARY)
            if (!empty($tran_id)) {
                $orders = $this->order->takePaymentOrderByGatewayTranId($tran_id);
            }

            // Method 2: By order code (fallback)
            if ((!$orders || count($orders) == 0) && !empty($code)) {
                $orders = $this->order->orderByCodes($code);
            }

            // Method 3: By internal trx_id (fallback)
            if ((!$orders || count($orders) == 0) && !empty($internal_trx_id)) {
                $orders = $this->order->takePaymentOrder($internal_trx_id);
            }

            if (!$orders || count($orders) == 0) {
                Log::error('SSLCOMMERZ: Order not found in success callback', [
                    'gateway_tran_id' => $tran_id,
                    'code' => $code,
                ]);

                $this->paymentLog->logError([
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'error_code' => 'ORDER_NOT_FOUND',
                    'error_message' => 'Order not found after successful payment',
                    'request_data' => $request->all(),
                ]);

                return redirect('/')->with('error', 'Payment was successful but we could not find your order. Please contact support with: ' . ($code ?? 'N/A'));
            }

            $first_order = $orders->first();

            // ─────────────────────────────────────────────────────────────
            // STEP 2: Log success callback received
            // ─────────────────────────────────────────────────────────────
            $this->paymentLog->logSuccessCallback([
                'order_id' => $first_order->id,
                'trx_id' => $first_order->trx_id,
                'gateway_tran_id' => $tran_id,
                'order_code' => $code,
                'gateway' => 'sslcommerz',
                'amount' => $amount,
                'currency' => $currency,
                'val_id' => $request->input('val_id'),
                'card_type' => $request->input('card_type'),
                'status' => $request->input('status'),
                'request_data' => $request->all(),
                'gateway_responded_at' => now(),
            ]);

            // ─────────────────────────────────────────────────────────────
            // STEP 3: Check if already completed (duplicate prevention)
            // ─────────────────────────────────────────────────────────────
            if ($first_order->status == 1) {
                Log::info('SSLCOMMERZ: Order already completed', ['order_code' => $first_order->code]);

                $this->paymentLog->logDuplicatePrevented([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'notes' => 'Success callback - order already completed by IPN',
                ]);

                // Store order code in session for frontend to load invoice
                session()->put('last_order_code', $first_order->code);
                return redirect('payment')->with('success', 'Payment already completed! Thank you for your order.');
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 4: Validate transaction with SSLCOMMERZ API
            // ─────────────────────────────────────────────────────────────
            $is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
            $api_domain = $is_sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';

            config(['sslcommerz.apiDomain' => $api_domain]);
            config(['sslcommerz.apiCredentials.store_id' => settingHelper('sslcommerz_id')]);
            config(['sslcommerz.apiCredentials.store_password' => settingHelper('sslcommerz_password')]);

            $sslc = new SslCommerzNotification();
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);

            if (!$validation) {
                $error = $sslc->error ?? 'Validation failed';
                Log::error('SSLCOMMERZ: Validation failed', ['tran_id' => $tran_id, 'error' => $error]);

                $this->paymentLog->logValidationFailed([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'error_message' => $error,
                    'validation_data' => $request->all(),
                ]);

                return redirect('payment')->with('error', 'Payment validation failed. Please contact support with: ' . $tran_id);
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 5: Validation successful - log it
            // ─────────────────────────────────────────────────────────────
            $this->paymentLog->logValidationSuccess([
                'order_id' => $first_order->id,
                'trx_id' => $first_order->trx_id,
                'gateway_tran_id' => $tran_id,
                'gateway' => 'sslcommerz',
                'validation_data' => [
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'VALIDATED',
                ],
            ]);

            Log::info('SSLCOMMERZ: Transaction validated', [
                'gateway_tran_id' => $tran_id,
                'order_code' => $first_order->code,
            ]);

            // ─────────────────────────────────────────────────────────────
            // STEP 6: Check again if already completed (race condition protection)
            // ─────────────────────────────────────────────────────────────
            if ($first_order->status == 1) {
                $this->paymentLog->logDuplicatePrevented([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'notes' => 'Success callback after validation - order already completed by IPN',
                ]);

                // Store order code in session for frontend to load invoice
                session()->put('last_order_code', $first_order->code);
                return redirect('payment')->with('success', 'Payment completed successfully!');
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 7: Complete the order
            // ─────────────────────────────────────────────────────────────
            $data = [
                'trx_id' => $first_order->trx_id,
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

                // ─────────────────────────────────────────────────────────
                // STEP 8: Log order completion
                // ─────────────────────────────────────────────────────────
                $this->paymentLog->logOrderCompleted([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'order_code' => $code,
                    'gateway' => 'sslcommerz',
                    'amount' => $amount,
                    'currency' => 'BDT',
                    'card_type' => $request->input('card_type'),
                    'completed_at' => now(),
                ]);

                Log::info('SSLCOMMERZ: Order completed', [
                    'order_code' => $first_order->code,
                    'gateway_tran_id' => $tran_id,
                ]);

                // Store order code in session for frontend to load invoice
                session()->put('last_order_code', $first_order->code);
                return redirect('payment')->with('success', 'Payment completed successfully!');

            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('SSLCOMMERZ: Failed to complete order', [
                    'error' => $e->getMessage(),
                    'gateway_tran_id' => $tran_id,
                ]);

                $this->paymentLog->logError([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'error_code' => 'ORDER_COMPLETION_FAILED',
                    'error_message' => $e->getMessage(),
                ]);

                // Store order code in session for frontend to load invoice
                session()->put('last_order_code', $first_order->code);
                return redirect('payment')->with('warning', 'Payment was successful but there was an issue completing your order. Please contact support with: ' . $first_order->code);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SSLCOMMERZ: Exception in success callback', [
                'error' => $e->getMessage(),
                'gateway_tran_id' => $tran_id,
            ]);

            $this->paymentLog->logError([
                'gateway_tran_id' => $tran_id ?? 'N/A',
                'gateway' => 'sslcommerz',
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
            ]);

            return redirect('payment')->with('error', 'Error processing payment. Please contact support with: ' . ($tran_id ?? 'N/A'));
        }
    }

    /**
     * Fail Callback - Payment Failed
     *
     * Called when payment fails at SSLCOMMERZ gateway
     * Updates order status to failed
     *
     * @param Request $request Contains failure details
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $error_reason = $request->input('error') ?? 'Payment failed at gateway';

        Log::warning('SSLCOMMERZ: Fail callback received', [
            'tran_id' => $tran_id,
            'error' => $error_reason,
        ]);

        $this->paymentLog->logFailCallback([
            'trx_id' => $tran_id,
            'gateway_tran_id' => $tran_id,
            'gateway' => 'sslcommerz',
            'error_message' => $error_reason,
            'status' => $request->input('status'),
            'request_data' => $request->all(),
            'gateway_responded_at' => now(),
        ]);

        try {
            // Try to find and update order
            $orders = $this->order->takePaymentOrderByGatewayTranId($tran_id);

            if (!$orders || count($orders) == 0) {
                $orders = $this->order->takePaymentOrder($request->input('trx_id'));
            }

            if ($orders && count($orders) > 0) {
                $first_order = $orders->first();

                // Mark as failed if still pending
                if ($first_order->is_completed == 0) {
                    foreach ($orders as $order) {
                        $order->update(['is_completed' => -1]); // -1 = failed
                    }

                    $this->paymentLog->logOrderFailed([
                        'order_id' => $first_order->id,
                        'trx_id' => $first_order->trx_id,
                        'gateway_tran_id' => $tran_id,
                        'gateway' => 'sslcommerz',
                        'error_message' => $error_reason,
                        'notes' => 'Order marked as failed due to payment failure',
                    ]);

                    Log::info('SSLCOMMERZ: Order marked as failed', ['tran_id' => $tran_id]);
                }
            }

        } catch (\Exception $e) {
            Log::error('SSLCOMMERZ: Exception in fail callback', [
                'error' => $e->getMessage(),
                'tran_id' => $tran_id,
            ]);
        }

        return redirect('payment')->with('error', 'Payment failed. Please try again or contact support.');
    }

    /**
     * Cancel Callback - User Cancelled Payment
     *
     * Called when user cancels payment at SSLCOMMERZ gateway
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        Log::info('SSLCOMMERZ: Cancel callback received', ['tran_id' => $tran_id]);

        $this->paymentLog->logCancelCallback([
            'trx_id' => $tran_id,
            'gateway_tran_id' => $tran_id,
            'gateway' => 'sslcommerz',
            'notes' => 'User cancelled payment at gateway',
            'gateway_responded_at' => now(),
        ]);

        return redirect('payment')->with('info', 'Payment cancelled. You can try again when ready.');
    }

    /**
     * IPN (Instant Payment Notification) Handler
     *
     * Server-to-server callback from SSLCOMMERZ
     * This is the PRIMARY and MOST RELIABLE method for payment confirmation
     *
     * IMPORTANT: IPN works even if user closes browser after payment
     *
     * @param Request $request Contains SSLCOMMERZ IPN data
     * @return \Illuminate\Http\JsonResponse
     */
    public function ipn(Request $request)
    {
        $tran_id = $request->input('tran_id');

        Log::info('SSLCOMMERZ: IPN received', [
            'gateway_tran_id' => $tran_id,
            'ip' => $request->ip(),
        ]);

        // ─────────────────────────────────────────────────────────────
        // STEP 1: Log IPN received
        // ─────────────────────────────────────────────────────────────
        $this->paymentLog->logIpnReceived([
            'gateway_tran_id' => $tran_id,
            'gateway' => 'sslcommerz',
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'card_type' => $request->input('card_type'),
            'request_data' => $request->all(),
            'gateway_responded_at' => now(),
        ]);

        try {
            if (!$tran_id) {
                Log::error('SSLCOMMERZ IPN: Missing transaction ID');
                return response()->json(['error' => 'Missing transaction ID'], 400);
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 2: Find order by gateway transaction ID
            // ─────────────────────────────────────────────────────────────
            $orders = $this->order->takePaymentOrderByGatewayTranId($tran_id);

            if (!$orders || count($orders) == 0) {
                Log::warning('SSLCOMMERZ IPN: Order not found', ['gateway_tran_id' => $tran_id]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            $first_order = $orders->first();

            // ─────────────────────────────────────────────────────────────
            // STEP 3: Check if already processed (duplicate prevention)
            // ─────────────────────────────────────────────────────────────
            if ($first_order->status == 1) {
                Log::info('SSLCOMMERZ IPN: Order already completed - skipping', [
                    'order_code' => $first_order->code,
                ]);

                $this->paymentLog->logDuplicatePrevented([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'notes' => 'IPN - order already completed',
                ]);

                return response()->json(['success' => true, 'message' => 'Order already completed']);
            }

            // ─────────────────────────────────────────────────────────────
            // STEP 4: Calculate amount for validation
            // ─────────────────────────────────────────────────────────────
            $bdt_currency = $this->getCurrency();
            $active_currency = $this->activeCurrencyCheck();
            $amount_result = $this->amountCalculator($orders, [], $active_currency, $bdt_currency);
            $amount = $amount_result['total_amount'];

            // ─────────────────────────────────────────────────────────────
            // STEP 5: Configure SSLCOMMERZ API
            // ─────────────────────────────────────────────────────────────
            $is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
            $api_domain = $is_sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';

            config(['sslcommerz.apiDomain' => $api_domain]);
            config(['sslcommerz.apiCredentials.store_id' => settingHelper('sslcommerz_id')]);
            config(['sslcommerz.apiCredentials.store_password' => settingHelper('sslcommerz_password')]);

            // ─────────────────────────────────────────────────────────────
            // STEP 6: Validate transaction with SSLCOMMERZ API
            // ─────────────────────────────────────────────────────────────
            $sslc = new SslCommerzNotification();
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, 'BDT');

            if (!$validation) {
                $error = $sslc->error ?? 'Validation failed';
                Log::error('SSLCOMMERZ IPN: Validation failed', [
                    'tran_id' => $tran_id,
                    'error' => $error,
                ]);

                $this->paymentLog->logValidationFailed([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'error_message' => $error,
                    'validation_data' => $request->all(),
                ]);

                return response()->json(['error' => 'Validation failed'], 400);
            }

            Log::info('SSLCOMMERZ IPN: Transaction validated', ['tran_id' => $tran_id]);

            // ─────────────────────────────────────────────────────────────
            // STEP 7: Prepare order completion data
            // ─────────────────────────────────────────────────────────────
            $code = $request->input('value_a'); // Order code from value_a
            $data = [
                'trx_id' => $first_order->trx_id,
                'payment_type' => 'ssl_commerze',
                'card_type' => $request->input('card_type', ''),
            ];

            if ($code) {
                $data['code'] = $code;
            }

            $user = $first_order->user ?? null;
            $offlineMethod = app('App\Repositories\Interfaces\Admin\Addon\OfflineMethodInterface');

            // ─────────────────────────────────────────────────────────────
            // STEP 8: Complete the order
            // ─────────────────────────────────────────────────────────────
            DB::beginTransaction();
            try {
                $this->order->completeOrder($data, $user, $offlineMethod);
                DB::commit();

                // ─────────────────────────────────────────────────────────
                // STEP 9: Log IPN processed and order completed
                // ─────────────────────────────────────────────────────────
                $this->paymentLog->logIpnProcessed([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'order_code' => $code,
                    'gateway' => 'sslcommerz',
                    'amount' => $amount,
                    'currency' => 'BDT',
                    'card_type' => $request->input('card_type'),
                    'completed_at' => now(),
                ]);

                Log::info('SSLCOMMERZ IPN: Order completed', [
                    'order_code' => $first_order->code,
                    'gateway_tran_id' => $tran_id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'IPN processed successfully',
                    'tran_id' => $tran_id,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('SSLCOMMERZ IPN: Failed to complete order', [
                    'error' => $e->getMessage(),
                    'tran_id' => $tran_id,
                ]);

                $this->paymentLog->logError([
                    'order_id' => $first_order->id,
                    'trx_id' => $first_order->trx_id,
                    'gateway_tran_id' => $tran_id,
                    'gateway' => 'sslcommerz',
                    'error_code' => 'ORDER_COMPLETION_FAILED',
                    'error_message' => $e->getMessage(),
                ]);

                return response()->json(['error' => 'Failed to complete order'], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SSLCOMMERZ IPN: Exception', [
                'error' => $e->getMessage(),
                'tran_id' => $tran_id,
            ]);

            $this->paymentLog->logError([
                'gateway_tran_id' => $tran_id ?? 'N/A',
                'gateway' => 'sslcommerz',
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'IPN processing failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get BDT currency from database
     *
     * @return \App\Models\Currency|null
     */
    private function getCurrency()
    {
        return \App\Models\Currency::where('code', 'BDT')->first();
    }

    /**
     * Get active currency from system
     *
     * @return \App\Models\Currency|null
     */
    private function activeCurrencyCheck()
    {
        $user_currency = currencyCheck();
        $default_currency = settingHelper('default_currency') ?? 1;

        $currency = \App\Models\Currency::find($user_currency);
        if (!$currency) {
            $currency = \App\Models\Currency::find($default_currency);
        }

        return $currency;
    }

    /**
     * Calculate amount in BDT
     * Converts from active currency to BDT if needed
     *
     * @param \Illuminate\Database\Eloquent\Collection $orders
     * @param array $data
     * @param \App\Models\Currency $active_currency
     * @param \App\Models\Currency $bdt_currency
     * @return array
     */
    private function amountCalculator($orders, $data, $active_currency, $bdt_currency)
    {
        $amount = 0;
        if ($orders && count($orders) > 0) {
            if ($active_currency && $bdt_currency) {
                // Convert to BDT
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
     * Extracts billing and shipping address details
     *
     * @param \App\Models\Order $order
     * @return array
     */
    private function getCustomerInfo($order)
    {
        // Default values
        $info = [
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address' => 'Dhaka, Bangladesh',
        ];

        // Get from billing address
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

        // Get from shipping address
        if ($order && $order->shipping_address) {
            $shipping = $order->shipping_address;

            $info['shipping_address'] = $shipping['address'] ?? $info['address'];
            $info['shipping_address_2'] = $shipping['address_2'] ?? '';
            $info['shipping_city'] = $shipping['city_name'] ?? '';
            $info['shipping_state'] = $shipping['state_name'] ?? '';
            $info['shipping_postcode'] = $shipping['postal_code'] ?? '';
            $info['shipping_country'] = $shipping['country_name'] ?? 'Bangladesh';
        } else {
            // Fallback to billing for shipping
            $info['shipping_address'] = $info['address'];
            $info['shipping_city'] = $info['city'];
            $info['shipping_state'] = $info['state'];
            $info['shipping_postcode'] = $info['postcode'];
            $info['shipping_country'] = $info['country'];
        }

        return $info;
    }
}