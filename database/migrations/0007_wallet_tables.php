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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency')->default('DZD');

            $table->timestamps();
        });
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->enum('type', [
                'deposit',
                'withdraw',
                'purchase',
                'refund',
                'drop_sale',
                'prize_joining',
            ]);

            $table->decimal('amount', 12, 2);

            $table->nullableMorphs('related');
            // related_type, related_id: Order, Drop, Prize, etc.

            $table->text('description')->nullable();

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'cancelled',
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
