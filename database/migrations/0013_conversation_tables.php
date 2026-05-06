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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->enum('type', [
                'private',
                'support',
            ])->default('private');

            $table->foreignId('first_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('second_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('first_user_last_read_at')->nullable();
            $table->timestamp('second_user_last_read_at')->nullable();

            $table->timestamps();

            $table->unique(['first_user_id', 'second_user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();

            $table->enum('type', [
                'text',
                'product',
                'profile',
                'image',
            ])->default('text');

            $table->text('body')->nullable();

            $table->nullableMorphs('attachable');
            // product, profile, store, drop, prize, etc.

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
