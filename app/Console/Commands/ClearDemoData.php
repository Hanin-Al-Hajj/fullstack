<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDemoData extends Command
{
    protected $signature = 'store:clear-demo
        {what=all : all, orders (test orders, carts and customers) or catalog (demo products, categories, banners...)}
        {--force : allow this on a production server}';

    protected $description = 'Delete demo or test data before launch';

    public function handle(): int
    {
        $what = $this->argument('what');

        if (! in_array($what, ['all', 'orders', 'catalog'], true)) {
            $this->error('Use one of: all, orders, catalog.');

            return self::FAILURE;
        }

        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('This is a production server. Add --force only if you really mean it.');

            return self::FAILURE;
        }

        $this->warn(match ($what) {
            'orders' => 'This permanently deletes ALL orders, carts, wishlists, reviews and customer accounts (admins are kept).',
            'catalog' => 'This permanently deletes ALL products, categories, brands, attributes, banners, collections and promotions, with their photos.',
            default => 'This permanently deletes the whole catalog AND all orders and customer accounts (admins, shipping and coupons are kept).',
        });

        if ($this->ask('Type DELETE to confirm') !== 'DELETE') {
            $this->info('Nothing was deleted.');

            return self::SUCCESS;
        }

        if (in_array($what, ['all', 'orders'], true)) {
            $this->clearOrders();
        }

        if (in_array($what, ['all', 'catalog'], true)) {
            $this->clearCatalog();
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function clearOrders(): void
    {
        DB::transaction(function () {
            // Items, status history and payments go with their orders (foreign keys cascade).
            Order::query()->delete();
            DB::table('carts')->delete();
            // Addresses, wishlists, reviews, notes and saved payment methods go with their customers.
            User::query()->delete();
            Coupon::query()->update(['used_count' => 0]);
        });

        $this->line('Orders, carts and customers deleted.');
    }

    private function clearCatalog(): void
    {
        // One by one on purpose, so the uploaded photos are removed too.
        Product::withTrashed()->each(fn (Product $p) => $p->forceDelete());

        // Subcategories first, then their parents.
        Category::withDepth()->get()->sortByDesc('depth')->each(fn (Category $c) => $c->delete());

        Brand::query()->each(fn (Brand $b) => $b->delete());
        Banner::query()->each(fn (Banner $b) => $b->delete());
        Attribute::query()->delete();
        Collection::query()->delete();
        Promotion::query()->delete();

        $this->line('Products, categories, brands, attributes, banners, collections and promotions deleted.');
    }
}
