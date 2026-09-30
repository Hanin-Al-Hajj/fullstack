<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();

            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped',
                'out_for_delivery', 'delivered', 'delivery_failed',
                'returned', 'cancelled',
            ])->default('pending');

            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_method');

            $table->char('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 12, 6)->nullable();

            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('shipping_minor')->default(0);
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('tax_minor')->default(0);
            $table->unsignedBigInteger('total_minor');

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            $table->json('product_name');
            $table->string('variant_label')->nullable();
            $table->string('sku');

            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('line_total_minor');
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};