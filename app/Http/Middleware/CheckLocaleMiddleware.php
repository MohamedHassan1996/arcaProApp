<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class CheckLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from the request URL segment
        $locale = $request->segment(3); // 'ar' in /api/v1/ar/auth/login

        // Check if the locale is supported
        $supportedLocales = ['en', 'fr', 'es', 'de', 'ar']; // Add your supported locales here

        if (in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale')); // Set default locale if not supported
        }

        return $next($request);
    }
}
