<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', config('app.locale'));

        // only allow supported locales
        if (in_array($locale, ['fr', 'en', 'ar'], true)) {
            app()->setLocale($locale);
        } else {
            app()->setLocale('fr');
        }

        return $next($request);
    }
}
