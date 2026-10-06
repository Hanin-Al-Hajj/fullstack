<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown for expected checkout failures (empty cart, out of stock,
 * invalid coupon, invalid shipping method) — caught by the controller
 * and turned into a clean 422 response, never a 500.
 */
class CheckoutException extends Exception
{
}