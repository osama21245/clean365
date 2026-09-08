<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLocationWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $max = (int) config('tracking.location_write.max_per_minute', 30);

        if (!$user || $max <= 0) {
            return $next($request);
        }

        $key = 'tracking:location-write:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return response()->json([
                'response_code' => 'too_many_requests_429',
                'message' => translate('Too many location updates. Please slow down.'),
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
