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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wilaya_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();

            $table->string('password')->nullable();
            $table->string('password_plaintext')->nullable();

            $table->string('image_url')->nullable();
            $table->text('description')->nullable();

            $table->string('store_status')->nullable(); // pending, active, suspended,
            $table->boolean('is_approved')->default(false);

            $table->timestamps();
        });

        // 2. Store to delivery costs table
        Schema::create('store_to_delivery_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('delivery_company_id')->nullable()->constrained('delivery_companies')->nullOnDelete()->cascadeOnUpdate();
            $table->string('delivery_company_code', 50)->nullable()->index();
            $table->foreignId('wilaya_id')->nullable()->constrained('wilayas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('wilaya_name')->nullable();

            // Costs
            $table->decimal('cost_domicile', 12, 2)->default(0)->comment('Home delivery cost');
            $table->decimal('cost_stopdesk', 12, 2)->default(0)->comment('Stop desk / office pickup cost');
            $table->decimal('cost_cancel', 12, 2)->default(0)->comment('Cancellation / return fee');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
