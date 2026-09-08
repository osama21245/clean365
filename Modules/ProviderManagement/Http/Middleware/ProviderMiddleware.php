<?php

namespace Modules\ProviderManagement\Http\Middleware;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check()
            && in_array(Auth::guard('web')->user()->user_type, PROVIDER_USER_TYPES)
            && (int)Auth::guard('web')->user()->is_active === 1
        ) {
            return $next($request);
        }

        if (Auth::guard('web')->check() && in_array(Auth::guard('web')->user()->user_type, PROVIDER_USER_TYPES)) {
            $request->session()->forget('modalClosed');
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => translate(ACCESS_DENIED['key'])], 401);
        }

        Toastr::error(translate(ACCESS_DENIED['key']));
        return redirect()->route('provider.auth.login');
    }
}
