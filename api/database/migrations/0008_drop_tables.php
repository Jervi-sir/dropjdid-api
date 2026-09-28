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
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('drop_status')->nullable('draft'); // draft, published, ended, cancelled, rejected
            $table->json('rejection_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('drop_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_main')->default(false);
            $table->timestamps();
        });
        Schema::create('drop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('drop_price', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['drop_id', 'product_id']);
        });
        Schema::create('drop_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drop_id')->constrained('drops')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // created, updated, status_changed, submitted_for_review, published, rejected, paused, etc.
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable(); // e.g. rejection reason or change note
            $table->json('metadata')->nullable(); // extra context like changes, IP, user_agent
            $table->timestamps();

            $table->index(['drop_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drop_histories');
        Schema::dropIfExists('drop_products');
        Schema::dropIfExists('drop_images');
        Schema::dropIfExists('drops');
    }
};
