<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidatePathaoSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('X-PATHAO-Signature');

        // Get the expected signature from configuration
        $expectedSignature = config('pathao.webhook_signature');

        // Validate the header against the expected signature
        if ($header !== $expectedSignature) {
            // Handle invalid signature (e.g., return response)
            return response('Unauthorized', 401);
        }

        return $next($request);
    }
}
