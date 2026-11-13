<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    // Language order: the one chosen in the switcher, then the account's saved one, then English.
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale') ?? $request->user()?->locale ?? 'en';

        app()->setLocale(in_array($locale, ['en', 'ar'], true) ? $locale : 'en');

        return $next($request);
    }
}