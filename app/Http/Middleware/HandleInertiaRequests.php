<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    // Props available on every page via usePage().props
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => ['user' => fn () => $request->user()?->only(['id', 'name', 'email'])],
            'status' => fn () => $request->session()->get('status'),
            'wishlist' => fn () => $request->user() ? $request->user()->wishlists()->pluck('product_variant_id')->all() : [],
            'store' => fn () => config('store'),
            'cartCount' => fn () => app(CartService::class)->peekCount(),
        ];
    }
}