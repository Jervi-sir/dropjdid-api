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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wilaya_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('store_name');
            $table->string('phone_number')->nullable();
            $table->string('password')->nullable();
            $table->string('password_plaintext')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();

            $table->decimal('balance', 12, 2)->default(0);

            $table->smallInteger('status')->default(0); // pending, active, suspended,
            $table->boolean('is_verified')->default(true);

            $table->timestamps();
        });

        Schema::create('store_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('type')->default(0); // balance, refund,

            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('pending_balance', 12, 2)->default(0);

            $table->boolean('is_identity_verified')->default(false);

            $table->smallInteger('status')->default(0); // new, pending, verified, blocked, rejected,
            $table->string('currency', 3)->default('DZD');
            $table->timestamps();

            $table->unique(['store_id', 'type']);
            $table->index(['store_id', 'type']);
        });

        Schema::create('store_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->smallInteger('direction')->default(0); // in, out
            $table->smallInteger('type')->default(0); // drops, refund, bonus, request-withdrawal

            $table->smallInteger('status')->default(0); // pending, completed, failed, cancelled,

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

            $table->index(['store_id', 'created_at']);
            $table->index(['store_wallet_id', 'status']);

        });

        Schema::create('store_withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_wallet_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->smallInteger('method')->default(0); // baridimob, ccp, bank_transfer, cash
            $table->smallInteger('status')->default(0); // pending_identity_check, pending, approved, rejected, paid, cancelled, failed

            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('store_wallet_transactions')
                ->nullOnDelete();

            $table->json('payment_details')->nullable();
            // ccp number, rip, bank name, phone, etc.

            $table->text('admin_note')->nullable();

            $table->timestamp('identity_checked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'status']);
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
