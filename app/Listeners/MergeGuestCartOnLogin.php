<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartOnLogin
{
    public function __construct(private CartService $cartService) {}

    public function handle(Login $event): void
    {
        // Admin logins (Filament) use the 'admin' guard: no cart to merge.
        if ($event->guard !== 'web') {
            return;
        }

        $guestToken = $this->cartService->currentSessionToken();

        if (! $guestToken) {
            return; // nothing to merge, they never had a guest cart
        }

        $this->cartService->mergeGuestCartIntoUser($guestToken, $event->user->id);
        $this->cartService->forgetSessionToken();
    }
}