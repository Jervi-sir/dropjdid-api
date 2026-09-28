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
        // 1. Supply Requests Table (Targeting a specific product with batched sizes for warehouse supply)
        Schema::create('supply_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique(); // e.g. SR-20260925-XXXX
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('total_requested_quantity')->default(0);
            $table->integer('total_fulfilled_quantity')->default(0);
            $table->integer('total_received_quantity')->default(0);
            
            // draft, approved (sent_to_store), preparing, shipped_to_hub, received_at_hub, completed, cancelled
            $table->string('status')->default('draft');
            
            $table->string('tracking_number')->nullable();
            $table->string('courier_name')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
        });

        // 2. Supply Request Items Table (Breakdown per size & variant for this product supply request)
        Schema::create('supply_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('drop_id')->nullable()->constrained()->nullOnDelete();

            $table->string('product_name');
            $table->string('size_code')->nullable(); // e.g. 38, 39, L, XL
            $table->integer('requested_quantity')->default(1);
            $table->integer('fulfilled_quantity')->default(0);
            $table->integer('received_quantity')->default(0);

            $table->timestamps();
        });

        // 3. Update order_items to link each item to a supply request & item + track hub status
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('supply_request_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->foreignId('supply_request_item_id')->nullable()->after('supply_request_id')->constrained()->nullOnDelete();
            
            // awaiting_supply, supply_requested, in_transit_to_hub, in_hub, packed, shipped
            $table->string('fulfillment_status')->default('awaiting_supply')->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['supply_request_id']);
            $table->dropForeign(['supply_request_item_id']);
            $table->dropColumn(['supply_request_id', 'supply_request_item_id', 'fulfillment_status']);
        });

        Schema::dropIfExists('supply_request_items');
        Schema::dropIfExists('supply_requests');
    }
};
