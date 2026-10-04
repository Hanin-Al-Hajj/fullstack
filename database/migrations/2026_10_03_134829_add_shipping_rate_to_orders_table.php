<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_rate_id')->nullable()->after('coupon_id')
                ->constrained()->nullOnDelete();

            // Snapshot: if the rate's price changes later, this order's
            // historical total should still be explainable.
            $table->string('shipping_method_label')->nullable()->after('shipping_rate_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_rate_id');
            $table->dropColumn('shipping_method_label');
        });
    }
};