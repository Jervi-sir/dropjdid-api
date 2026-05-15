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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->enum('status', [
                'draft',
                'active',
                'ended',
                'cancelled',
            ])->default('draft');

            $table->timestamps();
        });

        Schema::create('prize_joinings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prize_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('status', [
                'joined',
                'cancelled',
                'refunded',
                'winner',
                'lost',
            ])->default('joined');

            $table->timestamps();

            $table->unique(['prize_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
