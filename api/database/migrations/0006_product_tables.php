<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gender_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('quality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_original', 12, 2)->nullable();
            $table->decimal('price_shown', 12, 2)->nullable();
            $table->decimal('price_store', 12, 2)->nullable();
            $table->decimal('price_selling', 12, 2)->nullable();

            // Promotion / Discounts
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();

            // Creator & Platform Commissions
            $table->string('creator_earning_type')->default('fixed');
            $table->decimal('creator_earning_value', 12, 2)->nullable();
            $table->decimal('platform_earning', 12, 2)->nullable();

            // Event-related Pricing (Flash sale, Campaign, etc.)
            $table->string('event_name')->nullable();
            $table->decimal('event_price', 12, 2)->nullable();
            $table->timestamp('event_start_at')->nullable();
            $table->timestamp('event_end_at')->nullable();

            $table->string('product_status')->nullable('draft'); // draft, published, archived, rejected,
            $table->json('rejection_reason')->nullable();
            $table->boolean('is_affiliate')->default(true);

            $table->timestamp('refreshed_at')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_url');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_main')->default(false);
            $table->timestamps();
        });
        Schema::create('product_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('size_id')->constrained()->cascadeOnDelete();
            // $table->string('color')->nullable(); // Future incase
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
