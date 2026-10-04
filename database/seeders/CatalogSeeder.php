<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        [$colorValues, $sizeValues] = $this->seedAttributes();
        $this->seedProducts($categories, $brands, $colorValues, $sizeValues);
    }

    /**
     * Build a simple 2-level category tree: a handful of top-level
     * categories, each with 2-3 subcategories. Products only ever
     * attach to the subcategories (the leaves).
     */
    private function seedCategories(): array
    {
        $tree = [
            'Electronics' => ['Mobile Phones', 'Laptops', 'Accessories'],
            'Fashion' => ["Men's Clothing", "Women's Clothing", 'Shoes'],
            'Home Goods' => ['Kitchen', 'Furniture', 'Decor'],
        ];

        $leafCategories = [];

        foreach ($tree as $parentName => $children) {
            $parent = Category::create([
                'name' => ['en' => $parentName, 'ar' => $parentName],
                'slug' => Str::slug($parentName),
                'is_active' => true,
            ]);

            foreach ($children as $childName) {
                $child = Category::create([
                    'name' => ['en' => $childName, 'ar' => $childName],
                    'slug' => Str::slug($parentName.'-'.$childName),
                    'is_active' => true,
                ]);
                $child->appendToNode($parent)->save();

                $leafCategories[] = $child;
            }
        }

        return $leafCategories;
    }

    private function seedBrands(): array
    {
        $names = [
            'Aurora', 'Nova', 'Meridian', 'Vantage', 'Crestline',
            'Pinnacle', 'Westbrook', 'Solace', 'Harborlight', 'Fieldstone',
            'Northgate', 'Driftwood',
        ];

        return collect($names)->map(fn ($name) => Brand::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
        ]))->all();
    }

    /**
     * Returns [colorValues, sizeValues] so seedProducts can build
     * realistic variant combinations.
     */
    private function seedAttributes(): array
    {
        $color = Attribute::create(['name' => 'Color', 'slug' => 'color', 'type' => 'color']);
        $colorOptions = [
            'Black' => '#000000', 'White' => '#ffffff', 'Red' => '#dc2626',
            'Blue' => '#2563eb', 'Green' => '#16a34a',
        ];
        $colorValues = collect($colorOptions)->map(
            fn ($hex, $name) => AttributeValue::create([
                'attribute_id' => $color->id,
                'value' => $name,
                'meta' => ['hex' => $hex],
            ])
        )->values()->all();

        $size = Attribute::create(['name' => 'Size', 'slug' => 'size', 'type' => 'select']);
        $sizeValues = collect(['S', 'M', 'L', 'XL'])->map(
            fn ($value) => AttributeValue::create([
                'attribute_id' => $size->id,
                'value' => $value,
            ])
        )->all();

        return [$colorValues, $sizeValues];
    }

    private function seedProducts(array $categories, array $brands, array $colorValues, array $sizeValues): void
    {
        $adjectives = ['Classic', 'Modern', 'Premium', 'Essential', 'Deluxe', 'Everyday', 'Pro', 'Signature'];
        $nouns = ['Jacket', 'Sneakers', 'Backpack', 'Lamp', 'Chair', 'Phone Case', 'Headphones', 'Mug', 'Watch', 'Wallet'];

        for ($i = 1; $i <= 200; $i++) {
            $adjective = $adjectives[array_rand($adjectives)];
            $noun = $nouns[array_rand($nouns)];
            $nameEn = "{$adjective} {$noun} {$i}";

            $product = Product::create([
                'category_id' => $categories[array_rand($categories)]->id,
                'brand_id' => $brands[array_rand($brands)]->id,
                'name' => [
                    'en' => $nameEn,
                    // Placeholder Arabic text for seed/dev data only —
                    // replace with real translated content before launch.
                    'ar' => "منتج {$i}",
                ],
                'slug' => Str::slug($nameEn).'-'.$i,
                'description' => [
                    'en' => "A {$adjective} {$noun} built for everyday use.",
                    'ar' => "وصف تجريبي للمنتج رقم {$i}.",
                ],
                'short_description' => [
                    'en' => "{$adjective} {$noun}",
                    'ar' => "منتج {$i}",
                ],
                'is_active' => true,
                'published_at' => now(),
            ]);

            // 2-3 variants per product, each a Color + Size combination
            $variantCount = rand(2, 3);
            $usedCombos = [];

            for ($v = 0; $v < $variantCount; $v++) {
                $color = $colorValues[array_rand($colorValues)];
                $size = $sizeValues[array_rand($sizeValues)];
                $comboKey = $color->id.'-'.$size->id;

                if (isset($usedCombos[$comboKey])) {
                    continue;
                }
                $usedCombos[$comboKey] = true;

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper(Str::random(3)).'-'.$product->id.'-'.$v,
                    'price_minor' => rand(500, 20000) * 100, // $5.00–$200.00
                    'stock_qty' => rand(0, 50),
                    'is_default' => $v === 0,
                    'is_active' => true,
                ]);

                $variant->attributeValues()->attach([$color->id, $size->id]);
            }
        }
    }
}