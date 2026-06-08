<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Priority:
     * 1. URL locale prefix (if exists)
     * 2. Session locale (if set)
     * 3. Default locale from config
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy danh sách ngôn ngữ hỗ trợ từ config (không fallback hardcode)
        $supportedLocales = array_keys(config('languages.supported', []));

        // Validate config
        if (empty($supportedLocales)) {
            throw new \RuntimeException('No supported languages configured. Please check config/languages.php');
        }

        // Priority 1: Get locale from URL route parameter
        $locale = $request->route('locale');

        // Priority 2: Get locale from session if URL doesn't have it
        if (!$locale || !in_array($locale, $supportedLocales)) {
            $locale = Session::get('locale');
        }

        // Priority 3: Use default locale if nothing is set
        if (!$locale || !in_array($locale, $supportedLocales)) {
            $locale = config('languages.default');

            // Validate default locale
            if (!$locale || !in_array($locale, $supportedLocales)) {
                $locale = $supportedLocales[0]; // Fallback to first supported language
            }
        }

        // Set application locale
        App::setLocale($locale);

        // Store in session for future requests
        Session::put('locale', $locale);

        return $next($request);
    }
}
