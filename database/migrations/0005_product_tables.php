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
            $table->foreignId('category_id')->constrained()->nullOnDelete();
            $table->foreignId('gender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quality_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->decimal('original_price', 12, 2)->nullable();
            $table->decimal('show_price', 12, 2)->nullable();
            $table->decimal('store_price', 12, 2)->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'archived',
                'rejected',
            ])->default('draft');
            $table->text('rejection_reason')->nullable();

            $table->timestamp('refreshed_at')->nullable()->after('status');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('image');
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

        Schema::create('liked_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

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
