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
        Schema::create('drops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('image')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'ended',
                'cancelled',
            ])->default('draft');

            $table->timestamps();
        });

        Schema::create('drop_product', function (Blueprint $table) {
            $table->id();

            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->decimal('drop_price', 12, 2)->nullable();

            $table->timestamps();

            $table->unique(['drop_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drops');
    }
};
