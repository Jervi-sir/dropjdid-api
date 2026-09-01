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
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('level')->default('user'); // 'user' (creator/global), 'store'
            $table->string('type')->nullable(); // balance, store, refund, etc.
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('pending_balance', 12, 2)->default(0);
            $table->boolean('is_identity_verified')->default(false);

            $table->string('status')->default('new'); // new, pending, verified, blocked, rejected
            $table->string('currency', 3)->default('DZD');
            $table->timestamps();

            $table->index(['user_id', 'level']);
            $table->index(['store_id', 'level']);
            $table->index(['user_id', 'type']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();

            $table->smallInteger('direction')->default(0); // in (1), out (0)
            $table->string('type')->nullable(); // drops, store_sales, refund, bonus, request-withdrawal
            $table->string('status')->nullable(); // pending, completed, failed, cancelled

            $table->decimal('amount', 12, 2);

            // balance after this transaction
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);

            // Example: Drop, Order, Refund, Withdrawal
            $table->string('title')->nullable();

            // Example: #Colden_men_visiting_forest
            $table->string('reference')->nullable();

            $table->nullableMorphs('source');
            // source_type, source_id
            // can be Order, Drop, WithdrawalRequest, etc.

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['store_id', 'created_at']);
            $table->index(['wallet_id', 'status']);
        });

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->smallInteger('method')->default(0); // baridimob, ccp, bank_transfer, cash
            $table->smallInteger('status')->default(0); // pending_identity_check, pending, approved, rejected, paid, cancelled, failed

            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('wallet_transactions')
                ->nullOnDelete();

            $table->json('payment_details')->nullable();
            // ccp number, rip, bank name, phone, etc.

            $table->text('admin_note')->nullable();

            $table->timestamp('identity_checked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
