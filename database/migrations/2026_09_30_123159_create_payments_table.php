<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway'); // 'cod', 'stripe', 'whish', ...
            $table->string('transaction_reference')->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending');
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};