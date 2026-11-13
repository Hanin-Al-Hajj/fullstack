<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PricingService
{
    private ?Collection $promotions = null;

    // Live promotions, loaded once per request, with the ids of their products.
    private function promotions(): Collection
    {
        return $this->promotions ??= Promotion::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with('products:id')
            ->get();
    }

    /**
     * The price a customer actually pays for one unit right now: the normal
     * price, or the best live promotion price if lower. Cart totals, checkout
     * and the shop pages all use this, so they always agree.
     */
    public function unitPriceMinor(ProductVariant $variant): int
    {
        $best = $variant->price_minor;

        foreach ($this->promotions() as $promo) {
            if (! $promo->products->contains('id', $variant->product_id)) {
                continue;
            }

            $sale = $promo->discount_type === 'percentage'
                ? (int) round($variant->price_minor * (100 - $promo->discount_value) / 100)
                : max(0, $variant->price_minor - $promo->discount_value);

            $best = min($best, $sale);
        }

        return $best;
    }

    /**
     * Price fields for showing a variant: the sale price, with the normal
     * price as the struck-through one.
     */
    public function display(ProductVariant $variant): array
    {
        $data = $variant->only(['id', 'price_minor', 'compare_at_price_minor', 'currency']);
        $sale = $this->unitPriceMinor($variant);

        if ($sale < $variant->price_minor) {
            $data['compare_at_price_minor'] = $variant->price_minor;
            $data['price_minor'] = $sale;
        }

        return $data;
    }
}
