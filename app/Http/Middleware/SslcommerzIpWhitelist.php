<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * SSLCOMMERZ IP Whitelist Middleware
 *
 * This middleware ensures that IPN (Instant Payment Notification) requests
 * only come from SSLCOMMERZ's official servers.
 *
 * SSLCOMMERZ IP Ranges:
 * Source: https://developer.sslcommerz.com/
 */
class SslcommerzIpWhitelist
{
    /**
     * SSLCOMMERZ Server IP Addresses
     *
     * Sandbox IPs:
     * - 103.163.226.132
     * - 103.163.226.133
     *
     * Production IPs:
     * - 103.163.226.128
     * - 103.163.226.129
     * - 103.163.226.130
     * - 103.163.226.131
     *
     * @var array
     */
    protected $allowedIps = [
        // Sandbox Environment
        '103.163.226.132',
        '103.163.226.133',

        // Production Environment
        '103.163.226.128',
        '103.163.226.129',
        '103.163.226.130',
        '103.163.226.131',
    ];

    /**
     * Additional trusted proxy IPs (load balancers, etc.)
     * If you use a proxy/load balancer, add its IP here
     *
     * @var array
     */
    protected $trustedProxies = [
        // Add your proxy/load balancer IPs if needed
        // Example: '192.168.1.100',
    ];

    /**
     * Enable/disable IP whitelist checking
     * Set to false in development/testing if needed
     *
     * @var bool
     */
    protected $enabled = env('SSLCOMMERZ_IP_WHITELIST_ENABLED', true);

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check if disabled (for development/testing)
        if (!$this->enabled) {
            Log::warning('SSLCOMMERZ IPN: IP whitelist check disabled', [
                'ip' => $request->ip(),
            ]);
            return $next($request);
        }

        $requestIp = $request->ip();

        // Check if request is from trusted proxy
        if (in_array($requestIp, $this->trustedProxies)) {
            // Get real IP from X-Forwarded-For header
            $forwardedFor = $request->header('X-Forwarded-For');
            if ($forwardedFor) {
                $ips = explode(',', $forwardedFor);
                $requestIp = trim($ips[0]); // Get original client IP
            }
        }

        // Check if IP is in whitelist
        if (!$this->isIpAllowed($requestIp)) {
            Log::warning('SSLCOMMERZ IPN: Blocked unauthorized IP', [
                'request_ip' => $requestIp,
                'user_agent' => $request->userAgent(),
                'uri' => $request->fullUrl(),
                'allowed_ips' => $this->allowedIps,
            ]);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Access denied'
            ], 403);
        }

        // IP is allowed
        Log::info('SSLCOMMERZ IPN: Request allowed from whitelisted IP', [
            'ip' => $requestIp,
        ]);

        return $next($request);
    }

    /**
     * Check if IP is allowed
     *
     * @param string $ip
     * @return bool
     */
    protected function isIpAllowed(string $ip): bool
    {
        // Direct match
        if (in_array($ip, $this->allowedIps)) {
            return true;
        }

        // Check if it's a local/private IP (for testing)
        if ($this->isLocalIp($ip)) {
            $allowLocal = env('SSLCOMMERZ_ALLOW_LOCAL_IP', false);
            return $allowLocal;
        }

        return false;
    }

    /**
     * Check if IP is a local/private IP
     *
     * @param string $ip
     * @return bool
     */
    protected function isLocalIp(string $ip): bool
    {
        // Check for localhost
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        // Check for private IP ranges
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            $firstOctet = (int)$parts[0];
            $secondOctet = (int)$parts[1];

            // 10.0.0.0/8
            if ($firstOctet === 10) {
                return true;
            }

            // 172.16.0.0/12
            if ($firstOctet === 172 && $secondOctet >= 16 && $secondOctet <= 31) {
                return true;
            }

            // 192.168.0.0/16
            if ($firstOctet === 192 && $secondOctet === 168) {
                return true;
            }
        }

        return false;
    }
}