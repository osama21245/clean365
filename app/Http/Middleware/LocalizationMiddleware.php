<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $local = $this->resolveLocale($request);
        App::setLocale($local);
        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $candidates = [
            $request->header('X-localization'),
            $request->header('localization'),
            $request->header('X-Localization'),
            $request->query('lang'),
            $request->query('locale')];

        foreach ($candidates as $value) {
            $locale = $this->normalizeLocale($value);
            if ($locale) {
                return $locale;
            }
        }

        $accept = (string) $request->header('Accept-Language', '');
        if ($accept !== '') {
            $primary = strtolower(substr(trim(explode(',', $accept)[0]), 0, 2));
            if (in_array($primary, ['ar', 'en'], true)) {
                return $primary;
            }
        }

        return 'en';
    }

    private function normalizeLocale(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $locale = strtolower(substr(trim($value), 0, 2));
        return in_array($locale, ['ar', 'en'], true) ? $locale : null;
    }
}
