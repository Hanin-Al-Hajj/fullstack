<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InertiaTestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;
use App\Support\Seo;
use Inertia\Inertia;
use Illuminate\Http\Request;

// --- Storefront ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ProductController::class, 'index'])->name('shop');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category:slug?}', [CategoryController::class, 'index'])->name('categories');

// --- Cart ---
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

// --- Checkout ---
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout/quote', [CheckoutController::class, 'quote']);
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');

Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
Route::post('/addresses', [AddressController::class, 'store']);
Route::put('/addresses/{address}', [AddressController::class, 'update']);
Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist', [WishlistController::class, 'toggle']);

Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

Route::middleware('auth')->group(function () {
Route::get('/account', [AccountController::class, 'show'])->name('account');
Route::patch('/account', [AccountController::class, 'updateProfile']);
Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::post('/orders/{order:order_number}/reorder', [OrderController::class, 'reorder']);
});

// Breeze's old profile links now land on the new page.
Route::get('/profile', fn () => redirect('/account'))->name('profile.edit');

Route::get('/order/{order:order_number}', [OrderController::class, 'confirmation'])->name('order.confirmation');




// After login or registration, Breeze sends people here: take them home.
Route::get('/dashboard', fn () => redirect('/'))->name('dashboard');

$infoTitles = [
    'about' => ['About us', 'من نحن'],
    'contact' => ['Contact us', 'اتصل بنا'],
    'support' => ['Support', 'الدعم'],
    'privacy-policy' => ['Privacy policy', 'سياسة الخصوصية'],
];

foreach ($infoTitles as $slug => [$en, $ar]) {
    Route::get("/{$slug}", function () use ($slug, $en, $ar) {
        Seo::set(['title' => Seo::tr($en, $ar)]);

        return Inertia::render('Info', ['page' => $slug]);
    })->name($slug);
}

require __DIR__.'/auth.php';
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
    Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register');
    Route::get('/forgot-password', fn () => Inertia::render('Auth/ForgotPassword'))->name('password.request');
    Route::get('/reset-password/{token}', fn (Request $request, string $token) => Inertia::render('Auth/ResetPassword', [
        'token' => $token,
        'email' => $request->query('email'),
    ]))->name('password.reset');
});

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);
    session(['locale' => $locale]);
    $request->user()?->update(['locale' => $locale]);

    return redirect()->back();
})->name('locale');

