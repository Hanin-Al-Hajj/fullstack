<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->unsignedInteger('discount_value');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('promotion_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promotion_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_product');
        Schema::dropIfExists('promotions');
    }
};