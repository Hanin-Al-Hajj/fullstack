<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

// Demo data for the home page. Run once: php artisan db:seed --class=HomeSeeder
class HomeSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            ['Free delivery on orders over $50', 'توصيل مجاني للطلبات الكبيرة'],
            ['New season, new arrivals', 'وصل حديثاً'],
            ['Flash sale: 20% off selected items', 'تخفيضات سريعة'],
        ];
        foreach ($banners as $i => [$en, $ar]) {
            Banner::create(['title' => ['en' => $en, 'ar' => $ar], 'sort_order' => $i, 'is_active' => true]);
        }

        $ids = Product::where('is_active', true)->inRandomOrder()->limit(12)->pluck('id');

        $featured = Collection::create([
            'name' => ['en' => 'Featured', 'ar' => 'مميز'],
            'slug' => 'featured',
            'type' => 'featured',
            'is_active' => true,
        ]);
        $featured->products()->sync(
            $ids->take(6)->values()->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->all()
        );

        $promo = Promotion::create([
            'name' => ['en' => 'Flash Sale', 'ar' => 'تخفيضات سريعة'],
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(3),
            'is_active' => true,
        ]);
        $promo->products()->sync($ids->slice(6)->values()->all());
    }
}