<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

// Demo shipping so checkout can be tested. Run once:
// php artisan db:seed --class=ShippingSeeder
class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        $zone = ShippingZone::create([
            'name' => 'Lebanon',
            'governorates' => ['Beirut', 'Mount Lebanon', 'North Lebanon', 'Akkar', 'Beqaa', 'Baalbek-Hermel', 'South Lebanon', 'Nabatieh'],
            'is_active' => true,
        ]);

        $zone->rates()->createMany([
            ['name' => 'Standard (2-4 days)', 'rate_minor' => 300, 'free_shipping_over_minor' => 5000],
            ['name' => 'Express (next day)', 'rate_minor' => 600, 'free_shipping_over_minor' => null],
            ['name' => 'Pickup from store', 'rate_minor' => 0, 'free_shipping_over_minor' => null],
        ]);
    }
}