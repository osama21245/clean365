<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\UserManagement\Entities\User;
use Symfony\Component\HttpFoundation\Response;

class UpdateFieldAgentLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (
            !$user instanceof User
            || !$user->is_active
            || !in_array($user->user_type, ['provider-admin', 'provider-serviceman'], true)
        ) {
            return $response;
        }

        $cacheKey = "field_agent_last_seen:{$user->id}";

        if (!Cache::has($cacheKey)) {
            User::whereKey($user->id)->update(['last_seen_at' => now()]);
            Cache::put($cacheKey, true, 60);
        }

        return $response;
    }
}
