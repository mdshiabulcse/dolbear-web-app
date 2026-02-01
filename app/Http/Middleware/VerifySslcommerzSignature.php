<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Signature Verification Middleware for SSLCOMMERZ
 *
 * This middleware verifies that incoming requests from SSLCOMMERZ
 * have a valid signature, ensuring they haven't been tampered with.
 */
class VerifySslcommerzSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get store password from settings
        $storePassword = settingHelper('sslcommerz_password');

        if (!$storePassword) {
            Log::error('SSLCOMMERZ Signature: Store password not configured');
            return response()->json(['error' => 'Configuration error'], 500);
        }

        // Verify signature
        if (!$this->verifySignature($request, $storePassword)) {
            Log::warning('SSLCOMMERZ Signature: Invalid signature detected', [
                'tran_id' => $request->input('tran_id'),
                'ip' => $request->ip(),
                'verify_sign' => $request->input('verify_sign'),
                'verify_key' => $request->input('verify_key'),
            ]);

            return response()->json([
                'error' => 'Invalid signature',
                'message' => 'Request verification failed'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Verify SSLCOMMERZ signature
     *
     * @param \Illuminate\Http\Request $request
     * @param string $storePassword
     * @return bool
     */
    private function verifySignature(Request $request, string $storePassword): bool
    {
        // Check if signature fields exist
        if (!$request->has('verify_sign') || !$request->has('verify_key')) {
            Log::warning('SSLCOMMERZ Signature: Missing signature fields');
            return false;
        }

        $verifySign = $request->input('verify_sign');
        $verifyKey = $request->input('verify_key');

        // Parse the keys that should be included in hash
        $keys = explode(',', $verifyKey);
        $hashData = [];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $hashData[$key] = $request->input($key);
            }
        }

        // Add store password (MD5 hashed as per SSLCOMMERZ spec)
        $hashData['store_passwd'] = md5($storePassword);

        // Sort by key (alphabetically)
        ksort($hashData);

        // Build hash string: key1=value1&key2=value2&...
        $hashString = '';
        foreach ($hashData as $key => $value) {
            $hashString .= $key . '=' . $value . '&';
        }
        $hashString = rtrim($hashString, '&');

        // Calculate MD5 hash
        $calculatedHash = md5($hashString);

        // Compare hashes
        return hash_equals($calculatedHash, $verifySign);
    }
}