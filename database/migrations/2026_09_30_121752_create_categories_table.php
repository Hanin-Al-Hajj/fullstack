<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('_lft')->default(0);
            $table->unsignedInteger('_rgt')->default(0);
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->index(['_lft', '_rgt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};