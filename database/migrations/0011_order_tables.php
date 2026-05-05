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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->string('order_number')->unique();

            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();

            $table->string('full_name');
            $table->string('phone_number');

            $table->string('wilaya');
            $table->string('baladiya');
            $table->text('home_address');

            $table->enum('delivery_method', [
                'home',
                'desk',
            ])->default('home');

            $table->decimal('delivery_fees', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'returned',
            ])->default('pending');

            $table->boolean('has_claim_issue')->default(false);
            $table->text('claim_issue')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            $table->string('product_name');
            // $table->string('size')->nullable();
            // $table->string('color')->nullable();

            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
