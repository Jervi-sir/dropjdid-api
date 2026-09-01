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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. pending, confirmed, processing, shipped, delivered, cancelled, returned
            $table->string('en');
            $table->string('fr');
            $table->string('ar');
            $table->string('color')->nullable()->default('#8C94A3');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wilaya_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->string('order_number')->unique();

            $table->string('full_name');
            $table->string('phone_number');

            $table->string('wilaya');
            $table->string('baladiya');
            $table->text('home_address');

            $table->string('delivery_method')->nullable()->default('home'); // home, desk

            $table->decimal('delivery_fees', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('order_status_code')->nullable()->default('pending');
            $table->foreign('order_status_code')->references('code')->on('order_statuses')->nullOnDelete();

            $table->boolean('has_claim_issue')->default(false);
            $table->text('claim_issue')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drop_id')->nullable()->constrained();
            $table->foreignId('size_id')->constrained()->cascadeOnDelete();
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
