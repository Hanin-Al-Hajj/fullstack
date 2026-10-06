<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartOnLogin
{
    public function __construct(private CartService $cartService) {}

    public function handle(Login $event): void
    {
        $guestToken = $this->cartService->currentSessionToken();

        if (! $guestToken) {
            return; // nothing to merge, they never had a guest cart
        }

        $this->cartService->mergeGuestCartIntoUser($guestToken, $event->user->id);
        $this->cartService->forgetSessionToken();
    }
}